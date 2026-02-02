<?php
// backend/fetch_stock.php
$symbol = trim($_GET['symbol'] ?? '');
if (!$symbol) { http_response_code(400); echo json_encode(['error'=>'Missing symbol']); exit; }

// Replace with your free API key
$apiKey = 'YOUR_ALPHA_VANTAGE_API_KEY';
$url = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . urlencode($symbol) . "&outputsize=compact&apikey=" . $apiKey;

$opts = ['http'=>['timeout'=>10]];
$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);
if ($response === false) { http_response_code(502); echo json_encode(['error'=>'API fetch failed']); exit; }
header('Content-Type: application/json');
echo $response;
?>