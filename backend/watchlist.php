<?php
// backend/watchlist.php — CRUD for user's stock watchlist
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { http_response_code(401); echo json_encode(['error'=>'Not logged in']); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get all watchlist items
    $stmt = $conn->prepare("SELECT id, stock_symbol, added_at FROM watchlist WHERE user_id=? ORDER BY added_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    echo json_encode(['watchlist' => $items]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $symbol = strtoupper(trim($input['symbol'] ?? ''));
    if (!$symbol) { http_response_code(400); echo json_encode(['error'=>'Missing symbol']); exit; }

    $stmt = $conn->prepare("INSERT IGNORE INTO watchlist (user_id, stock_symbol) VALUES (?, ?)");
    $stmt->bind_param('is', $user_id, $symbol);
    $stmt->execute();
    $inserted = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    echo json_encode(['status' => $inserted ? 'added' : 'already_exists', 'symbol' => $symbol]);
    exit;
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $symbol = strtoupper(trim($input['symbol'] ?? ''));
    if (!$symbol) { http_response_code(400); echo json_encode(['error'=>'Missing symbol']); exit; }

    $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id=? AND stock_symbol=?");
    $stmt->bind_param('is', $user_id, $symbol);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();
    $conn->close();
    echo json_encode(['status' => $deleted ? 'removed' : 'not_found']);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
?>
