<?php
declare(strict_types=1);

require_once __DIR__ . '/php/layout.php';

$filters = [
    'q' => get_string('q'),
    'city' => get_string('city'),
    'property_type' => get_string('property_type'),
    'purpose' => get_string('purpose'),
    'status' => get_string('status'),
    'min_price' => get_string('min_price'),
    'max_price' => get_string('max_price'),
];

$propertyTypes = ['apartment', 'house', 'villa', 'plot', 'commercial'];
$purposes = ['sale', 'rent'];
$statuses = ['pending', 'approved', 'booked', 'rejected'];
$isAdmin = has_role('admin');
$isAgent = has_role('agent');

$sql = "
    SELECT p.*, u.name AS owner_name, u.email AS owner_email
    FROM properties p
    LEFT JOIN users u ON u.id = p.user_id
    WHERE 1 = 1
";
$params = [];
$types = '';

$citySql = "SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city <> ''";
$cityParams = [];
$cityTypes = '';

if ($isAdmin) {
    if ($filters['status'] !== '' && in_array($filters['status'], $statuses, true)) {
        $sql .= ' AND p.status = ?';
        $types .= 's';
        $params[] = $filters['status'];
    }
} elseif ($isAgent) {
    $sql .= ' AND p.user_id = ?';
    $types .= 'i';
    $params[] = current_user()['id'];

    $citySql .= ' AND user_id = ?';
    $cityTypes .= 'i';
    $cityParams[] = current_user()['id'];

    if ($filters['status'] !== '' && in_array($filters['status'], $statuses, true)) {
        $sql .= ' AND p.status = ?';
        $types .= 's';
        $params[] = $filters['status'];
    }
} else {
    $sql .= " AND p.status = 'approved'";
    $citySql .= " AND status = 'approved'";
}

if ($filters['q'] !== '') {
    $searchTerm = '%' . $filters['q'] . '%';
    $sql .= ' AND (p.title LIKE ? OR p.location LIKE ? OR p.city LIKE ? OR p.description LIKE ?)';
    $types .= 'ssss';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filters['city'] !== '') {
    $sql .= ' AND p.city = ?';
    $types .= 's';
    $params[] = $filters['city'];
}

if ($filters['property_type'] !== '' && in_array($filters['property_type'], $propertyTypes, true)) {
    $sql .= ' AND p.property_type = ?';
    $types .= 's';
    $params[] = $filters['property_type'];
}

if ($filters['purpose'] !== '' && in_array($filters['purpose'], $purposes, true)) {
    $sql .= ' AND p.purpose = ?';
    $types .= 's';
    $params[] = $filters['purpose'];
}

if ($filters['min_price'] !== '' && is_positive_amount($filters['min_price'])) {
    $sql .= ' AND p.price >= ?';
    $types .= 'd';
    $params[] = (float) $filters['min_price'];
}

if ($filters['max_price'] !== '' && is_positive_amount($filters['max_price'])) {
    $sql .= ' AND p.price <= ?';
    $types .= 'd';
    $params[] = (float) $filters['max_price'];
}

$sql .= " ORDER BY
    CASE p.status
        WHEN 'pending' THEN 1
        WHEN 'approved' THEN 2
        WHEN 'booked' THEN 3
        WHEN 'rejected' THEN 4
        ELSE 5
    END,
    p.created_at DESC,
    p.id DESC";

$citySql .= ' ORDER BY city ASC';

$properties = db_all($conn, $sql, $types, $params);
$cities = db_all($conn, $citySql, $cityTypes, $cityParams);

$pageTitle = $isAgent ? 'My Listings' : 'Property Catalogue';
$pageDescription = '';
$filterTitle = $isAgent ? 'Filter Your Listings' : 'Search the Inventory';
$searchPlaceholder = $isAgent ? 'Title, city, description' : 'Title, city, location';
$activeFilterCount = count(array_filter($filters, static fn ($value) => $value !== ''));

render_page_start($pageTitle, $pageDescription);
?>

<section class="section-panel">
    <div class="section-heading section-heading--filters">
        <div>
            <p class="eyebrow">SMART FILTERS</p>
            <h2><?php echo h($filterTitle); ?></h2>
            <p class="section-subcopy">
                <?php echo h($isAgent ? 'This page shows only your own listings.' : 'Use a few focused filters to narrow the catalogue quickly.'); ?>
            </p>
        </div>

        <div class="filter-toolbar">
            <?php if ($activeFilterCount > 0) { ?>
                <span class="badge badge--primary"><?php echo h((string) $activeFilterCount); ?> active</span>
            <?php } ?>
            <?php if (has_role(['agent', 'admin'])) { ?>
                <a class="btn btn--primary" href="add_property.php">Add Property</a>
            <?php } ?>
        </div>
    </div>

    <form method="GET" class="filters-grid filters-grid--catalogue">
        <label class="field field--search">
            <span>Search</span>
            <input type="text" name="q" value="<?php echo h($filters['q']); ?>" placeholder="<?php echo h($searchPlaceholder); ?>">
        </label>

        <label class="field">
            <span>City</span>
            <select name="city">
                <option value="">All Cities</option>
                <?php foreach ($cities as $cityRow) { ?>
                    <option value="<?php echo h($cityRow['city']); ?>" <?php echo $filters['city'] === $cityRow['city'] ? 'selected' : ''; ?>>
                        <?php echo h($cityRow['city']); ?>
                    </option>
                <?php } ?>
            </select>
        </label>

        <label class="field">
            <span>Type</span>
            <select name="property_type">
                <option value="">All Types</option>
                <?php foreach ($propertyTypes as $type) { ?>
                    <option value="<?php echo h($type); ?>" <?php echo $filters['property_type'] === $type ? 'selected' : ''; ?>>
                        <?php echo h(ucfirst($type)); ?>
                    </option>
                <?php } ?>
            </select>
        </label>

        <label class="field">
            <span>Purpose</span>
            <select name="purpose">
                <option value="">Sale & Rent</option>
                <?php foreach ($purposes as $purpose) { ?>
                    <option value="<?php echo h($purpose); ?>" <?php echo $filters['purpose'] === $purpose ? 'selected' : ''; ?>>
                        <?php echo h(ucfirst($purpose)); ?>
                    </option>
                <?php } ?>
            </select>
        </label>

        <label class="field">
            <span>Minimum Price</span>
            <input type="number" min="0" name="min_price" value="<?php echo h($filters['min_price']); ?>" placeholder="0">
        </label>

        <label class="field">
            <span>Maximum Price</span>
            <input type="number" min="0" name="max_price" value="<?php echo h($filters['max_price']); ?>" placeholder="5000000">
        </label>

        <?php if (has_role(['agent', 'admin'])) { ?>
            <label class="field">
                <span>Status</span>
                <select name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $status) { ?>
                        <option value="<?php echo h($status); ?>" <?php echo $filters['status'] === $status ? 'selected' : ''; ?>>
                            <?php echo h(ucfirst($status)); ?>
                        </option>
                    <?php } ?>
                </select>
            </label>
        <?php } ?>

        <div class="form-actions form-actions--filters">
            <button class="btn btn--primary" type="submit">Apply Filters</button>
            <a class="btn btn--ghost" href="properties.php">Reset</a>
        </div>
    </form>
</section>

<?php if ($properties === []) { ?>
    <?php render_empty_state('No properties matched your search', ''); ?>
<?php } else { ?>
    <section class="card-grid">
        <?php foreach ($properties as $property) { ?>
            <?php
            $isOwner = is_logged_in() && (int) ($property['user_id'] ?? 0) === (int) current_user()['id'];
            $city = trim((string) ($property['city'] ?? ''));
            $location = trim((string) ($property['location'] ?? ''));
            $description = trim((string) ($property['description'] ?? ''));
            $summary = $description !== '' ? (strlen($description) > 135 ? substr($description, 0, 132) . '...' : $description) : 'Property details are available for this listing.';
            $showLocation = $location !== '' && strcasecmp($location, $city) !== 0;
            ?>
            <article class="card">
                <?php if (!empty($property['image'])) { ?>
                    <img src="uploads/<?php echo h($property['image']); ?>" alt="<?php echo h($property['title']); ?>">
                <?php } else { ?>
                    <div class="card__placeholder">Image coming soon</div>
                <?php } ?>

                <div class="card__body">
                    <div class="card__meta-row">
                        <span class="badge badge--<?php echo h(property_status_class((string) $property['status'])); ?>">
                            <?php echo h(ucfirst((string) $property['status'])); ?>
                        </span>
                        <strong class="price-tag"><?php echo currency($property['price']); ?></strong>
                    </div>

                    <h3><?php echo h($property['title']); ?></h3>
                    <?php if ($city !== '') { ?>
                        <p class="card__detail">
                            <span class="card__detail-label">City:</span>
                            <?php echo h($city); ?>
                        </p>
                    <?php } ?>
                    <?php if ($showLocation) { ?>
                        <p class="card__detail">
                            <span class="card__detail-label">Location:</span>
                            <?php echo h($location); ?>
                        </p>
                    <?php } ?>
                    <p class="card__detail card__copy">
                        <span class="card__detail-label">Description:</span>
                        <?php echo h($summary); ?>
                    </p>

                    <div class="spec-row">
                        <span><?php echo h(ucfirst((string) $property['property_type'])); ?></span>
                        <span><?php echo h(ucfirst((string) $property['purpose'])); ?></span>
                        <span><?php echo h((string) ($property['bedrooms'] ?: 0)); ?> Bed</span>
                        <span><?php echo h((string) ($property['bathrooms'] ?: 0)); ?> Bath</span>
                        <span><?php echo h((string) ($property['area_sqft'] ?: 0)); ?> sqft</span>
                    </div>

                    <p class="card__support">
                        Listed by <?php echo h($isOwner ? 'You' : ($property['owner_name'] ?: 'Real Estate Hub')); ?>
                        <?php if ($isAdmin) { ?>
                            <br>Email: <?php echo h($property['owner_email'] ?: 'N/A'); ?>
                        <?php } ?>
                    </p>

                    <?php if (!is_logged_in() || (has_role('customer') && $property['status'] === 'approved' && !$isOwner) || (has_role(['agent', 'admin']) && ($isAdmin || $isOwner))) { ?>
                        <div class="card__actions">
                            <?php if (!is_logged_in()) { ?>
                                <a class="btn btn--primary" href="login.php">Login to Book</a>
                            <?php } elseif (has_role('customer') && $property['status'] === 'approved' && !$isOwner) { ?>
                                <a class="btn btn--primary" href="book.php?property_id=<?php echo h((string) $property['id']); ?>">Request Booking</a>
                            <?php } elseif (has_role(['agent', 'admin']) && ($isAdmin || $isOwner)) { ?>
                                <a class="btn btn--ghost" href="edit_property.php?id=<?php echo h((string) $property['id']); ?>">Edit</a>

                                <form method="POST" action="delete.php">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo h((string) $property['id']); ?>">
                                    <button class="btn btn--danger" type="submit" data-confirm="Delete this property listing?">Delete</button>
                                </form>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </article>
        <?php } ?>
    </section>
<?php } ?>

<?php render_page_end(); ?>
