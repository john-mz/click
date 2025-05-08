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
        <style>
            :root {
                --reddit-orange: #ff4500;
                --reddit-dark: #1a1a1b;
                --reddit-light: #ffffff;
                --reddit-gray: #878a8c;
                --reddit-gray-dark: #232324;
                --header-height: 56px;
            }
            body {
                background: #181819;
                color: #fff;
                min-height: 100vh;
                margin: 0;
                font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
                padding-top: 2rem;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 2rem;
            }
            .card {
                background: #232324;
                border: none;
                border-radius: 12px;
                margin-bottom: 1.5rem;
                box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            }
            .card-header {
                background: #232324;
                border-bottom: 1px solid #333;
                padding: 1.5rem;
            }
            .card-body {
                padding: 1.5rem;
            }
            .btn-click {
                background: var(--reddit-orange);
                color: #fff !important;
                border: none;
                border-radius: 20px;
                padding: 8px 24px;
                font-weight: 500;
                font-size: 1.1rem;
                transition: background 0.2s;
            }
            .btn-click:hover {
                background: #d93a00;
                color: #fff !important;
            }
            .reddit-logo {
                width: 38px; height: 38px;
                margin: 0 18px 0 12px;
                display: flex; align-items: center; justify-content: center;
            }
            .reddit-logo svg {
                width: 38px; height: 38px;
            }
            .reddit-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--reddit-orange);
                margin-right: 24px;
            }
            .reddit-search {
                flex: 1;
                max-width: 400px;
                margin: 0 24px;
            }
            .reddit-search input {
                width: 100%;
                border-radius: 20px;
                border: none;
                padding: 7px 16px;
                background: #272729;
                color: #fff;
            }
            .reddit-user {
                display: flex; align-items: center;
                gap: 10px;
                margin-right: 24px;
            }
            .reddit-user .bi-person-circle {
                font-size: 1.5rem;
            }
            .main-content {
                padding-top: calc(var(--header-height) + 2rem);
                padding-right: 2rem;
                padding-left: 2rem;
                min-height: 100vh;
            }
            .list-group-item {
                background: #181819;
                border: 1px solid #232324;
                border-radius: 10px !important;
                margin-bottom: 10px;
                color: #fff;
            }
            .list-group-item:hover {
                background: #232324;
                color: #fff;
            }
            .btn-click-dark {
                background: #333;
                color: #fff !important;
                border: none;
                border-radius: 20px;
                padding: 6px 18px;
                font-weight: 500;
                font-size: 1rem;
                transition: background 0.2s;
                margin-right: 0.3rem;
            }
            .btn-click-dark:hover {
                background: #222;
                color: #fff !important;
            }
            .badge {
                padding: 6px 12px;
                border-radius: 6px;
                font-weight: 500;
                letter-spacing: 0.3px;
                font-size: 0.95rem;
            }
            .badge.bg-primary {
                background: var(--reddit-orange) !important;
                color: #fff !important;
            }
            .badge.bg-secondary {
                background: var(--reddit-gray) !important;
                color: #fff !important;
            }
            .welcome-section {
                text-align: center;
                margin-bottom: 2rem;
            }
            .welcome-section h1 {
                color: var(--reddit-orange);
                font-weight: 700;
                letter-spacing: -0.5px;
            }
            .welcome-section .lead {
                color: #bbb;
            }
            .user-avatar {
                width:40px;
                height:40px;
                display:flex;
                align-items:center;
                justify-content:center;
                background: var(--reddit-gray-dark);
                border-radius:50%;
                color:var(--reddit-orange);
                font-size: 1.5rem;
            }
            @media (max-width: 900px) {
                .main-content {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
            }
            .input-group.mb-4 {
                background: #fff;
                border-radius: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                padding: 2px 6px;
            }
            .input-group.mb-4 .input-group-text {
                background: #fff;
                border: none;
                color: #333;
            }
            .input-group.mb-4 .bi-search {
                color: #333 !important;
            }
            .input-group.mb-4 .form-control {
                background: #fff !important;
                color: #232324 !important;
                border: none;
                border-radius: 20px;
                font-weight: 500;
            }
            .input-group.mb-4 .form-control::placeholder {
                color: #888 !important;
                opacity: 1;
            }
            .form-label {
                color: #fff;
                font-weight: 500;
            }
            .form-select {
                background: #232324 !important;
                color: #fff !important;
                border: 1.5px solid #333;
                border-radius: 12px;
                font-size: 1.1rem;
                font-weight: 500;
                box-shadow: none;
                transition: border-color 0.2s;
            }
            .form-select:focus {
                border-color: var(--reddit-orange);
                box-shadow: 0 0 0 0.15rem rgba(255,69,0,0.15);
                background: #232324 !important;
                color: #fff !important;
            }
            .form-select option {
                background: #232324;
                color: #fff;
            }
            .form-select option[disabled], .form-select option[value=""] {
                color: #bbb;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header text-center">
                            <h1 class="mb-4" style="color: var(--reddit-orange);">Bienvenido a Click</h1>
                            <p class="lead mb-0">Selecciona un usuario para comenzar</p>
                        </div>
                        <div class="card-body">
                            <form method="post" class="mb-4">
                                <div class="list-group" id="listaUsuarios">
                                    <?php foreach($usuarios as $usuario): ?>
                                        <div class="list-group-item usuario-item d-flex justify-content-between align-items-center" style="background:#232324; color:#fff; border:1px solid #333; border-radius:10px; margin-bottom:10px;">
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:#333;border-radius:50%;color:var(--reddit-orange);">
                                                    <i class="bi bi-person-circle fs-4"></i>
                                                </div>
                                                <div class="user-info">
                                                    <h6 class="mb-0" style="color:#fff;font-weight:600;">
                                                        <?php echo htmlspecialchars($usuario['nombre']); ?>
                                                    </h6>
                                                    <span class="badge <?php echo $usuario['rol'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>" style="margin-left:2px;">
                                                        <?php echo htmlspecialchars($usuario['rol']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_usuario']; ?>">
                                                <input type="hidden" name="usuario_nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                                <input type="hidden" name="usuario_rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>">
                                                <button type="submit" name="seleccionar_usuario" class="btn btn-click">
                                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                                    Seleccionar
                                                </button>
                                            </form>
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
                    echo '<div class="comentario-header">';
                    echo '<i class="bi bi-person-circle me-2"></i>';
                    echo '<span class="autor">' . htmlspecialchars($respuesta['nombre_usuario']) . '</span>';
                    echo '<span class="tiempo">' . $respuesta['fecha_comentario'] . '</span>';
                    echo '</div>';
                    echo '<div class="comentario-contenido">' . mostrarAt($respuesta['comentario'], $usuario_actual['nombre']) . '</div>';
                    echo '<div class="comentario-acciones">';
                    echo '<button type="button" class="btn btn-click accion-btn" onclick="mostrarFormularioRespuesta(' . $respuesta['id_comentario'] . ', \'' . addslashes(htmlspecialchars($respuesta['nombre_usuario'], ENT_QUOTES, 'UTF-8')) . '\', true)"><i class="bi bi-reply"></i> <span>Responder</span></button>';
                    if (puedeEliminarComentario($respuesta, $usuario_actual, $publicacion)) {
                        echo '<button type="button" class="btn btn-click accion-btn" onclick="eliminarComentario(' . $respuesta['id_comentario'] . ')"><i class="bi bi-trash"></i> <span>Eliminar</span></button>';
                    }
                    echo '</div>';
                    // Formulario oculto para responder a la respuesta
                    echo '<form method="post" action="index.php?view=comentario&id_publicacion=' . $id_publicacion . '" class="mt-2 respuesta-form" id="respuesta-form-' . $respuesta['id_comentario'] . '" style="display:none;">';
                    echo '<div class="form-group">';
                    echo '<textarea class="form-control" name="comentario" rows="2" placeholder="Responder a ' . htmlspecialchars($respuesta['nombre_usuario']) . '..." required></textarea>';
                    echo '</div>';
                    echo '<div class="d-flex justify-content-end mt-2">';
                    echo '<button type="submit" class="btn btn-click">Responder</button>';
                    echo '</div>';
                    echo '<input type="hidden" name="parent_id" value="' . $respuesta['id_comentario'] . '">';
                    echo '<input type="hidden" name="id_publicacion" value="' . $id_publicacion . '">';
                    echo '</form>';
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