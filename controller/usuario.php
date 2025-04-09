<?php 
require_once 'model/usuario.php';
$usuario = new Usuario(); 
// funciona la insercion
// funciona editar
// $usuario->editar(8, 'kevin', 'jua3n@gmail.com', 123, '2025-02-17 14:52:14', 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['editar'])){
        $id_usuario = (int)$_POST['id_usuario'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $fecha_registro = $_POST['fecha_registro'];
        $rol_id = $_POST['rol'];
        $usuario->editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id);    
    }
    if (isset($_POST['agregar'])){
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rol_id = $_POST['rol'];
        echo $usuario->insertar($nombre, $email, $password, $rol_id);
    }

}
?>