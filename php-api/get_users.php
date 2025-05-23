<?php
include 'db.php';

$result = $conn->query("SELECT usuario.id_usuario, usuario.nombre, usuario.email, usuario.password, usuario.fecha_registro, usuario.rol_id FROM usuario ORDER BY usuario.id_usuario DESC");
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
