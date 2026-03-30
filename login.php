<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

if (is_logged_in()) {
    redirect(has_role('admin') ? 'admin_dashboard.php' : 'dashboard.php');
}

$email = '';
$errors = [];

if (is_post_request()) {
    verify_csrf();

    $email = post_string('email');
    $password = (string) ($_POST['password'] ?? '');

    if (!is_valid_email($email)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if ($errors === []) {
        $user = db_one($conn, 'SELECT * FROM users WHERE email = ? LIMIT 1', 's', [$email]);

        if (!$user || !password_verify($password, (string) $user['password'])) {
            $errors[] = 'Email or password is incorrect.';
        } else {
            login_user($user);
            set_flash('success', 'Welcome back, ' . $user['name'] . '.');
            redirect(normalize_role((string) $user['role']) === 'admin' ? 'admin_dashboard.php' : 'dashboard.php');
        }
    }
}

render_page_start('Login', 'Access the customer, agent, or admin workspace.');
?>

<section class="form-shell">
    <div class="form-card">
        <h2>Sign In</h2>
        <p>Use the email and password associated with your real estate account.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" class="stack-md">
            <?php echo csrf_field(); ?>

            <label class="field">
                <span>Email Address</span>
                <input type="email" name="email" value="<?php echo h($email); ?>" required>
            </label>

            <label class="field">
                <span>Password</span>
                <input type="password" name="password" required>
            </label>

            <button class="btn btn--primary btn--full" type="submit">Login</button>
        </form>

        <p class="form-note">Need a new account? <a href="register.php">Register here</a>.</p>
    </div>
</section>

<?php render_page_end(); ?>
