<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_role('customer');

if (is_post_request()) {
    verify_csrf();

    $action = post_string('action');
    $bookingId = post_int('booking_id');

    if ($action === 'cancel_booking' && $bookingId > 0) {
        $booking = db_one(
            $conn,
            "SELECT b.*, pay.id AS payment_id
             FROM bookings b
             LEFT JOIN payments pay ON pay.booking_id = b.id
             WHERE b.id = ? AND b.user_id = ? LIMIT 1",
            'ii',
            [$bookingId, current_user()['id']]
        );

        if (!$booking) {
            set_flash('error', 'Booking not found.');
        } elseif (!booking_is_open((string) $booking['status']) || !empty($booking['payment_id'])) {
            set_flash('error', 'That booking can no longer be cancelled.');
        } else {
            db_execute($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = ?", 'i', [$bookingId]);

            if ((string) $booking['status'] === 'confirmed') {
                db_execute($conn, "UPDATE properties SET status = 'approved' WHERE id = ?", 'i', [(int) $booking['property_id']]);
            }

            set_flash('success', 'Booking cancelled successfully.');
        }

        redirect('my_bookings.php');
    }
}

$bookings = db_all(
    $conn,
    "SELECT b.*, p.title, p.city, p.location, p.price, p.image, pay.id AS payment_id, pay.status AS payment_status, pay.invoice_number
     FROM bookings b
     INNER JOIN properties p ON p.id = b.property_id
     LEFT JOIN payments pay ON pay.booking_id = b.id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC, b.id DESC",
    'i',
    [current_user()['id']]
);

render_page_start('My Bookings', 'Track booking approvals, payment status, and visit schedules.');
?>

<?php if ($bookings === []) { ?>
    <?php render_empty_state('No bookings yet', 'Explore approved listings and submit your first booking request.', 'Browse Properties', 'properties.php'); ?>
<?php } else { ?>
    <section class="card-grid">
        <?php foreach ($bookings as $booking) { ?>
            <article class="card">
                <?php if (!empty($booking['image'])) { ?>
                    <img src="uploads/<?php echo h((string) $booking['image']); ?>" alt="<?php echo h($booking['title']); ?>">
                <?php } else { ?>
                    <div class="card__placeholder">No image uploaded</div>
                <?php } ?>

                <div class="card__body">
                    <div class="card__meta-row">
                        <span class="badge badge--<?php echo h(property_status_class((string) $booking['status'])); ?>">
                            <?php echo h(ucfirst((string) $booking['status'])); ?>
                        </span>
                        <strong class="price-tag"><?php echo currency($booking['price']); ?></strong>
                    </div>

                    <h3><?php echo h($booking['title']); ?></h3>
                    <p class="card__location"><?php echo h(trim(($booking['city'] ? $booking['city'] . ', ' : '') . $booking['location'], ', ')); ?></p>
                    <p class="card__copy">Requested on <?php echo h(format_date((string) $booking['booking_date'])); ?>. Visit date: <?php echo h(format_date((string) $booking['visit_date'])); ?>.</p>

                    <?php if (!empty($booking['payment_status'])) { ?>
                        <p class="card__support">
                            Payment:
                            <span class="badge badge--<?php echo h(property_status_class((string) $booking['payment_status'])); ?>">
                                <?php echo h(ucfirst((string) $booking['payment_status'])); ?>
                            </span>
                        </p>
                    <?php } ?>

                    <div class="card__actions">
                        <?php if (booking_can_be_paid($booking)) { ?>
                            <a class="btn btn--primary" href="payments.php?booking_id=<?php echo h((string) $booking['id']); ?>">Pay Now</a>
                        <?php } ?>

                        <?php if (!empty($booking['payment_id'])) { ?>
                            <a class="btn btn--ghost" href="payment_receipt.php?id=<?php echo h((string) $booking['payment_id']); ?>">View Receipt</a>
                        <?php } ?>

                        <?php if (booking_is_open((string) $booking['status']) && empty($booking['payment_id'])) { ?>
                            <form method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="cancel_booking">
                                <input type="hidden" name="booking_id" value="<?php echo h((string) $booking['id']); ?>">
                                <button class="btn btn--danger" type="submit" data-confirm="Cancel this booking request?">Cancel</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </article>
        <?php } ?>
    </section>
<?php } ?>

<?php render_page_end(); ?>
