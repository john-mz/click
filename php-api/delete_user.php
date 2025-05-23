<?php
include 'db.php';

$id_usuario = $_POST['id_usuario'];
$admin_id = 1;
$preSql = "SET @current_admin = $admin_id";
$conn->query($preSql);
$result = $conn->query("DELETE FROM usuario WHERE id_usuario = $id_usuario");
if (!$result) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}
if ($conn->affected_rows === 0) {
    echo json_encode(["success" => false, "error" => "No user found with that ID."]);
    exit;
}
$data = [
    'type' => 'user_deleted',
    'data' => [
        'id_usuario' => $id_usuario
    ]
];
// Notify WebSocket server
$ch = curl_init('http://localhost:3000/broadcast');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

echo json_encode(["success" => true]);
?>