<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_login();

$bookingId = is_post_request() ? post_int('booking_id') : get_int('booking_id');

if ($bookingId <= 0) {
    set_flash('error', 'Booking not found.');
    redirect('my_bookings.php');
}

$booking = db_one(
    $conn,
    "SELECT b.*, p.title, p.city, p.location, p.price, pay.id AS payment_id, pay.status AS payment_status
     FROM bookings b
     INNER JOIN properties p ON p.id = b.property_id
     LEFT JOIN payments pay ON pay.booking_id = b.id
     WHERE b.id = ? AND b.user_id = ? LIMIT 1",
    'ii',
    [$bookingId, current_user()['id']]
);

if (!$booking) {
    set_flash('error', 'Booking not found.');
    redirect('my_bookings.php');
}

if (!empty($booking['payment_id'])) {
    redirect('payment_receipt.php?id=' . (int) $booking['payment_id']);
}

if ((string) $booking['status'] !== 'confirmed') {
    set_flash('error', 'Payments are available only after an admin confirms the booking.');
    redirect('my_bookings.php');
}

$form = [
    'payment_method' => 'upi',
    'transaction_ref' => '',
    'notes' => '',
];
$errors = [];
$methods = ['upi', 'card', 'netbanking', 'cash', 'wallet'];

if (is_post_request()) {
    verify_csrf();

    $form['payment_method'] = post_string('payment_method', 'upi');
    $form['transaction_ref'] = post_string('transaction_ref');
    $form['notes'] = post_string('notes');

    if (!in_array($form['payment_method'], $methods, true)) {
        $errors[] = 'Choose a valid payment method.';
    }

    if (strlen($form['transaction_ref']) < 6) {
        $errors[] = 'Transaction reference must be at least 6 characters.';
    }

    if (strlen($form['notes']) > 255) {
        $errors[] = 'Notes must stay under 255 characters.';
    }

    if ($errors === []) {
        $invoiceNumber = 'INV-' . date('YmdHis') . '-' . $bookingId;

        db_execute(
            $conn,
            'INSERT INTO payments (booking_id, amount, payment_method, transaction_ref, payment_date, status, invoice_number, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'idssssss',
            [
                $bookingId,
                (float) $booking['price'],
                $form['payment_method'],
                $form['transaction_ref'],
                date('Y-m-d H:i:s'),
                'paid',
                $invoiceNumber,
                $form['notes'],
            ]
        );

        $paymentId = (int) $conn->insert_id;
        db_execute($conn, "UPDATE bookings SET status = 'completed' WHERE id = ?", 'i', [$bookingId]);
        db_execute($conn, "UPDATE properties SET status = 'booked' WHERE id = ?", 'i', [(int) $booking['property_id']]);

        set_flash('success', 'Payment recorded successfully.');
        redirect('payment_receipt.php?id=' . $paymentId);
    }
}

render_page_start('Payment', 'Record payment details and generate an invoice receipt for the confirmed booking.');
?>

<section class="content-grid content-grid--wide">
    <article class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">BOOKING SUMMARY</p>
                <h2><?php echo h($booking['title']); ?></h2>
            </div>
            <span class="price-tag"><?php echo currency($booking['price']); ?></span>
        </div>

        <div class="spec-row">
            <span><?php echo h(trim(($booking['city'] ? $booking['city'] . ', ' : '') . $booking['location'], ', ')); ?></span>
            <span>Visit: <?php echo h(format_date((string) $booking['visit_date'])); ?></span>
            <span>Status: Confirmed</span>
        </div>

        <p>This form records the payment transaction for your confirmed booking and generates a downloadable receipt.</p>
    </article>

    <article class="form-card">
        <h2>Payment Details</h2>
        <p>Use the property price as the booked amount.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" class="stack-md">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="booking_id" value="<?php echo h((string) $bookingId); ?>">

            <label class="field">
                <span>Amount</span>
                <input type="text" value="<?php echo h(strip_tags(currency($booking['price']))); ?>" readonly>
            </label>

            <label class="field">
                <span>Payment Method</span>
                <select name="payment_method" required>
                    <?php foreach ($methods as $method) { ?>
                        <option value="<?php echo h($method); ?>" <?php echo $form['payment_method'] === $method ? 'selected' : ''; ?>>
                            <?php echo h(ucfirst($method)); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>

            <label class="field">
                <span>Transaction Reference</span>
                <input type="text" name="transaction_ref" value="<?php echo h($form['transaction_ref']); ?>" required>
            </label>

            <label class="field">
                <span>Notes</span>
                <textarea name="notes" rows="4" placeholder="Optional payment remarks"><?php echo h($form['notes']); ?></textarea>
            </label>

            <button class="btn btn--primary btn--full" type="submit">Record Payment</button>
        </form>
    </article>
</section>

<?php render_page_end(); ?>
