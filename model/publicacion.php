<?php
class Publicacion {
    private $conn;
    public $id_publicacion;
    public $descripcion;
    public $imagen_url;
    public $fecha_creacion;
    public $usuario_id;

    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    public function consultar() {
        $sql = "SELECT p.*, u.nombre as nombre_usuario 
                FROM publicacion p 
                LEFT JOIN usuario u ON p.usuario_id = u.id_usuario 
                ORDER BY p.fecha_creacion DESC";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Error en la consulta: " . $this->conn->error);
        }
        return $result;
    }

    public function consultarPorId($id) {
        $sql = "SELECT p.*, u.nombre as nombre_usuario 
                FROM publicacion p 
                LEFT JOIN usuario u ON p.usuario_id = u.id_usuario 
                WHERE p.id_publicacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function insertar($descripcion, $imagen_url, $usuario_id) {
        $sql = "INSERT INTO publicacion (descripcion, imagen_url, usuario_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $descripcion, $imagen_url, $usuario_id);
        return $stmt->execute();
    }

    public function editar($id, $descripcion, $imagen_url, $usuario_id) {
        $sql = "UPDATE publicacion SET descripcion = ?, imagen_url = ?, usuario_id = ? WHERE id_publicacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $descripcion, $imagen_url, $usuario_id, $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM publicacion WHERE id_publicacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
