<?php
// backend/get_simulations.php
session_start();
require_once __DIR__ . '/../private_config/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id,stock_symbol,model_used,parameters,results_json,created_at FROM simulations WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param('i',$user_id);
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode(['ok'=>true,'simulations'=>$rows]);
$stmt->close();
$conn->close();
?>
