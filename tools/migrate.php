<?php
// tools/migrate.php — Run this once to add new tables/columns
require_once __DIR__ . '/../private_config/config.php';

$queries = [
    // Profile fields on users
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar_url VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS institution VARCHAR(200) DEFAULT NULL",

    // Watchlist table
    "CREATE TABLE IF NOT EXISTS watchlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        stock_symbol VARCHAR(20) NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_stock (user_id, stock_symbol)
    )",

    // Comparisons table
    "CREATE TABLE IF NOT EXISTS comparisons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) DEFAULT 'Untitled Comparison',
        simulation_ids JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

$errors = [];
foreach ($queries as $sql) {
    if (!$conn->query($sql)) {
        // Ignore "duplicate column" errors for ALTER TABLE
        if ($conn->errno !== 1060) {
            $errors[] = $conn->error . " — SQL: " . substr($sql, 0, 80);
        }
    }
}

$conn->close();

header('Content-Type: application/json');
if (empty($errors)) {
    echo json_encode(['status' => 'ok', 'message' => 'Migration completed successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'errors' => $errors]);
}
?>
