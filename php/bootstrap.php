<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$conn = db_connection();

function app_name(): string
{
    return 'Real Estate Hub';
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post_request(): bool
{
    return request_method() === 'POST';
}

function redirect(string $path, array $query = []): void
{
    $location = $path;

    if ($query !== []) {
        $location .= '?' . http_build_query($query);
    }

    header('Location: ' . $location);
    exit();
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function normalize_role(string $role): string
{
    if ($role === 'user') {
        return 'customer';
    }

    return $role ?: 'customer';
}

function build_auth_user(array $user): array
{
    return [
        'id' => (int) ($user['id'] ?? 0),
        'name' => (string) ($user['name'] ?? ''),
        'email' => (string) ($user['email'] ?? ''),
        'phone' => (string) ($user['phone'] ?? ''),
        'role' => normalize_role((string) ($user['role'] ?? 'customer')),
    ];
}

function persist_auth_user(array $authUser): void
{
    $_SESSION['auth_user'] = $authUser;
    $_SESSION['user_id'] = $authUser['id'];
    $_SESSION['user_name'] = $authUser['name'];
    $_SESSION['user_email'] = $authUser['email'];
    $_SESSION['role'] = $authUser['role'];
    $_SESSION['is_admin'] = $authUser['role'] === 'admin' ? 1 : 0;
}

function login_user(array $user): void
{
    session_regenerate_id(true);

    persist_auth_user(build_auth_user($user));
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function current_user(): ?array
{
    if (!isset($_SESSION['auth_user']) && isset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['role'])) {
        persist_auth_user([
            'id' => (int) $_SESSION['user_id'],
            'name' => (string) $_SESSION['user_name'],
            'email' => (string) ($_SESSION['user_email'] ?? ''),
            'phone' => '',
            'role' => normalize_role((string) $_SESSION['role']),
        ]);
    }

    $user = $_SESSION['auth_user'] ?? null;

    if ($user === null) {
        return null;
    }

    global $conn;

    $freshUser = db_one(
        $conn,
        'SELECT id, name, email, phone, role FROM users WHERE id = ? LIMIT 1',
        'i',
        [(int) ($user['id'] ?? 0)]
    );

    if ($freshUser) {
        $authUser = build_auth_user($freshUser);

        if ($authUser !== $user) {
            persist_auth_user($authUser);
            $user = $authUser;
        }
    }

    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function current_role(): string
{
    $user = current_user();

    return $user['role'] ?? 'guest';
}

function has_role($roles): bool
{
    $roles = (array) $roles;

    return in_array(current_role(), $roles, true);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_role($roles): void
{
    require_login();

    if (!has_role($roles)) {
        set_flash('error', 'You do not have permission to access that page.');
        redirect('dashboard.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['_token'] ?? '';

    if (!hash_equals(csrf_token(), (string) $submitted)) {
        http_response_code(419);
        die('Security token mismatch. Please refresh the page and try again.');
    }
}

function get_string(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

function post_string(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function get_int(string $key, int $default = 0): int
{
    return (int) ($_GET[$key] ?? $default);
}

function post_int(string $key, int $default = 0): int
{
    return (int) ($_POST[$key] ?? $default);
}

function is_valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_phone(string $phone): bool
{
    if ($phone === '') {
        return true;
    }

    return (bool) preg_match('/^[6-9]\d{9}$/', $phone);
}

function is_valid_password(string $password): bool
{
    return strlen($password) >= 8;
}

function is_positive_amount(string $value): bool
{
    return is_numeric($value) && (float) $value > 0;
}

function indian_number_format(int $value): string
{
    $digits = (string) $value;

    if (strlen($digits) <= 3) {
        return $digits;
    }

    $lastThree = substr($digits, -3);
    $remaining = substr($digits, 0, -3);
    $parts = [];

    while (strlen($remaining) > 2) {
        array_unshift($parts, substr($remaining, -2));
        $remaining = substr($remaining, 0, -2);
    }

    if ($remaining !== '') {
        array_unshift($parts, $remaining);
    }

    return implode(',', $parts) . ',' . $lastThree;
}

function currency($value): string
{
    $rounded = (int) round((float) $value);
    $sign = $rounded < 0 ? '-' : '';

    return $sign . '&#8377;' . indian_number_format(abs($rounded));
}

function format_date(?string $value, string $fallback = 'Not scheduled'): string
{
    if (!$value) {
        return $fallback;
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $fallback;
    }

    return date('d M Y', $timestamp);
}

function property_status_class(string $status): string
{
    $status = strtolower($status);
    $map = [
        'approved' => 'success',
        'booked' => 'primary',
        'pending' => 'warning',
        'confirmed' => 'primary',
        'paid' => 'success',
        'cancelled' => 'danger',
        'rejected' => 'danger',
        'failed' => 'danger',
    ];

    return $map[$status] ?? 'muted';
}

function upload_directory(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
}

function ensure_upload_directory(): void
{
    $directory = upload_directory();

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

function upload_image(string $field, ?string $existingFile = null): ?string
{
    if (!isset($_FILES[$field]) || (int) $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $existingFile;
    }

    $file = $_FILES[$field];

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    if ((int) $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mimeType])) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are supported.');
    }

    ensure_upload_directory();

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mimeType];
    $destination = upload_directory() . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    if ($existingFile) {
        delete_image($existingFile);
    }

    return $fileName;
}

function delete_image(?string $fileName): void
{
    if (!$fileName) {
        return;
    }

    $path = upload_directory() . DIRECTORY_SEPARATOR . basename($fileName);

    if (is_file($path)) {
        @unlink($path);
    }
}

function booking_can_be_paid(array $booking): bool
{
    return ($booking['booking_status'] ?? $booking['status'] ?? '') === 'confirmed'
        && empty($booking['payment_status']);
}

function booking_is_open(string $status): bool
{
    return in_array($status, ['pending', 'confirmed'], true);
}
