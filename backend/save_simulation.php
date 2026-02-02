<?php
// backend/save_simulation.php
session_start();
require_once __DIR__ . '/../private_config/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) { http_response_code(400); echo json_encode(['error'=>'Invalid payload']); exit; }

$user_id = $_SESSION['user_id'];
$stock_symbol = $data['stock_symbol'] ?? '';
$model = $data['model'] ?? '';
$parameters = json_encode($data['parameters'] ?? []);
$results = json_encode($data['results'] ?? []);

$stmt = $conn->prepare("INSERT INTO simulations (user_id,stock_symbol,model_used,parameters,results_json) VALUES (?,?,?,?,?)");
$stmt->bind_param('issss',$user_id,$stock_symbol,$model,$parameters,$results);
if ($stmt->execute()) echo json_encode(['ok'=>true,'id'=>$stmt->insert_id]);
else { http_response_code(500); echo json_encode(['error'=>'Save failed']); }
$stmt->close();
$conn->close();
?>