<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_role('admin');

if (is_post_request()) {
    verify_csrf();

    $action = post_string('action');

    if ($action === 'property_status') {
        $propertyId = post_int('property_id');
        $status = post_string('status');

        if ($propertyId > 0 && in_array($status, ['approved', 'rejected', 'pending'], true)) {
            db_execute($conn, 'UPDATE properties SET status = ? WHERE id = ?', 'si', [$status, $propertyId]);
            set_flash('success', 'Property status updated.');
        }
    }

    if ($action === 'booking_status') {
        $bookingId = post_int('booking_id');
        $status = post_string('status');
        $booking = db_one($conn, 'SELECT * FROM bookings WHERE id = ? LIMIT 1', 'i', [$bookingId]);

        if ($booking && in_array($status, ['confirmed', 'cancelled'], true)) {
            $payment = db_one($conn, 'SELECT id FROM payments WHERE booking_id = ? LIMIT 1', 'i', [$bookingId]);

            if ($status === 'cancelled' && $payment) {
                set_flash('error', 'Paid bookings should not be cancelled from the admin dashboard.');
            } else {
                db_execute($conn, 'UPDATE bookings SET status = ? WHERE id = ?', 'si', [$status, $bookingId]);

                if ($status === 'confirmed') {
                    db_execute($conn, "UPDATE properties SET status = 'booked' WHERE id = ?", 'i', [(int) $booking['property_id']]);
                    db_execute(
                        $conn,
                        "UPDATE bookings SET status = 'cancelled' WHERE property_id = ? AND id <> ? AND status = 'pending'",
                        'ii',
                        [(int) $booking['property_id'], $bookingId]
                    );
                } else {
                    db_execute($conn, "UPDATE properties SET status = 'approved' WHERE id = ?", 'i', [(int) $booking['property_id']]);
                }

                set_flash('success', 'Booking status updated.');
            }
        }
    }

    redirect('admin_dashboard.php');
}

$stats = [
    'users' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM users') ?? 0),
    'properties' => (int) (db_scalar($conn, 'SELECT COUNT(*) FROM properties') ?? 0),
    'pending_properties' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM properties WHERE status = 'pending'") ?? 0),
    'pending_bookings' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM bookings WHERE status = 'pending'") ?? 0),
    'paid_payments' => (int) (db_scalar($conn, "SELECT COUNT(*) FROM payments WHERE status = 'paid'") ?? 0),
];

$pendingProperties = db_all(
    $conn,
    "SELECT p.*, u.name AS owner_name, u.email AS owner_email
     FROM properties p
     LEFT JOIN users u ON u.id = p.user_id
     WHERE p.status = 'pending'
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT 8"
);

$pendingBookings = db_all(
    $conn,
    "SELECT b.*, p.title, p.city, p.location, u.name AS customer_name, u.email AS customer_email
     FROM bookings b
     INNER JOIN properties p ON p.id = b.property_id
     INNER JOIN users u ON u.id = b.user_id
     WHERE b.status = 'pending'
     ORDER BY b.created_at DESC, b.id DESC
     LIMIT 8"
);

$recentPayments = db_all(
    $conn,
    "SELECT pay.*, p.title, u.name AS customer_name
     FROM payments pay
     INNER JOIN bookings b ON b.id = pay.booking_id
     INNER JOIN properties p ON p.id = b.property_id
     INNER JOIN users u ON u.id = b.user_id
     ORDER BY pay.payment_date DESC, pay.id DESC
     LIMIT 8"
);

render_page_start('Admin Dashboard', 'Review pending approvals, booking requests, revenue records, and system health in one place.');
?>

<section class="stats-grid stats-grid--wide">
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
            <p class="eyebrow">LISTING APPROVALS</p>
            <h2>Pending Properties</h2>
        </div>
        <a class="btn btn--ghost" href="reports.php">Open Reports</a>
    </div>

    <?php if ($pendingProperties === []) { ?>
        <?php render_empty_state('No pending properties', 'Agents have no listings waiting for approval right now.'); ?>
    <?php } else { ?>
        <div class="card-grid">
            <?php foreach ($pendingProperties as $property) { ?>
                <article class="card">
                    <?php if (!empty($property['image'])) { ?>
                        <img src="uploads/<?php echo h((string) $property['image']); ?>" alt="<?php echo h($property['title']); ?>">
                    <?php } else { ?>
                        <div class="card__placeholder">No image uploaded</div>
                    <?php } ?>
                    <div class="card__body">
                        <div class="card__meta-row">
                            <span class="badge badge--warning">Pending</span>
                            <strong class="price-tag"><?php echo currency($property['price']); ?></strong>
                        </div>
                        <h3><?php echo h($property['title']); ?></h3>
                        <p class="card__location"><?php echo h(trim(($property['city'] ? $property['city'] . ', ' : '') . $property['location'], ', ')); ?></p>
                        <p class="card__support"><?php echo h($property['owner_name'] ?: 'Unknown Agent'); ?> &middot; <?php echo h($property['owner_email'] ?: 'No email'); ?></p>

                        <div class="card__actions">
                            <form method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="property_status">
                                <input type="hidden" name="property_id" value="<?php echo h((string) $property['id']); ?>">
                                <input type="hidden" name="status" value="approved">
                                <button class="btn btn--primary" type="submit">Approve</button>
                            </form>
                            <form method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="property_status">
                                <input type="hidden" name="property_id" value="<?php echo h((string) $property['id']); ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn--danger" type="submit">Reject</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">BOOKING REVIEWS</p>
            <h2>Pending Booking Requests</h2>
        </div>
    </div>

    <?php if ($pendingBookings === []) { ?>
        <?php render_empty_state('No pending bookings', 'New customer requests will appear here for review.'); ?>
    <?php } else { ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Property</th>
                    <th>Visit Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pendingBookings as $booking) { ?>
                    <tr>
                        <td><?php echo h($booking['customer_name']); ?><br><small><?php echo h($booking['customer_email']); ?></small></td>
                        <td><?php echo h($booking['title']); ?><br><small><?php echo h(trim(($booking['city'] ? $booking['city'] . ', ' : '') . $booking['location'], ', ')); ?></small></td>
                        <td><?php echo h(format_date((string) $booking['visit_date'])); ?></td>
                        <td><span class="badge badge--warning">Pending</span></td>
                        <td>
                            <div class="inline-actions">
                                <form method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="booking_status">
                                    <input type="hidden" name="booking_id" value="<?php echo h((string) $booking['id']); ?>">
                                    <input type="hidden" name="status" value="confirmed">
                                    <button class="btn btn--primary" type="submit">Confirm</button>
                                </form>
                                <form method="POST">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="booking_status">
                                    <input type="hidden" name="booking_id" value="<?php echo h((string) $booking['id']); ?>">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn--danger" type="submit">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<section class="section-panel">
    <div class="section-heading">
        <div>
            <p class="eyebrow">PAYMENT LOG</p>
            <h2>Recent Payments</h2>
        </div>
        <a class="btn btn--ghost" href="reports.php?export=payments">Export CSV</a>
    </div>

    <?php if ($recentPayments === []) { ?>
        <?php render_empty_state('No payment records yet', 'Once customers complete payments, invoice records will appear here.'); ?>
    <?php } else { ?>
        <div class="table-shell">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Property</th>
                    <th>Amount</th>
                    <th>Paid On</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentPayments as $payment) { ?>
                    <tr>
                        <td><?php echo h($payment['invoice_number']); ?></td>
                        <td><?php echo h($payment['customer_name']); ?></td>
                        <td><?php echo h($payment['title']); ?></td>
                        <td><?php echo currency($payment['amount']); ?></td>
                        <td><?php echo h(date('d M Y, h:i A', strtotime((string) $payment['payment_date']))); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<?php render_page_end(); ?>
