<?php
// backend/login.php
session_start();
require_once __DIR__ . '/../private_config/config.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: $_POST;

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) { http_response_code(400); echo json_encode(['error'=>'Missing credentials']); exit; }

$stmt = $conn->prepare("SELECT id,name,password_hash FROM users WHERE email = ?");
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) { http_response_code(401); echo json_encode(['error'=>'Invalid credentials']); exit; }
$stmt->bind_result($id,$name,$hash);
$stmt->fetch();

if (!password_verify($password,$hash)) { http_response_code(401); echo json_encode(['error'=>'Invalid credentials']); exit; }

session_regenerate_id(true);
$_SESSION['user_id']=$id;
$_SESSION['user_name']=$name;
$_SESSION['user_email']=$email;

echo json_encode(['ok'=>true,'user'=>['id'=>$id,'name'=>$name]]);
$stmt->close();
$conn->close();
?>