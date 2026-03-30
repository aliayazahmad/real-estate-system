<?php
declare(strict_types=1);

require_once __DIR__ . '/php/bootstrap.php';

require_role('admin');

$export = get_string('export');

if ($export !== '') {
    $datasets = [
        'properties' => [
            'file' => 'properties-report.csv',
            'headers' => ['Title', 'City', 'Location', 'Type', 'Purpose', 'Price', 'Status', 'Owner', 'Created At'],
            'rows' => db_all(
                $conn,
                "SELECT p.title, p.city, p.location, p.property_type, p.purpose, p.price, p.status, u.name AS owner_name, p.created_at
                 FROM properties p
                 LEFT JOIN users u ON u.id = p.user_id
                 ORDER BY p.created_at DESC, p.id DESC"
            ),
        ],
        'bookings' => [
            'file' => 'bookings-report.csv',
            'headers' => ['Customer', 'Property', 'Booking Date', 'Visit Date', 'Status', 'Message'],
            'rows' => db_all(
                $conn,
                "SELECT u.name AS customer_name, p.title, b.booking_date, b.visit_date, b.status, b.message
                 FROM bookings b
                 INNER JOIN users u ON u.id = b.user_id
                 INNER JOIN properties p ON p.id = b.property_id
                 ORDER BY b.created_at DESC, b.id DESC"
            ),
        ],
        'payments' => [
            'file' => 'payments-report.csv',
            'headers' => ['Invoice', 'Customer', 'Property', 'Amount', 'Method', 'Reference', 'Status', 'Paid On'],
            'rows' => db_all(
                $conn,
                "SELECT pay.invoice_number, u.name AS customer_name, p.title, pay.amount, pay.payment_method, pay.transaction_ref, pay.status, pay.payment_date
                 FROM payments pay
                 INNER JOIN bookings b ON b.id = pay.booking_id
                 INNER JOIN users u ON u.id = b.user_id
                 INNER JOIN properties p ON p.id = b.property_id
                 ORDER BY pay.payment_date DESC, pay.id DESC"
            ),
        ],
    ];

    if (!isset($datasets[$export])) {
        set_flash('error', 'Unknown export requested.');
        redirect('reports.php');
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $datasets[$export]['file'] . '"');

    $stream = fopen('php://output', 'w');
    fputcsv($stream, $datasets[$export]['headers']);

    foreach ($datasets[$export]['rows'] as $row) {
        fputcsv($stream, array_values($row));
    }

    fclose($stream);
    exit();
}

require_once __DIR__ . '/php/layout.php';

$propertyBreakdown = db_all($conn, 'SELECT status, COUNT(*) AS total FROM properties GROUP BY status ORDER BY total DESC');
$bookingBreakdown = db_all($conn, 'SELECT status, COUNT(*) AS total FROM bookings GROUP BY status ORDER BY total DESC');
$topCities = db_all(
    $conn,
    "SELECT city, COUNT(*) AS total
     FROM properties
     WHERE city IS NOT NULL AND city <> ''
     GROUP BY city
     ORDER BY total DESC, city ASC
     LIMIT 5"
);
$totalRevenue = (float) (db_scalar($conn, "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'") ?? 0);

render_page_start('Reports', 'Export inventory, bookings, and payment records while monitoring status breakdowns.');
?>

<section class="stats-grid stats-grid--wide">
    <article class="stat-card">
        <span class="stat-card__value"><?php echo currency($totalRevenue); ?></span>
        <span class="stat-card__label">Total Recorded Revenue</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?php echo h((string) count($propertyBreakdown)); ?></span>
        <span class="stat-card__label">Property Status Buckets</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?php echo h((string) count($bookingBreakdown)); ?></span>
        <span class="stat-card__label">Booking Status Buckets</span>
    </article>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">EXPORTS</p>
            <h2>Download Operational Data</h2>
        </div>
    </div>

    <div class="action-row">
        <a class="btn btn--primary" href="reports.php?export=properties">Export Properties CSV</a>
        <a class="btn btn--ghost" href="reports.php?export=bookings">Export Bookings CSV</a>
        <a class="btn btn--ghost" href="reports.php?export=payments">Export Payments CSV</a>
    </div>
</section>

<section class="content-grid">
    <article class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">PROPERTY HEALTH</p>
                <h2>Status Breakdown</h2>
            </div>
        </div>

        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($propertyBreakdown as $row) { ?>
                    <tr>
                        <td>
                            <span class="badge badge--<?php echo h(property_status_class((string) $row['status'])); ?>">
                                <?php echo h(ucfirst((string) $row['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo h((string) $row['total']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">BOOKING HEALTH</p>
                <h2>Status Breakdown</h2>
            </div>
        </div>

        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bookingBreakdown as $row) { ?>
                    <tr>
                        <td>
                            <span class="badge badge--<?php echo h(property_status_class((string) $row['status'])); ?>">
                                <?php echo h(ucfirst((string) $row['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo h((string) $row['total']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">INVENTORY DISTRIBUTION</p>
            <h2>Top Cities</h2>
        </div>
    </div>

    <?php if ($topCities === []) { ?>
        <?php render_empty_state('No city data yet', 'Add properties with city values to see geographic distribution.'); ?>
    <?php } else { ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th>City</th>
                    <th>Property Count</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($topCities as $city) { ?>
                    <tr>
                        <td><?php echo h($city['city']); ?></td>
                        <td><?php echo h((string) $city['total']); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<?php render_page_end(); ?>
