<?php
require_once 'model/publicacion.php';
require_once 'model/usuario.php';

class PublicacionController {
    private $publicacion;
    private $usuario;
    private $usuarioActual;

    public function __construct() {
        $this->publicacion = new Publicacion();
        $this->usuario = new Usuario();
        $this->usuarioActual = null;
    }

    public function setUsuarioActual($usuario) {
        $this->usuarioActual = $usuario;
        $this->publicacion->setUsuarioActual($usuario);
    }

    public function index() {
        try {
            $result = $this->publicacion->consultar();
            if (!$result) {
                return ['error' => 'Error al consultar las publicaciones'];
            }
            $publicaciones = [];
            while ($row = $result->fetch_assoc()) {
                // Obtener reacciones para cada publicación
                $reacciones = $this->publicacion->obtenerReacciones($row['id_publicacion']);
                $row['likes'] = $reacciones['likes'] ?? 0;
                $row['dislikes'] = $reacciones['dislikes'] ?? 0;
                $publicaciones[] = $row;
            }
            return $publicaciones;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function obtenerPublicacionesUsuario($usuario_id) {
        try {
            if (empty($usuario_id)) {
                return ['error' => 'ID de usuario no proporcionado'];
            }
            $result = $this->publicacion->consultarPorUsuario($usuario_id);
            if (!$result) {
                return ['error' => 'Error al consultar las publicaciones del usuario'];
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
            if (empty($descripcion) || empty($usuario_id)) {
                return ['success' => false, 'message' => 'La descripción y el usuario son requeridos'];
            }

            // Verificar permisos si es usuario invitado
            if ($this->usuarioActual && $this->usuarioActual['rol'] !== 'admin' && $this->usuarioActual['id'] != $usuario_id) {
                return ['success' => false, 'message' => 'No tienes permiso para crear publicaciones para este usuario'];
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

            // Verificar permisos si es usuario invitado
            if ($this->usuarioActual && $this->usuarioActual['rol'] !== 'admin') {
                $publicacion = $this->publicacion->consultarPorId($id);
                if (!$publicacion || $publicacion['usuario_id'] != $this->usuarioActual['id']) {
                    return ['success' => false, 'message' => 'No tienes permiso para actualizar esta publicación'];
                }
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

    public function reaccionar($publicacion_id, $usuario_id, $tipo) {
        try {
            if (empty($publicacion_id) || empty($usuario_id) || !in_array($tipo, ['like', 'dislike'])) {
                return ['success' => false, 'message' => 'Datos inválidos'];
            }

            $resultado = $this->publicacion->agregarReaccion($publicacion_id, $usuario_id, $tipo);
            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function eliminar($id) {
        try {
            if (empty($id)) {
                return ['success' => false, 'message' => 'ID de publicación no proporcionado'];
            }
            // Verificar permisos si es usuario admin
            if ($this->usuarioActual && $this->usuarioActual['rol'] !== 'admin') {
                return ['success' => false, 'message' => 'No tienes permiso para eliminar esta publicación'];
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
}
