<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_login();

if (has_role('admin')) {
    redirect('admin_dashboard.php');
}

$user = current_user();
$isAgent = has_role('agent');

if ($isAgent) {
    $stats = [
        'properties' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM properties WHERE user_id = ?', 'i', [$user['id']]) ?? 0),
        'pending_properties' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE user_id = ? AND status = 'pending'", 'i', [$user['id']]) ?? 0),
        'booking_requests' => (int) (db_scalar(
            $conn,
            "SELECT COUNT(*)
             FROM bookings b
             INNER JOIN properties p ON p.id = b.property_id
             WHERE p.user_id = ? AND b.status IN ('pending', 'confirmed')",
            'i',
            [$user['id']]
        ) ?? 0),
    ];

    $recentProperties = db_all(
        $conn,
        'SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 5',
        'i',
        [$user['id']]
    );

    $recentBookings = db_all(
        $conn,
        "SELECT b.*, p.title, p.city, p.location, u.name AS customer_name
         FROM bookings b
         INNER JOIN properties p ON p.id = b.property_id
         INNER JOIN users u ON u.id = b.user_id
         WHERE p.user_id = ?
         ORDER BY b.created_at DESC, b.id DESC
         LIMIT 5",
        'i',
        [$user['id']]
    );
} else {
    $stats = [
        'bookings' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM bookings WHERE user_id = ?', 'i', [$user['id']]) ?? 0),
        'confirmed' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'", 'i', [$user['id']]) ?? 0),
        'paid' => (int) (db_scalar(
            $conn,
            "SELECT COUNT(*)
             FROM bookings b
             INNER JOIN payments pay ON pay.booking_id = b.id
             WHERE b.user_id = ? AND pay.status = 'paid'",
            'i',
            [$user['id']]
        ) ?? 0),
    ];

    $recentBookings = db_all(
        $conn,
        "SELECT b.*, p.title, p.city, p.location, p.price, p.image, pay.status AS payment_status, pay.invoice_number
         FROM bookings b
         INNER JOIN properties p ON p.id = b.property_id
         LEFT JOIN payments pay ON pay.booking_id = b.id
         WHERE b.user_id = ?
         ORDER BY b.created_at DESC, b.id DESC
         LIMIT 5",
        'i',
        [$user['id']]
    );

    $recentProperties = db_all(
        $conn,
        "SELECT p.*, u.name AS owner_name
         FROM properties p
         LEFT JOIN users u ON u.id = p.user_id
         WHERE p.status = 'approved'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 4"
    );
}

render_page_start('Dashboard', $isAgent ? 'Manage your listings and track inbound booking requests.' : 'Track your bookings, payments, and recent property activity.');
?>

<section class="stats-grid">
    <?php foreach ($stats as $label => $value) { ?>
        <article class="stat-card">
            <span class="stat-card__value"><?php echo h((string) $value); ?></span>
            <span class="stat-card__label"><?php echo h(ucwords(str_replace('_', ' ', $label))); ?></span>
        </article>
    <?php } ?>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow"><?php echo $isAgent ? 'LISTING MANAGEMENT' : 'RECENT ACTIVITY'; ?></p>
            <h2><?php echo $isAgent ? 'Recent Properties' : 'Recent Bookings'; ?></h2>
        </div>
        <a class="btn btn--ghost" href="<?php echo $isAgent ? 'add_property.php' : 'properties.php'; ?>">
            <?php echo $isAgent ? 'Add New Property' : 'Explore Properties'; ?>
        </a>
    </div>

    <?php if (($isAgent ? $recentProperties : $recentBookings) === []) { ?>
        <?php render_empty_state(
            $isAgent ? 'No listings yet' : 'No bookings yet',
            $isAgent ? 'Add your first property to begin the approval workflow.' : 'Browse approved properties and request your first booking.',
            $isAgent ? 'Add Property' : 'Browse Properties',
            $isAgent ? 'add_property.php' : 'properties.php'
        ); ?>
    <?php } else { ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <?php if ($isAgent) { ?>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Updated</th>
                    <?php } else { ?>
                        <th>Property</th>
                        <th>Visit Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($isAgent ? $recentProperties : $recentBookings) as $item) { ?>
                    <tr>
                        <td><?php echo h($item['title']); ?></td>
                        <?php if ($isAgent) { ?>
                            <td>
                                <?php echo h(trim(($item['city'] ? $item['city'] . ', ' : '') . $item['location'], ', ')); ?>
                            </td>
                            <td>
                                <span class="badge badge--<?php echo h(property_status_class((string) $item['status'])); ?>">
                                    <?php echo h(ucfirst((string) $item['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo h(format_date((string) $item['updated_at'], 'Recently')); ?></td>
                        <?php } else { ?>
                            <td><?php echo h(format_date((string) $item['visit_date'])); ?></td>
                            <td>
                                <span class="badge badge--<?php echo h(property_status_class((string) $item['status'])); ?>">
                                    <?php echo h(ucfirst((string) $item['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($item['payment_status'])) { ?>
                                    <span class="badge badge--<?php echo h(property_status_class((string) $item['payment_status'])); ?>">
                                        <?php echo h(ucfirst((string) $item['payment_status'])); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="badge badge--muted">Pending</span>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<?php if ($isAgent) { ?>
    <section class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">CUSTOMER INTEREST</p>
                <h2>Recent Booking Requests</h2>
            </div>
            <a class="btn btn--ghost" href="properties.php">View All Listings</a>
        </div>

        <?php if ($recentBookings === []) { ?>
            <?php render_empty_state('No booking requests yet', 'Once customers request a visit or booking on your listings, they will appear here.'); ?>
        <?php } else { ?>
            <div class="table-shell">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Visit Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentBookings as $booking) { ?>
                        <tr>
                            <td><?php echo h($booking['customer_name']); ?></td>
                            <td><?php echo h($booking['title']); ?></td>
                            <td><?php echo h(format_date((string) $booking['visit_date'])); ?></td>
                            <td>
                                <span class="badge badge--<?php echo h(property_status_class((string) $booking['status'])); ?>">
                                    <?php echo h(ucfirst((string) $booking['status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </section>
<?php } else { ?>
    <section class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">DISCOVER MORE</p>
                <h2>Recommended Approved Properties</h2>
            </div>
            <a class="btn btn--ghost" href="properties.php">View Catalogue</a>
        </div>

        <?php if ($recentProperties === []) { ?>
            <?php render_empty_state('No approved properties yet', 'Approved inventory will appear here for customers.'); ?>
        <?php } else { ?>
            <div class="card-grid">
                <?php foreach ($recentProperties as $property) { ?>
                    <article class="card">
                        <?php if (!empty($property['image'])) { ?>
                            <img src="uploads/<?php echo h($property['image']); ?>" alt="<?php echo h($property['title']); ?>">
                        <?php } else { ?>
                            <div class="card__placeholder">No image uploaded</div>
                        <?php } ?>
                        <div class="card__body">
                            <div class="card__meta-row">
                                <span class="badge badge--success">Approved</span>
                                <strong class="price-tag"><?php echo currency($property['price']); ?></strong>
                            </div>
                            <h3><?php echo h($property['title']); ?></h3>
                            <p class="card__location"><?php echo h(trim(($property['city'] ? $property['city'] . ', ' : '') . $property['location'], ', ')); ?></p>
                            <p class="card__support">Agent: <?php echo h($property['owner_name'] ?: 'Real Estate Hub'); ?></p>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </section>
<?php } ?>

<?php render_page_end(); ?>
