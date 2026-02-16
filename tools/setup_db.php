<?php
// tools/setup_db.php — Run the SQL in database/schema.sql to create DB and tables.
require_once __DIR__ . '/../private_config/config.php';

$sqlFile = __DIR__ . '/../database/schema.sql';
if (!file_exists($sqlFile)) {
    echo "schema.sql not found at $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) { echo "Failed to read schema.sql\n"; exit(1); }

// Connect without selecting DB so CREATE DATABASE works if DB doesn't exist
$link = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($link->connect_error) { echo "MySQL connect error: " . $link->connect_error . "\n"; exit(1); }

if ($link->multi_query($sql)) {
    do {
        if ($res = $link->store_result()) { $res->free(); }
    } while ($link->more_results() && $link->next_result());
    echo "Schema applied successfully.\n";
} else {
    echo "Schema apply failed: " . $link->error . "\n";
}

$link->close();

?>
