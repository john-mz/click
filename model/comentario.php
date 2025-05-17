<?php
class Comentario {
    private $conn;
    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');
        if ($this->conn->connect_error) {
            die('Error de conexión: ' . $this->conn->connect_error);
        }
    }

    public function insertar($comentario, $usuario_id, $publicacion_id, $parent_id = null) {
        $sql = "INSERT INTO comentario (comentario, usuario_id, publicacion_id, parent_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('siii', $comentario, $usuario_id, $publicacion_id, $parent_id);
        return $stmt->execute();
    }

    public function obtenerPorPublicacion($publicacion_id) {
        $sql = "SELECT c.*, u.nombre as nombre_usuario FROM comentario c LEFT JOIN usuario u ON c.usuario_id = u.id_usuario WHERE c.publicacion_id = ? AND c.parent_id IS NULL ORDER BY c.fecha_comentario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $publicacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            $comentarios[] = $row;
        }
        return $comentarios;
    }

    public function obtenerRespuestas($comentario_id) {
        $sql = "SELECT c.*, u.nombre as nombre_usuario FROM comentario c LEFT JOIN usuario u ON c.usuario_id = u.id_usuario WHERE c.parent_id = ? ORDER BY c.fecha_comentario ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $comentario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $respuestas = [];
        while ($row = $result->fetch_assoc()) {
            $respuestas[] = $row;
        }
        return $respuestas;
    }

    public function eliminarComentario($id_comentario) {
        $sql = "DELETE FROM comentario WHERE id_comentario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id_comentario);
        return $stmt->execute();
    }
}