<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_role(['agent', 'admin']);

$propertyTypes = ['apartment', 'house', 'villa', 'plot', 'commercial'];
$purposes = ['sale', 'rent'];
$statuses = ['pending', 'approved', 'booked', 'rejected'];
$propertyId = is_post_request() ? post_int('id') : get_int('id');

if ($propertyId <= 0) {
    set_flash('error', 'Property not found.');
    redirect('properties.php');
}

$property = db_one($conn, 'SELECT * FROM properties WHERE id = ? LIMIT 1', 'i', [$propertyId]);

if (!$property) {
    set_flash('error', 'Property not found.');
    redirect('properties.php');
}

$isOwner = (int) ($property['user_id'] ?? 0) === (int) current_user()['id'];

if (!has_role('admin') && !$isOwner) {
    set_flash('error', 'You can only edit your own listings.');
    redirect('properties.php');
}

$form = [
    'title' => (string) $property['title'],
    'city' => (string) ($property['city'] ?? ''),
    'location' => (string) $property['location'],
    'price' => (string) $property['price'],
    'property_type' => (string) ($property['property_type'] ?? 'apartment'),
    'purpose' => (string) ($property['purpose'] ?? 'sale'),
    'bedrooms' => (string) ($property['bedrooms'] ?? ''),
    'bathrooms' => (string) ($property['bathrooms'] ?? ''),
    'area_sqft' => (string) ($property['area_sqft'] ?? ''),
    'description' => (string) ($property['description'] ?? ''),
    'status' => (string) ($property['status'] ?? 'pending'),
];
$errors = [];

if (is_post_request()) {
    verify_csrf();

    foreach ($form as $key => $value) {
        $form[$key] = post_string($key, $value);
    }

    if ($form['title'] === '') {
        $errors[] = 'Property title is required.';
    }
    if ($form['city'] === '') {
        $errors[] = 'City is required.';
    }
    if ($form['location'] === '') {
        $errors[] = 'Location is required.';
    }
    if (!is_positive_amount($form['price'])) {
        $errors[] = 'Enter a valid property price.';
    }
    if (!in_array($form['property_type'], $propertyTypes, true)) {
        $errors[] = 'Choose a valid property type.';
    }
    if (!in_array($form['purpose'], $purposes, true)) {
        $errors[] = 'Choose a valid property purpose.';
    }
    if (has_role('admin') && !in_array($form['status'], $statuses, true)) {
        $errors[] = 'Choose a valid property status.';
    }

    $duplicate = db_one(
        $conn,
        'SELECT id FROM properties WHERE id <> ? AND LOWER(TRIM(title)) = LOWER(TRIM(?)) AND LOWER(TRIM(location)) = LOWER(TRIM(?)) LIMIT 1',
        'iss',
        [$propertyId, $form['title'], $form['location']]
    );

    if ($duplicate) {
        $errors[] = 'Another property already uses the same title and location.';
    }

    if ($errors === []) {
        try {
            $image = upload_image('image', (string) ($property['image'] ?? ''));
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if ($errors === []) {
        $status = has_role('admin')
            ? $form['status']
            : (($property['status'] ?? '') === 'booked' ? 'booked' : 'pending');

        db_execute(
            $conn,
            'UPDATE properties
             SET title = ?, city = ?, location = ?, price = ?, property_type = ?, purpose = ?, bedrooms = ?, bathrooms = ?, area_sqft = ?, description = ?, image = ?, status = ?
             WHERE id = ?',
            'sssdssiissssi',
            [
                $form['title'],
                $form['city'],
                $form['location'],
                (float) $form['price'],
                $form['property_type'],
                $form['purpose'],
                $form['bedrooms'] !== '' ? (int) $form['bedrooms'] : null,
                $form['bathrooms'] !== '' ? (int) $form['bathrooms'] : null,
                $form['area_sqft'] !== '' ? (int) $form['area_sqft'] : null,
                $form['description'],
                $image,
                $status,
                $propertyId,
            ]
        );

        set_flash('success', has_role('admin') ? 'Property updated successfully.' : 'Property updated and moved back to pending review.');
        redirect('properties.php');
    }
}

render_page_start('Edit Property', 'Update listing details, specifications, media, and approval status.');
?>

<section class="form-shell">
    <div class="form-card form-card--wide">
        <h2>Edit Listing</h2>
        <p>Agents can update their own listings. Admins can also adjust publication status.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" class="grid-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id" value="<?php echo h((string) $propertyId); ?>">

            <label class="field">
                <span>Property Title</span>
                <input type="text" name="title" value="<?php echo h($form['title']); ?>" required>
            </label>

            <label class="field">
                <span>City</span>
                <input type="text" name="city" value="<?php echo h($form['city']); ?>" required>
            </label>

            <label class="field">
                <span>Location</span>
                <input type="text" name="location" value="<?php echo h($form['location']); ?>" required>
            </label>

            <label class="field">
                <span>Price</span>
                <input type="number" min="1" step="0.01" name="price" value="<?php echo h($form['price']); ?>" required>
            </label>

            <label class="field">
                <span>Property Type</span>
                <select name="property_type" required>
                    <?php foreach ($propertyTypes as $type) { ?>
                        <option value="<?php echo h($type); ?>" <?php echo $form['property_type'] === $type ? 'selected' : ''; ?>>
                            <?php echo h(ucfirst($type)); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>

            <label class="field">
                <span>Purpose</span>
                <select name="purpose" required>
                    <?php foreach ($purposes as $purpose) { ?>
                        <option value="<?php echo h($purpose); ?>" <?php echo $form['purpose'] === $purpose ? 'selected' : ''; ?>>
                            <?php echo h(ucfirst($purpose)); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>

            <label class="field">
                <span>Bedrooms</span>
                <input type="number" min="0" name="bedrooms" value="<?php echo h($form['bedrooms']); ?>">
            </label>

            <label class="field">
                <span>Bathrooms</span>
                <input type="number" min="0" name="bathrooms" value="<?php echo h($form['bathrooms']); ?>">
            </label>

            <label class="field">
                <span>Area (sqft)</span>
                <input type="number" min="0" name="area_sqft" value="<?php echo h($form['area_sqft']); ?>">
            </label>

            <?php if (has_role('admin')) { ?>
                <label class="field">
                    <span>Status</span>
                    <select name="status">
                        <?php foreach ($statuses as $status) { ?>
                            <option value="<?php echo h($status); ?>" <?php echo $form['status'] === $status ? 'selected' : ''; ?>>
                                <?php echo h(ucfirst($status)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </label>
            <?php } ?>

            <label class="field field--full">
                <span>Description</span>
                <textarea name="description" rows="5"><?php echo h($form['description']); ?></textarea>
            </label>

            <label class="field">
                <span>Replace Image</span>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            </label>

            <?php if (!empty($property['image'])) { ?>
                <div class="media-preview">
                    <img src="uploads/<?php echo h((string) $property['image']); ?>" alt="<?php echo h($property['title']); ?>">
                </div>
            <?php } ?>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Update Listing</button>
            </div>
        </form>
    </div>
</section>

<?php render_page_end(); ?>
