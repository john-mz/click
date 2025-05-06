<?php
// Definir mostrarAt antes de cualquier uso
if (!function_exists('mostrarAt')) {
    function mostrarAt($comentario, $usuario_actual) {
        return preg_replace_callback('/@(\w+)/', function($matches) use ($usuario_actual) {
            if (strcasecmp($matches[1], $usuario_actual) === 0) {
                return '<span class="at-mention">@' . htmlspecialchars($matches[1]) . '</span>';
            } else {
                return '@' . htmlspecialchars($matches[1]);
            }
        }, htmlspecialchars($comentario));
    }
}

// Definir puedeEliminarComentario antes de cualquier uso
if (!function_exists('puedeEliminarComentario')) {
    function puedeEliminarComentario($comentario, $usuario_actual, $publicacion) {
        return (
            isset($usuario_actual['rol']) && (
                $usuario_actual['rol'] === 'admin' ||
                (isset($usuario_actual['id']) && $usuario_actual['id'] == $comentario['usuario_id']) ||
                (isset($publicacion['publicacion']['usuario_id']) && $usuario_actual['id'] == $publicacion['publicacion']['usuario_id'])
            )
        );
    }
}

session_start();

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar variables de mensaje
$mensaje = '';
$tipo_mensaje = '';

// Recuperar mensaje de la sesión si existe
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'];
    // Limpiar mensajes de la sesión
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Manejar la selección de usuario
if (isset($_POST['seleccionar_usuario'])) {
    $_SESSION['usuario_actual'] = [
        'id' => $_POST['usuario_id'],
        'nombre' => $_POST['usuario_nombre'],
        'rol' => $_POST['usuario_rol']
    ];
    header('Location: index.php?view=publicacion');
    exit;
}

// Obtener el usuario actual de la sesión
$usuario_actual = $_SESSION['usuario_actual'] ?? null;
$es_admin = $usuario_actual && $usuario_actual['rol'] === 'admin';

// Cargar controladores necesarios
require_once 'controller/usuario.php';
require_once 'controller/publicacion.php';
require_once 'controller/comentario.php';

// Obtener lista de usuarios
$usuarioController = new UsuarioController();
$usuarios = $usuarioController->obtenerUsuarios();

// Manejar peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        // ... código existente ...
    }
    else if (isset($_POST['eliminar'])) {
        // ... código existente ...
    }
    else if (isset($_POST['publicacion_id']) && isset($_POST['tipo'])) {
        // Manejar reacción
        $controller = new PublicacionController();
        $controller->setUsuarioActual($_SESSION['usuario_actual']);
        
        header('Content-Type: application/json');
        $resultado = $controller->reaccionar(
            $_POST['publicacion_id'],
            $_SESSION['usuario_actual']['id'],
            $_POST['tipo']
        );
        echo json_encode($resultado);
        exit;
    }
    else if (isset($_POST['comentario'])) {
        if (!isset($controllerComentario)) {
            require_once 'controller/comentario.php';
            $controllerComentario = new ComentarioController();
        }
        $id_publicacion = $_POST['id_publicacion'] ?? $_GET['id_publicacion'] ?? null;
        $comentario = trim($_POST['comentario']);
        $usuario_id = $_SESSION['usuario_actual']['id'];
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $at_usuario = !empty($_POST['at_usuario']) ? trim($_POST['at_usuario']) : '';
        if ($at_usuario && strpos($comentario, '@' . $at_usuario) !== 0) {
            $comentario = '@' . $at_usuario . ' ' . $comentario;
        }
        if (!empty($comentario) && !empty($usuario_id) && !empty($id_publicacion)) {
            $controllerComentario->agregarComentario($comentario, $usuario_id, $id_publicacion, $parent_id);
        }
        // Redirigir para evitar reenvío del formulario
        header('Location: index.php?view=comentario&id_publicacion=' . $id_publicacion);
        exit;
    }
    else if (isset($_POST['eliminar_comentario'])) {
        if (!isset($controllerComentario)) {
            require_once 'controller/comentario.php';
            $controllerComentario = new ComentarioController();
        }
        $id_comentario = intval($_POST['eliminar_comentario']);
        // Obtener el comentario y la publicación para validar permisos
        $comentarioData = null;
        $publicacionData = null;
        $controllerPublicacion = $controllerPublicacion ?? new PublicacionController();
        // Buscar el comentario y la publicación
        $db = new mysqli('localhost', 'root', '', 'clickupdated');
        $res = $db->query("SELECT * FROM comentario WHERE id_comentario = $id_comentario");
        if ($res && $row = $res->fetch_assoc()) {
            $comentarioData = $row;
            $publicacionData = $controllerPublicacion->obtener($comentarioData['publicacion_id']);
        }
        $puedeEliminar = false;
        if ($comentarioData && $publicacionData) {
            $puedeEliminar = (
                $usuario_actual['rol'] === 'admin' ||
                $usuario_actual['id'] == $comentarioData['usuario_id'] ||
                $usuario_actual['id'] == $publicacionData['publicacion']['usuario_id']
            );
        }
        if ($puedeEliminar) {
            // Eliminar respuestas hijas primero
            $db->query("DELETE FROM comentario WHERE parent_id = $id_comentario");
            $controllerComentario->eliminarComentario($id_comentario);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Si no hay vista específica o es la vista de inicio, mostrar la página principal
if (!isset($_GET['view']) || $_GET['view'] === 'index') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inicio - Sistema de Publicaciones</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="mb-4">Bienvenido a Click Administration</h1>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Selecciona tu usuario</h5>
                            <form action="index.php" method="post" class="mb-3">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar usuario...">
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div class="list-group" id="listaUsuarios">
                                    <?php foreach($usuarios as $usuario): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-person-circle"></i>
                                                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                                                    <span class="badge bg-<?php echo $usuario['rol'] === 'admin' ? 'primary' : 'secondary'; ?> ms-2">
                                                        <?php echo htmlspecialchars($usuario['rol']); ?>
                                                    </span>
                                                </div>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_usuario']; ?>">
                                                    <input type="hidden" name="usuario_nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                                    <input type="hidden" name="usuario_rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>">
                                                    <button type="submit" name="seleccionar_usuario" class="btn btn-primary">
                                                        Seleccionar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const buscarInput = document.getElementById('buscarUsuario');
                const listaUsuarios = document.getElementById('listaUsuarios');
                const items = listaUsuarios.getElementsByClassName('usuario-item');

                buscarInput.addEventListener('input', function() {
                    const busqueda = this.value.toLowerCase();
                    
                    Array.from(items).forEach(item => {
                        const nombre = item.dataset.nombre.toLowerCase();
                        const id = item.dataset.id;
                        const texto = `${nombre} ${id}`;
                        
                        if (texto.includes(busqueda)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        </script>
    </body>
    </html>
    <?php
} else {
    // Manejar otras vistas
    switch ($_GET['view']) {
        case 'publicacion':
            $controller = new PublicacionController();
            $controller->setUsuarioActual($usuario_actual);
            require_once 'view/publicacion.php';
            break;
        case 'usuario':
            if ($es_admin) {
                require_once 'view/usuario.php';
            } else {
                header('Location: index.php');
                exit;
            }
            break;
        case 'comentario':
            require_once 'controller/publicacion.php';
            require_once 'controller/comentario.php';
            $controllerPublicacion = new PublicacionController();
            $controllerComentario = new ComentarioController();
            $id_publicacion = $_GET['id_publicacion'] ?? null;

            // Si la petición es AJAX para cargar respuestas
            if (isset($_GET['parent_id'])) {
                if (!isset($publicacion)) {
                    $publicacion = ['publicacion' => ['usuario_id' => null]];
                }
                $respuestas = $controllerComentario->obtenerRespuestas($_GET['parent_id']);
                foreach ($respuestas as $respuesta) {
                    echo '<div class="respuesta-box" id="comentario-' . $respuesta['id_comentario'] . '">';
                    echo '<div class="d-flex align-items-start">';
                    echo '<div class="avatar"><i class="bi bi-person-circle"></i></div>';
                    echo '<div>';
                    echo '<strong>' . htmlspecialchars($respuesta['nombre_usuario']) . '</strong><br>';
                    echo '<span>' . mostrarAt($respuesta['comentario'], $usuario_actual['nombre']) . '</span><br>';
                    echo '<small class="text-muted">' . $respuesta['fecha_comentario'] . '</small>';
                    echo '<div>';
                    echo '<button class="btn btn-link btn-responder p-0" onclick="mostrarFormularioRespuesta(' . $respuesta['id_comentario'] . ', \'' . htmlspecialchars($respuesta['nombre_usuario']) . '\', true)">Responder</button>';
                    echo '<button class="btn-ver-respuestas" onclick="toggleRespuestas(' . $respuesta['id_comentario'] . ')">Ver respuestas</button>';
                    if (puedeEliminarComentario($respuesta, $usuario_actual, $publicacion)) {
                        echo '<button class="btn btn-danger btn-sm ms-2" onclick="eliminarComentario(' . $respuesta['id_comentario'] . ')"><i class="bi bi-trash"></i> Eliminar</button>';
                    }
                    echo '</div>';
                    echo '<div class="respuestas mt-2" id="respuestas-' . $respuesta['id_comentario'] . '" style="display:none;"></div>';
                    echo '<form method="post" action="index.php?view=comentario&id_publicacion=' . $id_publicacion . '" class="mt-2 respuesta-form" id="respuesta-form-' . $respuesta['id_comentario'] . '" style="display:none;">';
                    echo '<div class="d-flex align-items-start">';
                    echo '<div class="avatar"><i class="bi bi-person-circle"></i></div>';
                    echo '<textarea class="form-control me-2" name="comentario" rows="2" placeholder="Responder a ' . htmlspecialchars($respuesta['nombre_usuario']) . '..." required></textarea>';
                    echo '<button type="submit" class="btn btn-primary">Responder</button>';
                    echo '</div>';
                    echo '<input type="hidden" name="parent_id" value="' . $respuesta['id_comentario'] . '">';
                    echo '<input type="hidden" name="id_publicacion" value="' . $id_publicacion . '">';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                exit;
            }

            // Guardar comentario o respuesta si se envía el formulario
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
                $comentario = trim($_POST['comentario']);
                $usuario_id = $_SESSION['usuario_actual']['id'];
                $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
                $at_usuario = !empty($_POST['at_usuario']) ? trim($_POST['at_usuario']) : '';
                if ($at_usuario && strpos($comentario, '@' . $at_usuario) !== 0) {
                    $comentario = '@' . $at_usuario . ' ' . $comentario;
                }
                if (!empty($comentario) && !empty($usuario_id) && !empty($id_publicacion)) {
                    $controllerComentario->agregarComentario($comentario, $usuario_id, $id_publicacion, $parent_id);
                }
                // Redirigir para evitar reenvío del formulario
                header('Location: index.php?view=comentario&id_publicacion=' . $id_publicacion);
                exit;
            }

            $publicacion = $controllerPublicacion->obtener($id_publicacion);
            if (!isset($publicacion['success']) || !$publicacion['success'] || !isset($publicacion['publicacion']['id_publicacion'])) {
                $publicacion = [
                    'success' => false,
                    'publicacion' => []
                ];
            }
            $comentarios = $controllerComentario->obtenerComentarios($id_publicacion);
            require_once 'view/comentario.php';
            break;
        default:
            header('Location: index.php');
            exit;
    }
}
?>