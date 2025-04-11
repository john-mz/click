<?php
class Publicacion {
    private $conn;
    private $usuarioActual;
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
        $this->usuarioActual = null;
        // Modificar la tabla para permitir valores nulos en imagen_url
        $this->conn->query("ALTER TABLE publicacion MODIFY COLUMN imagen_url VARCHAR(255) NULL");
        
        // Asegurar que la tabla conteo tenga una restricción única
        $this->conn->query("ALTER TABLE conteo ADD UNIQUE KEY IF NOT EXISTS unique_reaccion (id_publicacion, id_usuario)");
        
        // Crear tabla de reacciones si no existe
        $this->conn->query("CREATE TABLE IF NOT EXISTS reaccion (
            id_reaccion INT AUTO_INCREMENT PRIMARY KEY,
            publicacion_id INT,
            usuario_id INT,
            tipo ENUM('like', 'dislike'),
            FOREIGN KEY (publicacion_id) REFERENCES publicacion(id_publicacion),
            FOREIGN KEY (usuario_id) REFERENCES usuario(id_usuario),
            UNIQUE KEY unique_reaccion (publicacion_id, usuario_id)
        )");
    }

    public function setUsuarioActual($usuario) {
        $this->usuarioActual = $usuario;
    }

    public function consultar() {
        $sql = "SELECT p.*, u.nombre as nombre_usuario, 
                COALESCE(cr.meGusta, 0) as meGusta, 
                COALESCE(cr.noMeGusta, 0) as noMeGusta 
                FROM publicacion p 
                LEFT JOIN usuario u ON p.usuario_id = u.id_usuario 
                LEFT JOIN conteoreal cr ON p.id_publicacion = cr.id_publicacion 
                GROUP BY p.id_publicacion 
                ORDER BY p.fecha_creacion DESC";
        $result = $this->conn->query($sql);
        if (!$result) {
            die("Error en la consulta: " . $this->conn->error);
        }
        return $result;
    }

    public function consultarPorUsuario($usuario_id) {
        $sql = "SELECT p.*, u.nombre as nombre_usuario 
                FROM publicacion p 
                LEFT JOIN usuario u ON p.usuario_id = u.id_usuario 
                WHERE p.usuario_id = ? 
                ORDER BY p.fecha_creacion DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        return $stmt->get_result();
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

    public function obtenerReacciones($publicacion_id) {
        $sql = "SELECT meGusta, noMeGusta FROM conteoreal WHERE id_publicacion = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['likes' => 0, 'dislikes' => 0];
        }
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ?: ['likes' => 0, 'dislikes' => 0];
    }

    public function obtenerReaccionUsuario($publicacion_id, $usuario_id) {
        $sql = "SELECT id_reaccion FROM conteo WHERE id_publicacion = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function agregarReaccion($publicacion_id, $usuario_id, $tipo) {
        $this->conn->begin_transaction();
        try {
            // Primero verificar si ya existe una reacción en esta publicación específica
            $reaccion_actual = $this->obtenerReaccionUsuario($publicacion_id, $usuario_id);
            
            // Si ya existe una reacción del mismo tipo en esta publicación, la eliminamos
            if ($reaccion_actual && $reaccion_actual['id_reaccion'] == ($tipo === 'like' ? 1 : 2)) {
                $result = $this->eliminarReaccion($publicacion_id, $usuario_id);
                $this->conn->commit();
                return ['success' => true, 'action' => 'removed'];
            }

            // Si existe una reacción diferente en esta publicación, la actualizamos
            if ($reaccion_actual) {
                // Actualizar conteoreal - restar la reacción anterior
                $campo_anterior = $reaccion_actual['id_reaccion'] == 1 ? 'meGusta' : 'noMeGusta';
                $sql = "UPDATE conteoreal SET $campo_anterior = GREATEST($campo_anterior - 1, 0) WHERE id_publicacion = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $publicacion_id);
                $stmt->execute();

                // Actualizar la reacción existente
                $id_reaccion = $tipo === 'like' ? 1 : 2;
                $sql = "UPDATE conteo SET id_reaccion = ? WHERE id_publicacion = ? AND id_usuario = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iii", $id_reaccion, $publicacion_id, $usuario_id);
                $stmt->execute();
            } else {
                // Insertar nueva reacción
                $id_reaccion = $tipo === 'like' ? 1 : 2;
                $sql = "INSERT INTO conteo (id_publicacion, id_usuario, id_reaccion) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("iii", $publicacion_id, $usuario_id, $id_reaccion);
                $stmt->execute();
            }

            // Actualizar conteoreal
            $campo = $tipo === 'like' ? 'meGusta' : 'noMeGusta';
            $sql = "INSERT INTO conteoreal (id_publicacion, meGusta, noMeGusta) 
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE $campo = GREATEST($campo + 1, 0)";
            $stmt = $this->conn->prepare($sql);
            $valores_iniciales = ($tipo === 'like' ? [1, 0] : [0, 1]);
            $stmt->bind_param("iii", $publicacion_id, $valores_iniciales[0], $valores_iniciales[1]);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'action' => 'added'];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function eliminarReaccion($publicacion_id, $usuario_id) {
        $this->conn->begin_transaction();
        try {
            // Obtener el tipo de reacción actual
            $reaccion_actual = $this->obtenerReaccionUsuario($publicacion_id, $usuario_id);

            if ($reaccion_actual) {
                // Eliminar de la tabla conteo
                $sql = "DELETE FROM conteo WHERE id_publicacion = ? AND id_usuario = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ii", $publicacion_id, $usuario_id);
                $stmt->execute();

                // Actualizar conteoreal
                $campo = $reaccion_actual['id_reaccion'] == 1 ? 'meGusta' : 'noMeGusta';
                $sql = "UPDATE conteoreal SET $campo = GREATEST($campo - 1, 0) WHERE id_publicacion = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $publicacion_id);
                $result = $stmt->execute();

                $this->conn->commit();
                return $result;
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}
