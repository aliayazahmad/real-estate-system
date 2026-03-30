<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_login();

$paymentId = get_int('id');

if ($paymentId <= 0) {
    set_flash('error', 'Payment receipt not found.');
    redirect('my_bookings.php');
}

$receipt = db_one(
    $conn,
    "SELECT pay.*, b.user_id, b.visit_date, p.title, p.city, p.location, u.name AS customer_name, u.email AS customer_email
     FROM payments pay
     INNER JOIN bookings b ON b.id = pay.booking_id
     INNER JOIN properties p ON p.id = b.property_id
     INNER JOIN users u ON u.id = b.user_id
     WHERE pay.id = ? LIMIT 1",
    'i',
    [$paymentId]
);

if (!$receipt) {
    set_flash('error', 'Payment receipt not found.');
    redirect('my_bookings.php');
}

if (!has_role('admin') && (int) $receipt['user_id'] !== (int) current_user()['id']) {
    set_flash('error', 'You do not have access to that receipt.');
    redirect('my_bookings.php');
}

render_page_start('Payment Receipt', 'Review the invoice and keep a record of the completed payment.');
?>

<section class="receipt-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">INVOICE</p>
            <h2><?php echo h($receipt['invoice_number']); ?></h2>
        </div>
        <button class="btn btn--ghost" type="button" onclick="window.print()">Print Receipt</button>
    </div>

    <div class="receipt-grid">
        <div>
            <h3>Customer</h3>
            <p><?php echo h($receipt['customer_name']); ?></p>
            <p><?php echo h($receipt['customer_email']); ?></p>
        </div>
        <div>
            <h3>Property</h3>
            <p><?php echo h($receipt['title']); ?></p>
            <p><?php echo h(trim(($receipt['city'] ? $receipt['city'] . ', ' : '') . $receipt['location'], ', ')); ?></p>
        </div>
        <div>
            <h3>Payment</h3>
            <p>Amount: <?php echo currency($receipt['amount']); ?></p>
            <p>Method: <?php echo h(ucfirst((string) $receipt['payment_method'])); ?></p>
            <p>Status: <?php echo h(ucfirst((string) $receipt['status'])); ?></p>
        </div>
        <div>
            <h3>Schedule</h3>
            <p>Visit Date: <?php echo h(format_date((string) $receipt['visit_date'])); ?></p>
            <p>Paid On: <?php echo h(date('d M Y, h:i A', strtotime((string) $receipt['payment_date']))); ?></p>
            <p>Reference: <?php echo h($receipt['transaction_ref']); ?></p>
        </div>
    </div>

    <?php if (!empty($receipt['notes'])) { ?>
        <div class="note-block">
            <strong>Notes</strong>
            <p><?php echo h($receipt['notes']); ?></p>
        </div>
    <?php } ?>
</section>

<?php render_page_end(); ?>
