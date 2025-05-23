<?php
include 'db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$rol = $_POST['rol'];
$admin_id = 1;

$preSql = "SET @current_admin = $admin_id";
$conn->query($preSql);
$conn->query("INSERT INTO usuario (nombre, email, password, rol_id) VALUES ('$name', '$email', '$password', $rol)");
$newUserId = $conn->insert_id;
$timestamp = null;
$result = $conn->query("SELECT fecha_registro FROM usuario WHERE id_usuario = $newUserId");
if ($result && $row = $result->fetch_assoc()) {
    $timestamp = $row['fecha_registro'];
}

$data = [
    'type' => 'user_created',
    'data' => [
        'id_usuario' => $newUserId,
        'nombre' => $name,
        'email' => $email,
        'password' => $password,
        'rol_id' => $rol,
        'fecha_registro' => $timestamp
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
