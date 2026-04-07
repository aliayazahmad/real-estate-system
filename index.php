<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

$isLoggedIn = is_logged_in();
$isAdmin = has_role('admin');
$isAgent = has_role('agent');
$isCustomer = has_role('customer');
$user = current_user();

$hero = [
    'eyebrow' => 'REAL ESTATE HUB',
    'title' => 'Discover verified listings and move from search to booking faster.',
    'description' => 'Browse approved properties, compare prices, and keep your property decisions organized in one place.',
    'primary' => ['label' => 'Browse Properties', 'href' => 'properties.php'],
    'secondary' => ['label' => 'Create an Account', 'href' => 'register.php'],
    'image_label' => 'Property search and ownership',
];

$section = [
    'eyebrow' => 'LATEST APPROVED LISTINGS',
    'title' => 'Featured Properties',
    'button' => ['label' => 'View All', 'href' => 'properties.php'],
    'empty_title' => 'No approved properties yet',
    'empty_description' => 'Add a property as an agent or approve one as an admin to see it here.',
];

$stats = [];
$featuredProperties = [];

if ($isAdmin) {
    $hero = [
        'eyebrow' => 'ADMIN OVERVIEW',
        'title' => 'Review approvals, booking activity, and payment health from one control point.',
        'description' => 'Your homepage now prioritizes operational oversight so pending work is visible before you enter the dashboard.',
        'primary' => ['label' => 'Open Dashboard', 'href' => 'admin_dashboard.php'],
        'secondary' => ['label' => 'View Reports', 'href' => 'reports.php'],
        'image_label' => 'Admin control and property approvals',
    ];

    $stats = [
        'Pending Properties' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE status = 'pending'") ?? 0),
        'Pending Bookings' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM bookings WHERE status = 'pending'") ?? 0),
        'Paid Transactions' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM payments WHERE status = 'paid'") ?? 0),
    ];

    $featuredProperties = db_all(
        $conn,
        "SELECT p.*, u.name AS owner_name
         FROM properties p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.status = 'pending'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 3"
    );

    $section = [
        'eyebrow' => 'APPROVAL QUEUE',
        'title' => 'Pending Property Reviews',
        'button' => ['label' => 'Open Dashboard', 'href' => 'admin_dashboard.php'],
        'empty_title' => 'No pending properties',
        'empty_description' => 'New listings submitted by agents will appear here for review.',
    ];
} elseif ($isAgent) {
    $hero = [
        'eyebrow' => 'AGENT WORKSPACE',
        'title' => 'Manage your listings, track responses, and keep your pipeline moving.',
        'description' => 'Your homepage focuses on the properties you own, the bookings they attract, and the next action you should take.',
        'primary' => ['label' => 'My Listings', 'href' => 'properties.php'],
        'secondary' => ['label' => 'Add Property', 'href' => 'add_property.php'],
        'image_label' => 'Agent listing management',
    ];

    $stats = [
        'My Listings' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM properties WHERE user_id = ?', 'i', [$user['id']]) ?? 0),
        'Pending Reviews' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE user_id = ? AND status = 'pending'", 'i', [$user['id']]) ?? 0),
        'Booking Requests' => (int) (db_scalar(
            $conn,
            "SELECT COUNT(*)
             FROM bookings b
             INNER JOIN properties p ON p.id = b.property_id
             WHERE p.user_id = ? AND b.status IN ('pending', 'confirmed')",
            'i',
            [$user['id']]
        ) ?? 0),
    ];

    $featuredProperties = db_all(
        $conn,
        "SELECT p.*, u.name AS owner_name
         FROM properties p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.user_id = ?
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 3",
        'i',
        [$user['id']]
    );

    $section = [
        'eyebrow' => 'YOUR INVENTORY',
        'title' => 'Recent Listings',
        'button' => ['label' => 'Open Dashboard', 'href' => 'dashboard.php'],
        'empty_title' => 'No listings yet',
        'empty_description' => 'Add your first property to start the review and booking flow.',
    ];
} elseif ($isCustomer) {
    $hero = [
        'eyebrow' => 'CUSTOMER SPACE',
        'title' => 'Track your bookings and keep exploring approved homes with confidence.',
        'description' => 'Your homepage now highlights your booking progress while keeping new approved listings easy to browse.',
        'primary' => ['label' => 'Browse Properties', 'href' => 'properties.php'],
        'secondary' => ['label' => 'My Bookings', 'href' => 'my_bookings.php'],
        'image_label' => 'Customer property browsing and booking',
    ];

    $stats = [
        'My Bookings' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM bookings WHERE user_id = ?', 'i', [$user['id']]) ?? 0),
        'Confirmed Visits' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'", 'i', [$user['id']]) ?? 0),
        'Paid Receipts' => (int) (db_scalar(
            $conn,
            "SELECT COUNT(*)
             FROM bookings b
             INNER JOIN payments pay ON pay.booking_id = b.id
             WHERE b.user_id = ? AND pay.status = 'paid'",
            'i',
            [$user['id']]
        ) ?? 0),
    ];

    $featuredProperties = db_all(
        $conn,
        "SELECT p.*, u.name AS owner_name
         FROM properties p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.status = 'approved'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 3"
    );
} else {
    $stats = [
        'Approved Properties' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE status = 'approved'") ?? 0),
        'Booking Records' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM bookings') ?? 0),
        'Paid Transactions' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM payments WHERE status = 'paid'") ?? 0),
    ];

    $featuredProperties = db_all(
        $conn,
        "SELECT p.*, u.name AS owner_name
         FROM properties p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.status = 'approved'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 3"
    );
}

render_page_start($isLoggedIn ? 'Welcome Back' : 'Welcome to Real Estate Hub');
?>

<section class="hero-panel">
    <div class="hero-panel__copy">
        <div class="action-row action-row--hero">
            <a class="btn btn--hero-toggle is-active" href="<?php echo h($hero['primary']['href']); ?>"><?php echo h($hero['primary']['label']); ?></a>
            <a class="btn btn--hero-toggle" href="<?php echo h($hero['secondary']['href']); ?>"><?php echo h($hero['secondary']['label']); ?></a>
        </div>

        <div class="hero-panel__image">
            <img src="uploads/hero-home.jpg" alt="<?php echo h($hero['image_label']); ?>">
        </div>
    </div>

    <div class="hero-panel__stats">
        <?php foreach ($stats as $label => $value) { ?>
            <div class="stat-card">
                <span class="stat-card__value"><?php echo h((string) $value); ?></span>
                <span class="stat-card__label"><?php echo h($label); ?></span>
            </div>
        <?php } ?>
    </div>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow"><?php echo h($section['eyebrow']); ?></p>
            <h2><?php echo h($section['title']); ?></h2>
        </div>
        <a class="btn btn--ghost" href="<?php echo h($section['button']['href']); ?>"><?php echo h($section['button']['label']); ?></a>
    </div>

    <?php if ($featuredProperties === []) { ?>
        <?php render_empty_state($section['empty_title'], $section['empty_description']); ?>
    <?php } else { ?>
        <div class="card-grid">
            <?php foreach ($featuredProperties as $property) { ?>
                <article class="card">
                    <?php if (!empty($property['image'])) { ?>
                        <img src="uploads/<?php echo h($property['image']); ?>" alt="<?php echo h($property['title']); ?>">
                    <?php } else { ?>
                        <div class="card__placeholder">No image uploaded</div>
                    <?php } ?>

                    <div class="card__body">
                        <div class="card__meta-row">
                            <span class="badge badge--<?php echo h(property_status_class((string) $property['status'])); ?>">
                                <?php echo h(ucfirst((string) $property['status'])); ?>
                            </span>
                            <strong class="price-tag"><?php echo currency($property['price']); ?></strong>
                        </div>

                        <h3><?php echo h($property['title']); ?></h3>
                        <p class="card__location">
                            <?php echo h(trim(($property['city'] ? $property['city'] . ', ' : '') . $property['location'], ', ')); ?>
                        </p>
                        <p class="card__copy"><?php echo h(substr((string) ($property['description'] ?? 'Property details available on the listing page.'), 0, 120)); ?></p>
                        <p class="card__support">Listed by <?php echo h($property['owner_name'] ?: 'Real Estate Hub'); ?></p>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<?php render_page_end(); ?>
