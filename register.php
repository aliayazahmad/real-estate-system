<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

if (is_logged_in()) {
    redirect(has_role('admin') ? 'admin_dashboard.php' : 'dashboard.php');
}

$form = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'customer',
];
$errors = [];

if (is_post_request()) {
    verify_csrf();

    $form['name'] = post_string('name');
    $form['email'] = strtolower(post_string('email'));
    $form['phone'] = post_string('phone');
    $form['role'] = post_string('role', 'customer');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($form['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if (!is_valid_email($form['email'])) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!is_valid_phone($form['phone'])) {
        $errors[] = 'Phone number must be a valid 10-digit Indian mobile number.';
    }

    if (!in_array($form['role'], ['customer', 'agent'], true)) {
        $errors[] = 'Choose a valid account type.';
    }

    if (!is_valid_password($password)) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    $existingUser = db_one($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1', 's', [$form['email']]);

    if ($existingUser) {
        $errors[] = 'That email address is already registered.';
    }

    if ($errors === []) {
        db_execute(
            $conn,
            'INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)',
            'sssss',
            [
                $form['name'],
                $form['email'],
                $form['phone'],
                password_hash($password, PASSWORD_DEFAULT),
                $form['role'],
            ]
        );

        set_flash('success', 'Account created successfully. You can log in now.');
        redirect('login.php');
    }
}

render_page_start('Register', 'Create a customer or agent account for the platform.');
?>

<section class="form-shell">
    <div class="form-card form-card--wide">
        <h2>Create Your Account</h2>
        <p>Customers can browse and book. Agents can list and manage properties.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" class="grid-form">
            <?php echo csrf_field(); ?>

            <label class="field">
                <span>Full Name</span>
                <input type="text" name="name" value="<?php echo h($form['name']); ?>" required>
            </label>

            <label class="field">
                <span>Email Address</span>
                <input type="email" name="email" value="<?php echo h($form['email']); ?>" required>
            </label>

            <label class="field">
                <span>Phone Number</span>
                <input type="tel" name="phone" maxlength="10" value="<?php echo h($form['phone']); ?>" placeholder="Optional">
            </label>

            <label class="field">
                <span>Account Type</span>
                <select name="role" required>
                    <option value="customer" <?php echo $form['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="agent" <?php echo $form['role'] === 'agent' ? 'selected' : ''; ?>>Agent</option>
                </select>
            </label>

            <label class="field">
                <span>Password</span>
                <input type="password" name="password" required>
            </label>

            <label class="field">
                <span>Confirm Password</span>
                <input type="password" name="confirm_password" required>
            </label>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Register</button>
            </div>
        </form>

        <p class="form-note">Already registered? <a href="login.php">Log in here</a>.</p>
    </div>
</section>

<?php render_page_end(); ?>
