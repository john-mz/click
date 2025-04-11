<?php
require_once 'model/publicacion.php';
require_once 'model/usuario.php';

class PublicacionController {
    private $publicacion;
    private $usuario;

    public function __construct() {
        $this->publicacion = new Publicacion();
        $this->usuario = new Usuario();
    }

    public function index() {
        try {
            $result = $this->publicacion->consultar();
            if (!$result) {
                return ['error' => 'Error al consultar las publicaciones'];
            }
            $publicaciones = [];
            while ($row = $result->fetch_assoc()) {
                $publicaciones[] = $row;
            }
            return $publicaciones;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function obtener($id) {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de publicación no proporcionado'];
            }
            $publicacion = $this->publicacion->consultarPorId($id);
            if (!$publicacion) {
                return ['success' => false, 'message' => 'Publicación no encontrada'];
            }
            return ['success' => true, 'publicacion' => $publicacion];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function crear($descripcion, $imagen_url, $usuario_id) {
        try {
            if (empty($descripcion) || empty($imagen_url) || empty($usuario_id)) {
                return ['success' => false, 'message' => 'Todos los campos son requeridos'];
            }

            $resultado = $this->publicacion->insertar($descripcion, $imagen_url, $usuario_id);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al crear la publicación'];
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actualizar($id, $descripcion, $imagen_url, $usuario_id) {
        try {
            if (empty($id) || empty($descripcion) || empty($imagen_url) || empty($usuario_id)) {
                return ['success' => false, 'message' => 'Todos los campos son requeridos'];
            }

            $resultado = $this->publicacion->editar($id, $descripcion, $imagen_url, $usuario_id);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al actualizar la publicación'];
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function eliminar($id) {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de publicación no proporcionado'];
            }
            $resultado = $this->publicacion->eliminar($id);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al eliminar la publicación'];
            }
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function obtenerUsuarios() {
        try {
            $result = $this->usuario->consultar();
            if (!$result) {
                return ['error' => 'Error al consultar los usuarios'];
            }
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
            return $usuarios;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
