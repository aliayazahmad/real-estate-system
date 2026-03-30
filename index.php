<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

$stats = [
    'approved_properties' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE status = 'approved'") ?? 0),
    'bookings' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM bookings') ?? 0),
    'payments' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM payments WHERE status = 'paid'") ?? 0),
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

render_page_start('Built for listings, bookings, approvals, and payments', 'Ship the core real-estate workflow from discovery to reporting in one place.');
?>

<section class="hero-panel">
    <div class="hero-panel__copy">
        <p class="eyebrow">IGNOU PROJECT READY MVP</p>
        <h2>From manual property handling to a structured digital workflow.</h2>
        <p>
            Manage customer registration, agent listings, admin approvals, booking requests, payment records,
            and reporting through a single PHP/MySQL application.
        </p>

        <div class="action-row">
            <a class="btn btn--primary" href="properties.php">Browse Properties</a>
            <?php if (!is_logged_in()) { ?>
                <a class="btn btn--ghost" href="register.php">Create an Account</a>
            <?php } else { ?>
                <a class="btn btn--ghost" href="<?php echo has_role('admin') ? 'admin_dashboard.php' : 'dashboard.php'; ?>">Open Dashboard</a>
            <?php } ?>
        </div>
    </div>

    <div class="hero-panel__stats">
        <div class="stat-card">
            <span class="stat-card__value"><?php echo $stats['approved_properties']; ?></span>
            <span class="stat-card__label">Approved Properties</span>
        </div>
        <div class="stat-card">
            <span class="stat-card__value"><?php echo $stats['bookings']; ?></span>
            <span class="stat-card__label">Booking Records</span>
        </div>
        <div class="stat-card">
            <span class="stat-card__value"><?php echo $stats['payments']; ?></span>
            <span class="stat-card__label">Paid Transactions</span>
        </div>
    </div>
</section>

<section class="feature-grid">
    <article class="feature-card">
        <h3>Customer Experience</h3>
        <p>Search listings with filters, request site visits, track booking status, and view payment receipts.</p>
    </article>
    <article class="feature-card">
        <h3>Agent Operations</h3>
        <p>Create property listings, upload images, maintain inventory, and monitor booking interest.</p>
    </article>
    <article class="feature-card">
        <h3>Admin Control</h3>
        <p>Approve or reject listings, confirm booking requests, review payments, and export reports.</p>
    </article>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">LATEST APPROVED LISTINGS</p>
            <h2>Featured Properties</h2>
        </div>
        <a class="btn btn--ghost" href="properties.php">View All</a>
    </div>

    <?php if ($featuredProperties === []) { ?>
        <?php render_empty_state('No approved properties yet', 'Add a property as an agent or approve one as an admin to see it here.'); ?>
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
