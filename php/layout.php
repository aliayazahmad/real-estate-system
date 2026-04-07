<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function nav_items(): array
{
    $items = [
        ['label' => 'Home', 'href' => 'index.php'],
        ['label' => 'Properties', 'href' => 'properties.php'],
    ];

    if (!is_logged_in()) {
        $items[] = ['label' => 'Login', 'href' => 'login.php'];
        $items[] = ['label' => 'Register', 'href' => 'register.php'];

        return $items;
    }

    $items[] = ['label' => 'Dashboard', 'href' => current_role() === 'admin' ? 'admin_dashboard.php' : 'dashboard.php'];

    if (has_role('agent')) {
        $items[] = ['label' => 'Add Property', 'href' => 'add_property.php'];
    }

    if (has_role('customer')) {
        $items[] = ['label' => 'My Bookings', 'href' => 'my_bookings.php'];
    }

    $items[] = ['label' => 'Profile', 'href' => 'profile.php'];

    if (has_role('admin')) {
        $items[] = ['label' => 'Reports', 'href' => 'reports.php'];
    }

    $items[] = ['label' => 'Logout', 'href' => 'logout.php'];

    return $items;
}

function render_page_start(string $title, string $description = ''): void
{
    $flash = pull_flash();
    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $styleVersion = (string) (@filemtime(dirname(__DIR__) . '/css/style.css') ?: '1');
    $scriptVersion = (string) (@filemtime(dirname(__DIR__) . '/js/script.js') ?: '1');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title . ' | ' . app_name()); ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo h($styleVersion); ?>">
    <script src="js/script.js?v=<?php echo h($scriptVersion); ?>" defer></script>
</head>
<body>
<header class="site-header">
    <div class="nav-shell">
        <a class="brand-mark" href="index.php">
            <span class="brand-mark__crest">RH</span>
            <span>
                <strong><?php echo h(app_name()); ?></strong>
                <small>Property Listing & Booking</small>
            </span>
        </a>

        <nav class="nav-links">
            <?php foreach (nav_items() as $item) { ?>
                <a class="nav-link <?php echo $currentScript === $item['href'] ? 'is-active' : ''; ?>" href="<?php echo h($item['href']); ?>">
                    <?php echo h($item['label']); ?>
                </a>
            <?php } ?>
        </nav>
    </div>
</header>

<main class="page-shell">
    <?php if ($flash) { ?>
        <div class="alert alert--<?php echo h($flash['type']); ?>" data-auto-dismiss="true">
            <strong><?php echo ucfirst(h($flash['type'])); ?>:</strong>
            <?php echo h($flash['message']); ?>
        </div>
    <?php } ?>

    <?php if ($title !== '') { ?>
        <section class="page-banner">
            <div>
                <p class="eyebrow"><?php echo is_logged_in() ? h(strtoupper(current_role())) : 'SMART REAL ESTATE WORKFLOW'; ?></p>
                <h1><?php echo h($title); ?></h1>
                <?php if ($description !== '') { ?>
                    <p><?php echo h($description); ?></p>
                <?php } ?>
            </div>
            <?php if (is_logged_in()) { ?>
                <div class="user-pill">
                    <span><?php echo h(current_user()['name']); ?></span>
                    <?php if (!empty(current_user()['email'])) { ?>
                        <small><?php echo h(current_user()['email']); ?></small>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    <?php } ?>
    <?php
}

function render_page_end(): void
{
    ?>
</main>
</body>
</html>
    <?php
}

function render_empty_state(string $title, string $description, string $actionLabel = '', string $actionHref = ''): void
{
    ?>
    <section class="empty-state">
        <h2><?php echo h($title); ?></h2>
        <?php if ($description !== '') { ?>
            <p><?php echo h($description); ?></p>
        <?php } ?>
        <?php if ($actionLabel !== '' && $actionHref !== '') { ?>
            <a class="btn btn--primary" href="<?php echo h($actionHref); ?>"><?php echo h($actionLabel); ?></a>
        <?php } ?>
    </section>
    <?php
}
