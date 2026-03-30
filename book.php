<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_role('customer');

$propertyId = is_post_request() ? post_int('property_id') : get_int('property_id');

if ($propertyId <= 0) {
    set_flash('error', 'Select a property before creating a booking request.');
    redirect('properties.php');
}

$property = db_one(
    $conn,
    "SELECT p.*, u.name AS owner_name
     FROM properties p
     LEFT JOIN users u ON u.id = p.user_id
     WHERE p.id = ? LIMIT 1",
    'i',
    [$propertyId]
);

if (!$property) {
    set_flash('error', 'Property not found.');
    redirect('properties.php');
}

if (($property['status'] ?? '') !== 'approved') {
    set_flash('error', 'Only approved properties can be booked.');
    redirect('properties.php');
}

if ((int) ($property['user_id'] ?? 0) === (int) current_user()['id']) {
    set_flash('error', 'You cannot book your own listing.');
    redirect('properties.php');
}

$form = [
    'visit_date' => date('Y-m-d', strtotime('+1 day')),
    'message' => '',
];
$errors = [];

if (is_post_request()) {
    verify_csrf();

    $form['visit_date'] = post_string('visit_date');
    $form['message'] = post_string('message');

    if ($form['visit_date'] === '') {
        $errors[] = 'Visit date is required.';
    } elseif (strtotime($form['visit_date']) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Visit date cannot be in the past.';
    }

    if (strlen($form['message']) > 500) {
        $errors[] = 'Message must stay under 500 characters.';
    }

    $openBooking = db_one(
        $conn,
        "SELECT id FROM bookings
         WHERE user_id = ? AND property_id = ? AND status IN ('pending', 'confirmed')
         LIMIT 1",
        'ii',
        [current_user()['id'], $propertyId]
    );

    if ($openBooking) {
        $errors[] = 'You already have an active booking request for this property.';
    }

    if ($errors === []) {
        db_execute(
            $conn,
            'INSERT INTO bookings (user_id, property_id, booking_date, visit_date, message, status) VALUES (?, ?, ?, ?, ?, ?)',
            'iissss',
            [
                current_user()['id'],
                $propertyId,
                date('Y-m-d'),
                $form['visit_date'],
                $form['message'],
                'pending',
            ]
        );

        set_flash('success', 'Booking request submitted. An admin can review and confirm it next.');
        redirect('my_bookings.php');
    }
}

render_page_start('Book Property', 'Capture the requested visit date and booking notes for admin review.');
?>

<section class="content-grid content-grid--wide">
    <article class="section-panel">
        <div class="section-heading">
            <div>
                <p class="eyebrow">SELECTED PROPERTY</p>
                <h2><?php echo h($property['title']); ?></h2>
            </div>
            <span class="price-tag"><?php echo currency($property['price']); ?></span>
        </div>

        <?php if (!empty($property['image'])) { ?>
            <div class="media-preview media-preview--wide">
                <img src="uploads/<?php echo h((string) $property['image']); ?>" alt="<?php echo h($property['title']); ?>">
            </div>
        <?php } ?>

        <div class="spec-row">
            <span><?php echo h(trim(($property['city'] ? $property['city'] . ', ' : '') . $property['location'], ', ')); ?></span>
            <span><?php echo h(ucfirst((string) $property['property_type'])); ?></span>
            <span><?php echo h(ucfirst((string) $property['purpose'])); ?></span>
        </div>

        <p><?php echo h((string) ($property['description'] ?: 'No additional description provided for this property.')); ?></p>
        <p class="card__support">Listed by <?php echo h($property['owner_name'] ?: 'Real Estate Hub'); ?></p>
    </article>

    <article class="form-card">
        <h2>Booking Request</h2>
        <p>Pick a visit date and share any booking notes or preferences.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" class="stack-md">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="property_id" value="<?php echo h((string) $propertyId); ?>">

            <label class="field">
                <span>Requested Visit Date</span>
                <input type="date" name="visit_date" min="<?php echo h(date('Y-m-d')); ?>" value="<?php echo h($form['visit_date']); ?>" required>
            </label>

            <label class="field">
                <span>Message</span>
                <textarea name="message" rows="5" placeholder="Any booking notes, budget range, or visit preferences"><?php echo h($form['message']); ?></textarea>
            </label>

            <button class="btn btn--primary btn--full" type="submit">Submit Booking Request</button>
        </form>
    </article>
</section>

<?php render_page_end(); ?>
