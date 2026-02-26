<?php
// backend/fetch_stock.php — Fetch stock data from Alpha Vantage
header('Content-Type: application/json');

$symbol = trim($_GET['symbol'] ?? '');
if (!$symbol) { http_response_code(400); echo json_encode(['error'=>'Missing symbol']); exit; }

require_once __DIR__ . '/../private_config/config.php';

$apiKey = $ALPHA_VANTAGE_API_KEY ?? '';
if (!$apiKey) {
	http_response_code(500);
	echo json_encode(['error' => 'Alpha Vantage API key not configured']);
	exit;
}

$function = $_GET['function'] ?? 'TIME_SERIES_DAILY';
$outputsize = $_GET['outputsize'] ?? 'compact';

// Build URL
$url = "https://www.alphavantage.co/query?function=" . urlencode($function)
     . "&symbol=" . urlencode($symbol)
     . "&outputsize=" . urlencode($outputsize)
     . "&apikey=" . $apiKey;

$opts = ['http'=>['timeout'=>15, 'ignore_errors'=>true]];
$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);
if ($response === false) {
    http_response_code(502);
    echo json_encode(['error'=>'API fetch failed']);
    exit;
}

$data = json_decode($response, true);

// Check for Alpha Vantage error messages
if (isset($data['Error Message'])) {
    http_response_code(400);
    echo json_encode(['error' => $data['Error Message']]);
    exit;
}
if (isset($data['Note'])) {
    // Rate limit reached
    http_response_code(429);
    echo json_encode(['error' => 'API rate limit reached. Please wait a minute and try again.']);
    exit;
}

echo $response;
?>