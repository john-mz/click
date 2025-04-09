<?php 
require_once 'model/usuario.php';
$usuario = new Usuario(); 
// funciona la insercion
// $usuario->insertar('juan', 'juan@gmail.com', 123, '2025-02-17 14:57:14', 1);
// funciona editar
// $usuario->editar(8, 'kevin', 'jua3n@gmail.com', 123, '2025-02-17 14:52:14', 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (isset($_POST['editar'])){
        $nombre = $_POST['inputNombre'];
        $email = $_POST['inputEmail'];
        $password = $_POST['inputPassword'];
        $fecha_registro = $_POST['inputFechaRegistro'];
        $rol_id = $_POST['inputRolId'];
        $_SESSION['prueba'] = [$nombre, $email, $password, $fecha_registro, $rol_id];
        $usuario->editar(9, $nombre, $email, $password, $fecha_registro, $rol_id);    
    }

}
?>