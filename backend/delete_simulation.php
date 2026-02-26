<?php
// backend/delete_simulation.php — Delete a simulation
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../private_config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { http_response_code(401); echo json_encode(['error'=>'Not logged in']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$sim_id = intval($input['id'] ?? 0);
if (!$sim_id) { http_response_code(400); echo json_encode(['error'=>'Missing simulation id']); exit; }

$stmt = $conn->prepare("DELETE FROM simulations WHERE id=? AND user_id=?");
$stmt->bind_param('ii', $sim_id, $user_id);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['status' => $deleted ? 'deleted' : 'not_found']);
?>
