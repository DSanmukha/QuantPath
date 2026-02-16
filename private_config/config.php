<?php
// private_config/config.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'quantpath';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    error_log('DB connect error: ' . $conn->connect_error);
    exit('Database connection failed');
}
$conn->set_charset('utf8mb4');
// Optional external API keys
// You can set `ALPHA_VANTAGE_API_KEY` in your environment or edit the value below.
$ALPHA_VANTAGE_API_KEY = getenv('ALPHA_VANTAGE_API_KEY') ?: '';
// NOTE: The key should be set in the environment (or Apache SetEnv). Do NOT
// commit your API key into source control. If this repository already contains
// the key in history, consider rotating the key.
?>