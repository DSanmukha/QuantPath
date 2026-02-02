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
?>