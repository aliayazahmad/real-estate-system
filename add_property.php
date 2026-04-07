<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

require_role(['agent', 'admin']);

$propertyTypes = ['apartment', 'house', 'villa', 'plot', 'commercial'];
$purposes = ['sale', 'rent'];
$form = [
    'title' => '',
    'city' => '',
    'location' => '',
    'price' => '',
    'property_type' => 'apartment',
    'purpose' => 'sale',
    'bedrooms' => '',
    'bathrooms' => '',
    'area_sqft' => '',
    'description' => '',
];
$errors = [];

if (is_post_request()) {
    verify_csrf();

    foreach ($form as $key => $value) {
        $form[$key] = post_string($key);
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
        $errors[] = 'Choose whether the property is for sale or rent.';
    }

    $duplicate = db_one(
        $conn,
        'SELECT id FROM properties WHERE LOWER(TRIM(title)) = LOWER(TRIM(?)) AND LOWER(TRIM(location)) = LOWER(TRIM(?)) LIMIT 1',
        'ss',
        [$form['title'], $form['location']]
    );

    if ($duplicate) {
        $errors[] = 'A property with the same title and location already exists.';
    }

    if ($errors === []) {
        try {
            $image = upload_image('image');
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }

    if ($errors === []) {
        $status = has_role('admin') ? 'approved' : 'pending';

        db_execute(
            $conn,
            'INSERT INTO properties (user_id, title, city, location, price, property_type, purpose, bedrooms, bathrooms, area_sqft, description, image, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'isssdssiiisss',
            [
                current_user()['id'],
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
                $image ?? '',
                $status,
            ]
        );

        set_flash('success', has_role('admin') ? 'Property added and approved.' : 'Property submitted for admin approval.');
        redirect(has_role('admin') ? 'properties.php' : 'dashboard.php');
    }
}

render_page_start('Add Property');
?>

<section class="form-shell">
    <div class="form-card form-card--wide">
        <h2>New Property Listing</h2>
        <p>Agents submit listings for approval. Admins can publish directly.</p>

        <?php if ($errors !== []) { ?>
            <div class="alert alert--danger">
                <?php foreach ($errors as $error) { ?>
                    <p><?php echo h($error); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" class="grid-form">
            <?php echo csrf_field(); ?>

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

            <label class="field">
                <span>Listing Image</span>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            </label>

            <label class="field field--full">
                <span>Description</span>
                <textarea name="description" rows="5" placeholder="Add property highlights, amenities, or sale terms"><?php echo h($form['description']); ?></textarea>
            </label>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Save Listing</button>
            </div>
        </form>
    </div>
</section>

<?php render_page_end(); ?>
