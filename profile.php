<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_login();

$user = db_one($conn, 'SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [current_user()['id']]);

if (!$user) {
    logout_user();
    redirect('login.php');
}

$form = [
    'name' => (string) $user['name'],
    'email' => (string) $user['email'],
    'phone' => (string) ($user['phone'] ?? ''),
];
$errors = [];

if (is_post_request()) {
    verify_csrf();

    $form['name'] = post_string('name');
    $form['phone'] = post_string('phone');
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($form['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if (!is_valid_phone($form['phone'])) {
        $errors[] = 'Phone number must be a valid 10-digit Indian mobile number.';
    }

    if ($password !== '' && !is_valid_password($password)) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if ($errors === []) {
        if ($password !== '') {
            db_execute(
                $conn,
                'UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?',
                'sssi',
                [$form['name'], $form['phone'], password_hash($password, PASSWORD_DEFAULT), $user['id']]
            );
        } else {
            db_execute(
                $conn,
                'UPDATE users SET name = ?, phone = ? WHERE id = ?',
                'ssi',
                [$form['name'], $form['phone'], $user['id']]
            );
        }

        $updatedUser = db_one($conn, 'SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [$user['id']]);
        login_user($updatedUser ?: $user);
        set_flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

render_page_start('Profile', 'Update your contact details and account security settings.');
?>

<section class="form-shell">
    <div class="form-card form-card--wide">
        <h2>Profile Details</h2>
        <p>Keep your customer, agent, or admin profile information accurate.</p>

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
                <input type="email" value="<?php echo h($form['email']); ?>" readonly>
            </label>

            <label class="field">
                <span>Phone Number</span>
                <input type="tel" name="phone" maxlength="10" value="<?php echo h($form['phone']); ?>" placeholder="Optional">
            </label>

            <label class="field">
                <span>Role</span>
                <input type="text" value="<?php echo h(ucfirst((string) current_user()['role'])); ?>" readonly>
            </label>

            <label class="field">
                <span>New Password</span>
                <input type="password" name="password" placeholder="Leave blank to keep the current password">
            </label>

            <label class="field">
                <span>Confirm New Password</span>
                <input type="password" name="confirm_password" placeholder="Repeat the new password">
            </label>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</section>

<?php render_page_end(); ?>
