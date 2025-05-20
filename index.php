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
        $descripcion = $_POST['descripcion'] ?? '';
        $usuario_id = $_POST['usuario_id'] ?? '';
        $imagen_url = '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['imagen']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
                $imagen_url = $targetPath;
            } else {
                $_SESSION['mensaje'] = 'Error al subir la imagen';
                $_SESSION['tipo_mensaje'] = 'danger';
                header('Location: index.php?view=publicacion');
                exit;
            }
        }
        $controller = new PublicacionController();
        $controller->setUsuarioActual($_SESSION['usuario_actual']);
        if (!empty($descripcion) && !empty($usuario_id)) {
            $resultado = $controller->crear($descripcion, $imagen_url, $usuario_id);
            $_SESSION['mensaje'] = $resultado['success'] ? 'Publicación creada correctamente' : ($resultado['message'] ?? 'Error al crear la publicación');
            $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        } else {
            $_SESSION['mensaje'] = 'Todos los campos son requeridos';
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        header('Location: index.php?view=publicacion');
        exit;
    }
    else if (isset($_POST['eliminar']) && isset($_POST['id_publicacion'])) {
        $controller = new PublicacionController();
        $controller->setUsuarioActual($_SESSION['usuario_actual']);
        $id_publicacion = $_POST['id_publicacion'];
        $resultado = $controller->eliminar($id_publicacion);
        $_SESSION['mensaje'] = $resultado['success'] ? 'Publicación eliminada correctamente' : ($resultado['message'] ?? 'Error al eliminar la publicación');
        $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        header('Location: index.php?view=publicacion');
        exit;
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
    else if (isset($_POST['eliminar_todas_publicaciones']) && $es_admin) {
        $controller = new PublicacionController();
        $controller->setUsuarioActual($_SESSION['usuario_actual']);
        // Eliminar todas las publicaciones
        $db = new mysqli('localhost', 'root', '', 'clickupdated');
        $db->query('DELETE FROM publicacion');
        $_SESSION['mensaje'] = 'Todas las publicaciones han sido eliminadas correctamente.';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: index.php?view=publicacion');
        exit;
    }
    else if (isset($_POST['eliminar_todos_usuarios']) && $es_admin) {
        // Eliminar todos los usuarios
        $db = new mysqli('localhost', 'root', '', 'clickupdated');
        $db->query('DELETE FROM usuario');
        $_SESSION['mensaje'] = 'Todos los usuarios han sido eliminados correctamente.';
        $_SESSION['tipo_mensaje'] = 'success';
        header('Location: index.php?view=usuario');
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
        <title>Click - Social Media</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #1DA1F2;
                --secondary-color: #14171A;
                --background-color: #15202B;
                --card-bg: #192734;
                --text-primary: #FFFFFF;
                --text-secondary: #8899A6;
                --border-color: #38444D;
                --hover-color: #1E2732;
                --like-color: #E0245E;
                --header-height: 60px;
            }

            body {
                background: var(--background-color);
                color: var(--text-primary);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                min-height: 100vh;
                margin: 0;
            }

            .app-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: var(--header-height);
                background: rgba(21, 32, 43, 0.95);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid var(--border-color);
                z-index: 1000;
                display: flex;
                align-items: center;
                padding: 0 1.5rem;
            }

            .app-logo {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
            }

            .app-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--primary-color);
                margin: 0;
            }

            .main-content {
                padding-top: calc(var(--header-height) + 2rem);
                padding: 2rem;
                max-width: 600px;
                margin: 0 auto;
            }

            .welcome-card {
                background: var(--card-bg);
                border-radius: 16px;
                padding: 2rem;
                margin-bottom: 2rem;
                border: 1px solid var(--border-color);
                text-align: center;
            }

            .welcome-title {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--text-primary);
            }

            .welcome-text {
                color: var(--text-secondary);
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }

            .user-select-card {
                background: var(--card-bg);
                border-radius: 16px;
                padding: 2rem;
                border: 1px solid var(--border-color);
            }

            .user-select-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1.5rem;
                color: var(--text-primary);
            }

            .form-control {
                background: var(--background-color);
                border: 1px solid var(--border-color);
                color: var(--text-primary);
                padding: 12px;
                border-radius: 12px;
                margin-bottom: 1rem;
            }

            .form-control:focus {
                background: var(--background-color);
                border-color: var(--primary-color);
                color: var(--text-primary);
                box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.2);
            }

            .btn-primary {
                background: var(--primary-color);
                border: none;
                padding: 12px 24px;
                border-radius: 30px;
                font-weight: 600;
                transition: all 0.3s ease;
                width: 100%;
            }

            .btn-primary:hover {
                background: #1a91da;
                transform: translateY(-2px);
            }

            .alert {
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                color: var(--text-primary);
                border-radius: 12px;
                margin-bottom: 1.5rem;
            }

            .alert-success {
                border-color: #28a745;
            }

            .alert-danger {
                border-color: #dc3545;
            }

            .user-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .user-item {
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                padding: 1rem;
                margin-bottom: 1rem;
                transition: all 0.3s ease;
            }

            .user-item:hover {
                transform: translateY(-2px);
                border-color: var(--primary-color);
            }

            .user-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .user-avatar {
                width: 48px;
                height: 48px;
                background: var(--primary-color);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
            }

            .user-details {
                flex: 1;
            }

            .user-name {
                font-weight: 600;
                font-size: 1.1rem;
                color: var(--text-primary);
                margin: 0;
            }

            .user-role {
                color: var(--text-secondary);
                font-size: 0.9rem;
                margin: 0;
            }

            @media (max-width: 768px) {
                .main-content {
                    padding: 1rem;
                }
                .welcome-card, .user-select-card {
                    padding: 1.5rem;
                }
            }

            .main-content.centered-welcome {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding-top: 0;
                max-width: 700px;
            }

            .centered-welcome .welcome-card {
                margin-top: 80px;
            }
        </style>
    </head>
    <body>
        <header class="app-header">
            <a href="index.php" class="app-logo">
                <i class="bi bi-chat-dots-fill" style="color: var(--primary-color); font-size: 1.8rem;"></i>
                <h1 class="app-title">Click</h1>
            </a>
        </header>

        <main class="main-content<?php if (!$usuario_actual) echo ' centered-welcome'; ?>">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!$usuario_actual): ?>
                <div class="welcome-card">
                    <h2 class="welcome-title">Bienvenido a Click</h2>
                    <p class="welcome-text">Selecciona un usuario para comenzar a interactuar</p>
                </div>
            <?php else: ?>
                <div class="welcome-card">
                    <h2 class="welcome-title">¡Hola, <?php echo htmlspecialchars($usuario_actual['nombre']); ?>!</h2>
                    <p class="welcome-text">Bienvenido de nuevo a Click</p>
                    <a href="index.php?view=publicacion" class="btn btn-primary">
                        <i class="bi bi-house-door-fill"></i> Ir a Publicaciones
                    </a>
                </div>
            <?php endif; ?>

            <div class="user-select-card">
                <h3 class="user-select-title">Selecciona tu usuario</h3>
                <?php foreach($usuarios as $usuario): ?>
                    <form method="post" class="user-list" style="margin-bottom:0;">
                        <div class="user-item">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="user-details">
                                    <h4 class="user-name"><?php echo htmlspecialchars($usuario['nombre']); ?></h4>
                                    <p class="user-role"><?php echo htmlspecialchars($usuario['rol']); ?></p>
                                </div>
                                <?php if ($usuario_actual && $usuario_actual['id'] == $usuario['id_usuario']): ?>
                                    <button type="button" class="btn btn-primary" disabled>Actual</button>
                                <?php else: ?>
                                    <button type="submit" name="seleccionar_usuario" class="btn btn-primary">
                                        Seleccionar
                                    </button>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_usuario']; ?>">
                            <input type="hidden" name="usuario_nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                            <input type="hidden" name="usuario_rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>">
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </main>

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
                $respuestas = $controllerComentario->obtenerRespuestasAnidadas($_GET['parent_id']);
                foreach ($respuestas as $respuesta) {
                    $usuario_nombre_js = addslashes(htmlspecialchars($respuesta['nombre_usuario'], ENT_QUOTES, 'UTF-8'));
                    echo '<div class="comentario-card respuesta" id="comentario-' . $respuesta['id_comentario'] . '">';
                    echo '<div class="comentario-header">';
                    echo '<div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>';
                    echo '<div class="comentario-info">';
                    echo '<span class="comentario-autor">' . htmlspecialchars($respuesta['nombre_usuario']) . '</span>';
                    echo '<span class="comentario-tiempo">' . $respuesta['fecha_comentario'] . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="comentario-contenido">' . mostrarAt($respuesta['comentario'], $usuario_actual['nombre']) . '</div>';
                    echo '<div class="comentario-acciones">';
                    echo '<button type="button" class="comentario-btn" onclick="mostrarFormularioRespuesta(' . $respuesta['id_comentario'] . ', \'' . $usuario_nombre_js . '\', true)"><i class="bi bi-reply"></i> Responder</button>';
                    echo '<button type="button" class="comentario-btn" onclick="toggleRespuestas(' . $respuesta['id_comentario'] . ')"><i class="bi bi-chat-dots"></i> Ver respuestas</button>';
                    if (puedeEliminarComentario($respuesta, $usuario_actual, $publicacion)) {
                        echo '<button type="button" class="comentario-btn text-danger" onclick="eliminarComentario(' . $respuesta['id_comentario'] . ')"><i class="bi bi-trash"></i> Eliminar</button>';
                    }
                    echo '</div>';
                    // Formulario oculto para responder a la respuesta
                    echo '<form method="post" action="index.php?view=comentario&id_publicacion=' . $id_publicacion . '" class="mt-2 respuesta-form" id="respuesta-form-' . $respuesta['id_comentario'] . '" style="display:none;">';
                    echo '<div class="d-flex align-items-start gap-3">';
                    echo '<div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>';
                    echo '<textarea class="form-control me-2" name="comentario" rows="2" placeholder="Responder a ' . htmlspecialchars($respuesta['nombre_usuario']) . '..." required></textarea>';
                    echo '<button type="submit" class="btn btn-primary">Responder</button>';
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