<?php
include 'db.php';

// id_usuario, nombre, email, password, fecha_registro, rol_id
$id_usuario = $_POST['id_usuario'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$password = $_POST['password'];
$fecha_registro = $_POST['fecha_registro'];
$rol_id = $_POST['rol_id'];
$admin_id = 1;
$preSql = "SET @current_admin = $admin_id";
$conn->query($preSql);
$conn->query("UPDATE usuario SET nombre = '$nombre', email = '$email', password = '$password', fecha_registro = '$fecha_registro', rol_id = $rol_id WHERE id_usuario = $id_usuario");

$data = [
    'type' => 'user_updated',
    'data' => [
        'id_usuario' => $id_usuario,
        'nombre' => $nombre,
        'email' => $email,
        'password' => $password,
        'rol_id' => $rol_id,
        'fecha_registro' => $fecha_registro
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