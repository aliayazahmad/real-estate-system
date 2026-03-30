<?php
declare(strict_types=1);

function db_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [
        'host' => getenv('REAL_ESTATE_DB_HOST') ?: '127.0.0.1',
        'port' => (int) (getenv('REAL_ESTATE_DB_PORT') ?: 3306),
        'name' => getenv('REAL_ESTATE_DB_NAME') ?: 'real_estate',
        'user' => getenv('REAL_ESTATE_DB_USER') ?: 'root',
        'pass' => getenv('REAL_ESTATE_DB_PASS') ?: '',
    ];

    return $config;
}

function db_connection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $config = db_config();
    $databaseName = str_replace('`', '', $config['name']);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $connection = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            '',
            $config['port']
        );

        $connection->set_charset('utf8mb4');
        $connection->query(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        $connection->select_db($databaseName);

        ensure_schema($connection, $databaseName);
    } catch (Throwable $exception) {
        http_response_code(500);
        $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');

        die(
            "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\"><title>Database Error</title>"
            . "<style>body{font-family:Trebuchet MS,Segoe UI,sans-serif;background:#f7f4ef;color:#1f2937;margin:0;"
            . "padding:48px}.panel{max-width:720px;margin:0 auto;background:#fff;border-radius:20px;padding:32px;"
            . "box-shadow:0 18px 45px rgba(15,23,42,.12)}h1{margin-top:0}code{background:#f1f5f9;padding:2px 6px;"
            . "border-radius:6px}</style></head><body><div class=\"panel\"><h1>Database connection failed</h1>"
            . "<p>Start MySQL and confirm your credentials in the environment variables "
            . "<code>REAL_ESTATE_DB_HOST</code>, <code>REAL_ESTATE_DB_PORT</code>, "
            . "<code>REAL_ESTATE_DB_NAME</code>, <code>REAL_ESTATE_DB_USER</code>, and "
            . "<code>REAL_ESTATE_DB_PASS</code>.</p><p><strong>Error:</strong> {$message}</p></div></body></html>"
        );
    }

    return $connection;
}

function ensure_schema(mysqli $connection, string $databaseName): void
{
    $connection->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL,
            phone VARCHAR(20) NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'customer',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $connection->query(
        "CREATE TABLE IF NOT EXISTS properties (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            title VARCHAR(150) NOT NULL,
            location VARCHAR(180) NOT NULL,
            city VARCHAR(100) NULL,
            price DECIMAL(12,2) NOT NULL,
            property_type VARCHAR(40) NOT NULL DEFAULT 'apartment',
            purpose VARCHAR(20) NOT NULL DEFAULT 'sale',
            bedrooms TINYINT UNSIGNED NULL,
            bathrooms TINYINT UNSIGNED NULL,
            area_sqft INT UNSIGNED NULL,
            description TEXT NULL,
            image VARCHAR(255) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $connection->query(
        "CREATE TABLE IF NOT EXISTS bookings (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            property_id INT UNSIGNED NOT NULL,
            booking_date DATE NULL,
            visit_date DATE NULL,
            message TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $connection->query(
        "CREATE TABLE IF NOT EXISTS payments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            booking_id INT UNSIGNED NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            payment_method VARCHAR(30) NOT NULL DEFAULT 'upi',
            transaction_ref VARCHAR(120) NOT NULL,
            payment_date DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'paid',
            invoice_number VARCHAR(40) NOT NULL,
            notes VARCHAR(255) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    ensure_column($connection, $databaseName, 'users', 'phone', 'ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email');
    ensure_column($connection, $databaseName, 'users', 'role', "ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'customer' AFTER password");
    ensure_column($connection, $databaseName, 'users', 'created_at', 'ALTER TABLE users ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    ensure_column($connection, $databaseName, 'users', 'updated_at', 'ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    ensure_column($connection, $databaseName, 'properties', 'user_id', 'ALTER TABLE properties ADD COLUMN user_id INT UNSIGNED NULL AFTER id');
    ensure_column($connection, $databaseName, 'properties', 'city', 'ALTER TABLE properties ADD COLUMN city VARCHAR(100) NULL AFTER location');
    ensure_column($connection, $databaseName, 'properties', 'property_type', "ALTER TABLE properties ADD COLUMN property_type VARCHAR(40) NOT NULL DEFAULT 'apartment' AFTER price");
    ensure_column($connection, $databaseName, 'properties', 'purpose', "ALTER TABLE properties ADD COLUMN purpose VARCHAR(20) NOT NULL DEFAULT 'sale' AFTER property_type");
    ensure_column($connection, $databaseName, 'properties', 'bedrooms', 'ALTER TABLE properties ADD COLUMN bedrooms TINYINT UNSIGNED NULL AFTER purpose');
    ensure_column($connection, $databaseName, 'properties', 'bathrooms', 'ALTER TABLE properties ADD COLUMN bathrooms TINYINT UNSIGNED NULL AFTER bedrooms');
    ensure_column($connection, $databaseName, 'properties', 'area_sqft', 'ALTER TABLE properties ADD COLUMN area_sqft INT UNSIGNED NULL AFTER bathrooms');
    ensure_column($connection, $databaseName, 'properties', 'status', "ALTER TABLE properties ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER image");
    ensure_column($connection, $databaseName, 'properties', 'created_at', 'ALTER TABLE properties ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    ensure_column($connection, $databaseName, 'properties', 'updated_at', 'ALTER TABLE properties ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    ensure_column_definition(
        $connection,
        $databaseName,
        'properties',
        'image',
        'varchar',
        'YES',
        'ALTER TABLE properties MODIFY COLUMN image VARCHAR(255) NULL'
    );
    ensure_column_definition(
        $connection,
        $databaseName,
        'properties',
        'status',
        'varchar',
        'NO',
        "ALTER TABLE properties MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'"
    );

    ensure_column($connection, $databaseName, 'bookings', 'booking_date', 'ALTER TABLE bookings ADD COLUMN booking_date DATE NULL AFTER property_id');
    ensure_column($connection, $databaseName, 'bookings', 'visit_date', 'ALTER TABLE bookings ADD COLUMN visit_date DATE NULL AFTER booking_date');
    ensure_column($connection, $databaseName, 'bookings', 'message', 'ALTER TABLE bookings ADD COLUMN message TEXT NULL AFTER visit_date');
    ensure_column($connection, $databaseName, 'bookings', 'status', "ALTER TABLE bookings ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER message");
    ensure_column($connection, $databaseName, 'bookings', 'created_at', 'ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
    ensure_column($connection, $databaseName, 'bookings', 'updated_at', 'ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    ensure_column($connection, $databaseName, 'payments', 'notes', 'ALTER TABLE payments ADD COLUMN notes VARCHAR(255) NULL AFTER invoice_number');

    ensure_index($connection, $databaseName, 'users', 'uniq_users_email', 'ALTER TABLE users ADD UNIQUE KEY uniq_users_email (email)');
    ensure_index($connection, $databaseName, 'properties', 'idx_properties_owner', 'ALTER TABLE properties ADD KEY idx_properties_owner (user_id)');
    ensure_index($connection, $databaseName, 'properties', 'idx_properties_status', 'ALTER TABLE properties ADD KEY idx_properties_status (status)');
    ensure_index($connection, $databaseName, 'bookings', 'idx_bookings_user', 'ALTER TABLE bookings ADD KEY idx_bookings_user (user_id)');
    ensure_index($connection, $databaseName, 'bookings', 'idx_bookings_property', 'ALTER TABLE bookings ADD KEY idx_bookings_property (property_id)');
    ensure_index($connection, $databaseName, 'payments', 'uniq_payments_invoice', 'ALTER TABLE payments ADD UNIQUE KEY uniq_payments_invoice (invoice_number)');
    ensure_index($connection, $databaseName, 'payments', 'uniq_payments_booking', 'ALTER TABLE payments ADD UNIQUE KEY uniq_payments_booking (booking_id)');

    $connection->query("UPDATE users SET role = 'customer' WHERE role = 'user' OR role = '' OR role IS NULL");
    $connection->query("UPDATE properties SET status = 'approved' WHERE status = '' OR status IS NULL");
    $connection->query("UPDATE bookings SET status = 'pending' WHERE status = '' OR status IS NULL");
}

function ensure_column(mysqli $connection, string $databaseName, string $table, string $column, string $alterSql): void
{
    $statement = $connection->prepare(
        'SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $statement->bind_param('sss', $databaseName, $table, $column);
    $statement->execute();
    $statement->bind_result($total);
    $statement->fetch();
    $exists = (int) $total > 0;
    $statement->close();

    if (!$exists) {
        $connection->query($alterSql);
    }
}

function ensure_index(mysqli $connection, string $databaseName, string $table, string $indexName, string $alterSql): void
{
    $statement = $connection->prepare(
        'SELECT COUNT(*) AS total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?'
    );
    $statement->bind_param('sss', $databaseName, $table, $indexName);
    $statement->execute();
    $statement->bind_result($total);
    $statement->fetch();
    $exists = (int) $total > 0;
    $statement->close();

    if (!$exists) {
        $connection->query($alterSql);
    }
}

function ensure_column_definition(
    mysqli $connection,
    string $databaseName,
    string $table,
    string $column,
    string $expectedDataType,
    string $expectedNullable,
    string $alterSql
): void {
    $statement = $connection->prepare(
        'SELECT DATA_TYPE, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $statement->bind_param('sss', $databaseName, $table, $column);
    $statement->execute();
    $statement->bind_result($dataType, $isNullable);
    $exists = $statement->fetch();
    $statement->close();

    if (!$exists) {
        return;
    }

    if (strtolower((string) $dataType) !== strtolower($expectedDataType) || strtoupper((string) $isNullable) !== strtoupper($expectedNullable)) {
        $connection->query($alterSql);
    }
}

function db_query(mysqli $connection, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $statement = $connection->prepare($sql);

    if ($types !== '' && $params !== []) {
        bind_statement_params($statement, $types, $params);
    }

    $statement->execute();

    return $statement;
}

function db_all(mysqli $connection, string $sql, string $types = '', array $params = []): array
{
    $statement = db_query($connection, $sql, $types, $params);
    $rows = statement_fetch_all($statement);
    $statement->close();

    return $rows;
}

function db_one(mysqli $connection, string $sql, string $types = '', array $params = []): ?array
{
    $rows = db_all($connection, $sql, $types, $params);

    return $rows[0] ?? null;
}

function db_scalar(mysqli $connection, string $sql, string $types = '', array $params = [])
{
    $row = db_one($connection, $sql, $types, $params);

    if ($row === null) {
        return null;
    }

    return array_shift($row);
}

function db_execute(mysqli $connection, string $sql, string $types = '', array $params = []): int
{
    $statement = db_query($connection, $sql, $types, $params);
    $affectedRows = $statement->affected_rows;
    $statement->close();

    return $affectedRows;
}

function bind_statement_params(mysqli_stmt $statement, string $types, array $params): void
{
    $bindValues = [$types];

    foreach ($params as $key => $value) {
        $bindValues[] = &$params[$key];
    }

    call_user_func_array([$statement, 'bind_param'], $bindValues);
}

function statement_fetch_all(mysqli_stmt $statement): array
{
    if (method_exists($statement, 'get_result')) {
        $result = $statement->get_result();

        if ($result instanceof mysqli_result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    $metadata = $statement->result_metadata();

    if (!$metadata) {
        return [];
    }

    $row = [];
    $bindValues = [];

    while ($field = $metadata->fetch_field()) {
        $row[$field->name] = null;
        $bindValues[] = &$row[$field->name];
    }

    call_user_func_array([$statement, 'bind_result'], $bindValues);

    $rows = [];

    while ($statement->fetch()) {
        $copy = [];

        foreach ($row as $key => $value) {
            $copy[$key] = $value;
        }

        $rows[] = $copy;
    }

    return $rows;
}
