<?php
// backend/profile.php — Get/Update user profile
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { http_response_code(401); echo json_encode(['error'=>'Not logged in']); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT id, name, email, bio, phone, institution, created_at FROM users WHERE id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get simulation count
    $stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM simulations WHERE user_id=?");
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $simCount = $stmt2->get_result()->fetch_assoc()['count'];
    $stmt2->close();

    // Get watchlist count
    $stmt3 = $conn->prepare("SELECT COUNT(*) as count FROM watchlist WHERE user_id=?");
    $stmt3->bind_param('i', $user_id);
    $stmt3->execute();
    $watchCount = $stmt3->get_result()->fetch_assoc()['count'];
    $stmt3->close();

    // Get unique stocks simulated
    $stmt4 = $conn->prepare("SELECT COUNT(DISTINCT stock_symbol) as count FROM simulations WHERE user_id=?");
    $stmt4->bind_param('i', $user_id);
    $stmt4->execute();
    $stockCount = $stmt4->get_result()->fetch_assoc()['count'];
    $stmt4->close();

    $conn->close();
    echo json_encode([
        'user' => $user,
        'stats' => [
            'simulations' => (int)$simCount,
            'watchlist' => (int)$watchCount,
            'stocks_analyzed' => (int)$stockCount
        ]
    ]);
    exit;
}

if ($method === 'POST' || $method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $bio = trim($input['bio'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $institution = trim($input['institution'] ?? '');

    if (!$name) { http_response_code(400); echo json_encode(['error'=>'Name is required']); exit; }

    $stmt = $conn->prepare("UPDATE users SET name=?, bio=?, phone=?, institution=? WHERE id=?");
    $stmt->bind_param('ssssi', $name, $bio, $phone, $institution, $user_id);
    $stmt->execute();
    $stmt->close();

    // Update session name
    $_SESSION['user_name'] = $name;

    $conn->close();
    echo json_encode(['status'=>'updated']);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
?>
