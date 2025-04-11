<?php
class Usuario {
    public $id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id;
    public $conn;
    function __construct(){
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');
    }

    function consultar(){
        // $sql = "SELECT id_usuario, nombre, email, password, fecha_registro, rol_id FROM usuario";
        $sql = "SELECT usuario.id_usuario, usuario.nombre, usuario.email, usuario.password, usuario.fecha_registro , rol.nombre_rol FROM usuario LEFT JOIN rol ON usuario.rol_id = rol.id_rol ORDER BY usuario.id_usuario;";
        $res = $this->conn->query($sql);
        return $res;
    }

    function insertar($nombre, $email, $password, $rol_id){
        $sql = "INSERT INTO usuario (nombre, email, password, rol_id) VALUES ('$nombre', '$email', '$password', $rol_id)";
        $res = $this->conn->query($sql);

    }

    function editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id){
        $sql = "UPDATE usuario SET nombre = '$nombre', email = '$email', password = '$password', fecha_registro = '$fecha_registro', rol_id = $rol_id WHERE id_usuario = $id_usuario";
        $res = $this->conn->query($sql);
    }

    function eliminar($id_usuario){
        $sql = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
        $res = $this->conn->query($sql);
    }
}
?>