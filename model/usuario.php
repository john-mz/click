<?php
class Usuario {
    public $id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id;
    public $conn;
    function __construct(){
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');

    }

    // function setAdminIdSql($adminId){
    //     $sql = "SET @admin_id = $adminId";
    //     $res = $this->conn->query($sql);
    // }

    function consultar(){
        $sql = "SELECT usuario.id_usuario, usuario.nombre, usuario.email, usuario.password, usuario.fecha_registro, usuario.rol_id, rol.nombre_rol 
                FROM usuario 
                LEFT JOIN rol ON usuario.rol_id = rol.id_rol 
                ORDER BY usuario.id_usuario";
        $res = $this->conn->query($sql);
        return $res;
    }

    function insertar($nombre, $email, $password, $rol_id, $admin_id){
        $preSql = "SET @current_admin = $admin_id";
        $this->conn->query($preSql);
        $sql = "INSERT INTO usuario (nombre, email, password, rol_id) VALUES ('$nombre', '$email', '$password', $rol_id)";
        $res = $this->conn->query($sql);
        return $res;
    }

    function editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id, $admin_id){
        // echo $admin_id;
        $preSql = "SET @current_admin = $admin_id";
        $this->conn->query($preSql);
        $sql = "UPDATE usuario SET nombre = '$nombre', email = '$email', password = '$password', fecha_registro = '$fecha_registro', rol_id = $rol_id WHERE id_usuario = $id_usuario";
        $res = $this->conn->query($sql);
        return $res;
    }

    function eliminar($id_usuario, $admin_id){
        $preSql = "SET @current_admin = $admin_id";
        $preRes = $this->conn->query($preSql) or die(mysqli_error($this->conn));
        $sql = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
        $res = $this->conn->query($sql) or die(mysqli_error($this->conn));
        // return $res;
    }

    function consultarPorId($id_usuario) {
        $sql = "SELECT usuario.id_usuario, usuario.nombre, usuario.email, usuario.password, usuario.fecha_registro, usuario.rol_id, rol.nombre_rol 
                FROM usuario 
                LEFT JOIN rol ON usuario.rol_id = rol.id_rol 
                WHERE usuario.id_usuario = $id_usuario";
        $res = $this->conn->query($sql);
        return $res->fetch_assoc();
    }
}
?>