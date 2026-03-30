<?php
declare(strict_types=1);

require_once __DIR__ . '/php/bootstrap.php';

require_role(['agent', 'admin']);

if (!is_post_request()) {
    redirect('properties.php');
}

verify_csrf();

$propertyId = post_int('id');

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
    set_flash('error', 'You can only delete your own property listings.');
    redirect('properties.php');
}

$bookingCount = (int) (db_scalar($conn, 'SELECT COUNT(*) FROM bookings WHERE property_id = ?', 'i', [$propertyId]) ?? 0);

if ($bookingCount > 0) {
    set_flash('error', 'This property already has booking records and cannot be deleted.');
    redirect('properties.php');
}

db_execute($conn, 'DELETE FROM properties WHERE id = ?', 'i', [$propertyId]);
delete_image((string) ($property['image'] ?? ''));

set_flash('success', 'Property deleted successfully.');
redirect('properties.php');
