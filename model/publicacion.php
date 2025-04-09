<?php
class Publicacion {
    public $id_publicacion, $titulo, $contenido, $fecha_publicacion, $id_usuario;
    public $conn;
    function __construct(){
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');
    }

    function consultar(){
        $sql = "SELECT publicacion.id_publicacion, publicacion.descripcion, publicacion.fecha_creacion, usuario.id_usuario, usuario.nombre FROM publicacion LEFT JOIN usuario ON publicacion.usuario_id = usuario.id_usuario";
        $res = $this->conn->query($sql);
        return $res;
    }

    function insertar($titulo, $contenido, $fecha_publicacion, $id_usuario){
        $sql = "INSERT INTO publicacion (titulo, contenido, fecha_publicacion, id_usuario) VALUES ('$titulo', '$contenido', '$fecha_publicacion', '$id_usuario')";
        $res = $this->conn->query($sql);
    }

    function editar($id_publicacion, $titulo, $contenido, $fecha_publicacion, $id_usuario){
        $sql = "UPDATE publicacion SET titulo = '$titulo', contenido = '$contenido', fecha_publicacion = '$fecha_publicacion', id_usuario = $id_usuario WHERE id_publicacion = $id_publicacion";
        $res = $this->conn->query($sql);
    }

    function eliminar($id_publicacion){
        $sql = "DELETE FROM publicacion WHERE id_publicacion = $id_publicacion";
        $res = $this->conn->query($sql);
    }
}

