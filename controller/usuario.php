<?php 
require_once 'model/usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function obtenerUsuarios() {
        try {
            $result = $this->usuario->consultar();
            if (!$result) {
                return ['error' => 'Error al consultar los usuarios'];
            }
            $usuarios = [];
            while ($row = $result->fetch_assoc()) {
                // Obtener el nombre del rol basado en el rol_id
                $rol = '';
                if (isset($row['rol_id'])) {
                    $rol = $row['rol_id'] == 1 ? 'admin' : 'invitado';
                }
                $usuarios[] = [
                    'id_usuario' => $row['id_usuario'],
                    'nombre' => $row['nombre'],
                    'email' => $row['email'],
                    'rol' => $rol,
                    'rol_id' => $row['rol_id'] ?? null
                ];
            }
            return $usuarios;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id) {
        try {
            if (empty($id_usuario) || empty($nombre) || empty($email) || empty($password) || empty($rol_id)) {
                return ['success' => false, 'message' => 'Todos los campos son requeridos'];
            }
            $resultado = $this->usuario->editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al editar el usuario'];
            }
            return ['success' => true, 'message' => 'Usuario editado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function insertar($nombre, $email, $password, $rol_id) {
        try {
            if (empty($nombre) || empty($email) || empty($password) || empty($rol_id)) {
                return ['success' => false, 'message' => 'Todos los campos son requeridos'];
            }
            $resultado = $this->usuario->insertar($nombre, $email, $password, $rol_id);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al crear el usuario'];
            }
            return ['success' => true, 'message' => 'Usuario creado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function eliminar($id_usuario) {
        try {
            if (empty($id_usuario)) {
                return ['success' => false, 'message' => 'ID de usuario no proporcionado'];
            }
            $resultado = $this->usuario->eliminar($id_usuario);
            if (!$resultado) {
                return ['success' => false, 'message' => 'Error al eliminar el usuario'];
            }
            return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function obtenerPorId($id_usuario) {
        try {
            if (empty($id_usuario)) {
                return ['success' => false, 'message' => 'ID de usuario no proporcionado'];
            }
            $usuario = $this->usuario->consultarPorId($id_usuario);
            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }
            return ['success' => true, 'usuario' => $usuario];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Manejar peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UsuarioController();
    
    if (isset($_POST['editar'])) {
        $id_usuario = (int)$_POST['id_usuario'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $fecha_registro = $_POST['fecha_registro'];
        $rol_id = $_POST['rol'];
        $resultado = $controller->editar($id_usuario, $nombre, $email, $password, $fecha_registro, $rol_id);
        $_SESSION['mensaje'] = $resultado['message'];
        $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        header('Location: index.php?view=usuario');
        exit;
    }
    
    if (isset($_POST['agregar'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $rol_id = $_POST['rol'];
        $resultado = $controller->insertar($nombre, $email, $password, $rol_id);
        $_SESSION['mensaje'] = $resultado['message'];
        $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        header('Location: index.php?view=usuario');
        exit;
    }

    if (isset($_POST['eliminar'])) {
        $id_usuario = $_POST['id_usuario'];
        $resultado = $controller->eliminar($id_usuario);
        $_SESSION['mensaje'] = $resultado['message'];
        $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        header('Location: index.php?view=usuario');
        exit;
    }
}
?>