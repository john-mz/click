<?php
// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si hay un usuario seleccionado
if (!isset($_SESSION['usuario_actual']) || empty($_SESSION['usuario_actual'])) {
    header('Location: index.php');
    exit;
}

require_once 'controller/publicacion.php';
$controller = new PublicacionController();

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

// Manejar peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear'])) {
        $descripcion = $_POST['descripcion'] ?? '';
        $usuario_id = $_POST['usuario_id'] ?? '';
        
        // Manejo de la imagen
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
    else if (isset($_POST['eliminar'])) {
        $id_publicacion = $_POST['id_publicacion'] ?? '';
        if (!empty($id_publicacion)) {
            $resultado = $controller->eliminar($id_publicacion);
            $_SESSION['mensaje'] = $resultado['success'] ? 'Publicación eliminada correctamente' : ($resultado['message'] ?? 'Error al eliminar la publicación');
            $_SESSION['tipo_mensaje'] = $resultado['success'] ? 'success' : 'danger';
        } else {
            $_SESSION['mensaje'] = 'ID de publicación no proporcionado';
            $_SESSION['tipo_mensaje'] = 'danger';
        }
        header('Location: index.php?view=publicacion');
        exit;
    }
}

// Obtener lista de usuarios
$usuarios = $controller->obtenerUsuarios();
if (isset($usuarios['error'])) {
    $usuarios = [];
    $mensaje = 'Error al cargar la lista de usuarios';
    $tipo_mensaje = 'warning';
}

// Obtener publicaciones para mostrar
$publicaciones = $controller->index();
if (isset($publicaciones['error'])) {
    $publicaciones = [];
    if (empty($mensaje)) {
        $mensaje = 'Error al cargar las publicaciones';
        $tipo_mensaje = 'warning';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --reddit-orange: #ff4500;
            --reddit-blue: #0079d3;
            --reddit-dark: #1a1a1b;
            --reddit-light: #ffffff;
            --reddit-gray: #878a8c;
            --sidebar-bg: #161617;
            --sidebar-border: #222223;
            --header-height: 56px;
        }
        body {
            background: #181819;
            color: #fff;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .reddit-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--header-height);
            background: #030303;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 0 1rem;
        }
        .reddit-logo {
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
        }
        .reddit-logo svg {
            width: 38px; height: 38px;
        }
        .reddit-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--reddit-orange);
            margin-left: 12px;
        }
        .reddit-search {
            flex: 1;
            max-width: 500px;
            margin: 0 auto;
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
            display: flex; 
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .reddit-user .bi-person-circle {
            font-size: 1.5rem;
        }
        .reddit-theme-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            margin-right: 12px;
            cursor: pointer;
        }
        .reddit-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: 240px;
            height: calc(100vh - var(--header-height));
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            color: var(--reddit-gray);
            padding-top: 1.5rem;
            z-index: 900;
        }
        .reddit-sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .reddit-sidebar a {
            color: inherit;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .reddit-sidebar a.active, .reddit-sidebar a:hover {
            background: #222223;
            color: var(--reddit-orange);
        }
        .main-content {
            margin-left: 240px;
            padding-top: calc(var(--header-height) + 2rem);
            padding-right: 2rem;
            padding-left: 2rem;
        }
        .btn-reddit-danger {
            background: #ff4500;
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 6px 18px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-reddit-danger:hover {
            background: #b92d06;
            color: #fff;
        }
        @media (max-width: 900px) {
            .reddit-sidebar {
                width: 60px;
                padding: 0.5rem 0;
            }
            .reddit-sidebar a span {
                display: none;
            }
            .main-content {
                margin-left: 60px;
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        .publicacion {
            background: #232324;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            transition: all 0.3s ease;
            display: flex;
            padding: 0;
            box-sizing: border-box;
            flex-direction: column;
        }
        .contenido-publicacion {
            background: transparent;
            border-radius: 12px;
            flex: 1;
            padding: 1.1rem 1.2rem;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .publicacion-img-bg {
            width: 100%;
            height: 320px;
            background: #f6f7f8;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0.7rem 0;
            overflow: hidden;
            position: relative;
        }
        .publicacion-imagen {
            display: block;
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            background: transparent;
            margin: 0 auto;
        }
        .main-content, .container {
            background: #181819 !important;
            color: #fff;
        }
        .form-control, .form-select {
            background: #232324;
            color: #fff;
            border: 1px solid #333;
        }
        .form-control:focus, .form-select:focus {
            background: #232324;
            color: #fff;
            border-color: var(--reddit-orange);
            box-shadow: 0 0 0 0.2rem rgba(255,69,0,0.15);
        }
        .modal-content {
            background: #181819;
            color: #fff;
            border-radius: 12px;
        }
        .modal-header {
            background: #232324;
            color: #fff;
            border-bottom: 1px solid #333;
        }
        .modal-footer {
            background: #181819;
            border-top: 1px solid #333;
        }
        .alert {
            background: #232324;
            color: #fff;
            border: 1px solid #333;
        }
        .table {
            background: #181819;
            color: #fff;
        }
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #232324;
        }
        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #181819;
        }
        .table thead th {
            background: #222223;
            color: #fff;
        }
        .btn, .btn-reddit, .btn-reddit-danger, .btn-outline-primary, .btn-outline-danger {
            color: #fff !important;
        }
        .btn-outline-primary {
            border-color: var(--reddit-blue);
            color: var(--reddit-blue) !important;
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: var(--reddit-blue);
            color: #fff !important;
        }
        .btn-outline-danger {
            border-color: #ff4d4f;
            color: #ff4d4f !important;
            background: transparent;
        }
        .btn-outline-danger:hover {
            background: #ff4d4f;
            color: #fff !important;
        }
        .dropdown-menu {
            background: #232324;
            color: #fff;
        }
        .dropdown-item {
            color: #fff;
        }
        .dropdown-item:hover, .dropdown-item:focus {
            background: #333;
            color: #fff;
        }
        .publicacion-header .autor {
            color: var(--reddit-orange);
        }
        .publicacion-info, .publicacion-titulo, .publicacion-acciones, .badge {
            color: #fff !important;
        }
        .btn-reddit {
            background: var(--reddit-orange);
            border: none;
        }
        .btn-reddit-danger {
            background: #ff4d4f;
            border: none;
        }
        .btn-click {
            background: var(--reddit-orange);
            color: #fff !important;
            border: none;
            border-radius: 20px;
            padding: 6px 18px;
            font-weight: 500;
            font-size: 1rem;
            transition: background 0.2s;
            margin-right: 0.3rem;
        }
        .btn-click:hover {
            background: #d93a00;
            color: #fff !important;
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
    </style>
</head>
<body>
    <header class="reddit-header">
        <div class="d-flex align-items-center">
            <div class="reddit-logo">
                <svg viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="19" cy="19" r="18" fill="#ff4500"/>
                    <text x="10" y="25" font-size="16" font-family="Arial" fill="#fff" font-weight="bold">C</text>
                    <path d="M 25 15 Q 28 19 19 23 Q 10 19 13 15" stroke="#fff" stroke-width="2" fill="none"/>
                    <circle cx="28" cy="10" r="2" fill="#fff"/>
                </svg>
            </div>
            <span class="reddit-title">Click</span>
        </div>
        <form class="reddit-search">
            <input type="text" placeholder="Buscar en Click..." />
        </form>
        <div class="reddit-user">
            <i class="bi bi-person-circle"></i>
            <span style="font-size:1.1rem;font-weight:600; color:#fff;">
                <?php echo htmlspecialchars($_SESSION['usuario_actual']['nombre'] ?? ''); ?>
            </span>
            <span class="badge bg-secondary ms-2" style="background:#878a8c; font-size:0.95rem;">
                <?php echo htmlspecialchars($_SESSION['usuario_actual']['rol'] ?? ''); ?>
            </span>
            <span class="badge bg-dark ms-2" style="background:#222223; font-size:0.95rem;">
                ID: <?php echo htmlspecialchars($_SESSION['usuario_actual']['id'] ?? ''); ?>
            </span>
        </div>
    </header>
    <aside class="reddit-sidebar">
        <nav>
            <a href="index.php?view=publicacion" class="<?php echo ($_GET['view'] ?? '') === 'publicacion' ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i> <span>Publicaciones</span>
            </a>
            <a href="index.php?view=usuario" class="<?php echo ($_GET['view'] ?? '') === 'usuario' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> <span>Usuarios</span>
            </a>
        </nav>
    </aside>
    <main class="main-content">
    <div class="container mt-4">
        <div class="mb-4">
            <a href="index.php" class="btn btn-click">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button class="btn btn-click me-2" id="verMisPublicaciones">
                    <i class="bi bi-person-lines-fill"></i> Mis publicaciones
                </button>
                <button class="btn btn-click-dark" id="verTodasPublicaciones">
                    <i class="bi bi-people-fill"></i> Todas
                </button>
            </div>
            <button class="btn btn-click mb-3" data-bs-toggle="modal" data-bs-target="#crearPublicacionModal" id="btnCrearPublicacion">
                <i class="bi bi-plus-lg"></i> Nueva Publicación
            </button>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div id="contenedorPublicaciones">
            <?php if (empty($publicaciones)): ?>
                <div class="alert alert-info">
                    No hay publicaciones disponibles.
                </div>
            <?php else: ?>
                <?php foreach($publicaciones as $publicacion): ?>
                <div class="publicacion" data-usuario-id="<?php echo $publicacion['usuario_id']; ?>">
                    <div class="contenido-publicacion" style="text-align:center; align-items:center;">
                        <div class="publicacion-header mb-2 d-flex justify-content-center align-items-center" style="gap:8px;">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="autor display-6" style="font-size:1.4rem;font-weight:700; color:var(--reddit-orange);">
                                <?php echo htmlspecialchars($publicacion['nombre_usuario']); ?>
                            </span>
                        </div>
                        <div class="publicacion-info mb-2" style="color:#878a8c; font-size:0.98rem; text-align:center;">
                            <span class="me-3"><i class="bi bi-clock"></i> <?php echo $publicacion['fecha_creacion']; ?></span>
                        </div>
                        <div class="publicacion-titulo mb-2" style="font-size:1.1rem; font-weight:500; color:var(--reddit-light); text-align:center;">
                            <?php echo htmlspecialchars($publicacion['descripcion']); ?>
                        </div>
                        <div class="publicacion-img-bg d-flex justify-content-center align-items-center" style="margin:0.7rem auto;">
                            <?php if (!empty($publicacion['imagen_url'])): ?>
                                <img src="<?php echo htmlspecialchars($publicacion['imagen_url']); ?>" class="publicacion-imagen" alt="Imagen de la publicación">
                            <?php endif; ?>
                        </div>
                        <div class="publicacion-acciones mt-2 d-flex align-items-center justify-content-center" style="gap:10px;">
                            <a href="index.php?view=comentario&id_publicacion=<?php echo $publicacion['id_publicacion']; ?>" class="btn btn-click">
                                <i class="bi bi-chat-dots"></i>
                                <span>Comentar</span>
                            </a>
                            <?php if (
                                $_SESSION['usuario_actual']['rol'] === 'admin' || 
                                $_SESSION['usuario_actual']['id'] == $publicacion['usuario_id']
                            ): ?>
                                <button class="btn btn-click accion-btn" onclick="eliminarModal(<?php echo $publicacion['id_publicacion']; ?>)">
                                    <i class="bi bi-trash"></i>
                                    <span>Eliminar</span>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-click-dark btn-sm btn-like"
                                    data-publicacion-id="<?php echo $publicacion['id_publicacion']; ?>"
                                    onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'like')">
                                <i class="bi bi-hand-thumbs-up"></i>
                                <span class="contador-likes"><?php echo $publicacion['meGusta'] ?? 0; ?></span>
                            </button>
                            <button class="btn btn-click-dark btn-sm btn-dislike"
                                    data-publicacion-id="<?php echo $publicacion['id_publicacion']; ?>"
                                    onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'dislike')">
                                <i class="bi bi-hand-thumbs-down"></i>
                                <span class="contador-dislikes"><?php echo $publicacion['noMeGusta'] ?? 0; ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para crear publicación -->
    <div class="modal fade" id="crearPublicacionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="index.php?view=publicacion" method="post" enctype="multipart/form-data">
                        <?php if ($_SESSION['usuario_actual']['rol'] === 'admin'): ?>
                            <div class="mb-3">
                                <label for="usuario_id" class="form-label">Usuario</label>
                                <select class="form-select" id="usuario_id" name="usuario_id" required>
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach($usuarios as $usuario): ?>
                                        <option value="<?php echo $usuario['id_usuario']; ?>">
                                            <?php echo htmlspecialchars($usuario['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_actual']['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen (Opcional)</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-click-dark" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-click" name="crear">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar publicación -->
    <div class="modal fade" id="eliminarPublicacionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="index.php?view=publicacion" method="post" id="formEliminar">
                        <input type="hidden" id="id_publicacion_eliminar" name="id_publicacion" value="">
                        <p>¿Estás seguro de que deseas eliminar esta publicación?</p>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-click-dark" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-click" name="eliminar">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const verMisPublicaciones = document.getElementById('verMisPublicaciones');
            const verTodasPublicaciones = document.getElementById('verTodasPublicaciones');
            const publicaciones = document.querySelectorAll('.publicacion');
            const usuarioActualId = <?php echo $_SESSION['usuario_actual']['id']; ?>;

            // Función para mostrar todas las publicaciones
            function mostrarTodasPublicaciones() {
                publicaciones.forEach(publicacion => {
                    publicacion.style.display = 'block';
                });
            }

            // Función para mostrar solo las publicaciones del usuario actual
            function mostrarMisPublicaciones() {
                publicaciones.forEach(publicacion => {
                    const usuarioId = parseInt(publicacion.dataset.usuarioId);
                    publicacion.style.display = usuarioId === usuarioActualId ? 'block' : 'none';
                });
            }

            // Event listeners para los botones
            verMisPublicaciones.addEventListener('click', mostrarMisPublicaciones);
            verTodasPublicaciones.addEventListener('click', mostrarTodasPublicaciones);

            // Mostrar todas las publicaciones por defecto
            mostrarTodasPublicaciones();

            // Función para manejar reacciones
            window.reaccionar = function(publicacionId, tipo) {
                const formData = new FormData();
                formData.append('publicacion_id', publicacionId);
                formData.append('tipo', tipo);

                fetch('index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Obtener los elementos de los contadores
                        const contadorLikes = document.querySelector(`.btn-like[data-publicacion-id="${publicacionId}"] .contador-likes`);
                        const contadorDislikes = document.querySelector(`.btn-dislike[data-publicacion-id="${publicacionId}"] .contador-dislikes`);
                        
                        // Obtener los valores actuales
                        let likes = parseInt(contadorLikes.textContent) || 0;
                        let dislikes = parseInt(contadorDislikes.textContent) || 0;
                        
                        // Actualizar según la acción
                        if (data.action === 'added') {
                            if (tipo === 'like') {
                                likes++;
                                // Si había un dislike previo, lo quitamos
                                if (dislikes > 0) dislikes--;
                            } else {
                                dislikes++;
                                // Si había un like previo, lo quitamos
                                if (likes > 0) likes--;
                            }
                        } else if (data.action === 'removed') {
                            if (tipo === 'like') {
                                if (likes > 0) likes--;
                            } else {
                                if (dislikes > 0) dislikes--;
                            }
                        }
                        
                        // Actualizar los contadores en la interfaz
                        contadorLikes.textContent = likes;
                        contadorDislikes.textContent = dislikes;

                        // Actualizar el estilo de los botones
                        const btnLike = document.querySelector(`.btn-like[data-publicacion-id="${publicacionId}"]`);
                        const btnDislike = document.querySelector(`.btn-dislike[data-publicacion-id="${publicacionId}"]`);
                        
                        if (tipo === 'like') {
                            btnLike.classList.toggle('active', data.action === 'added');
                            btnDislike.classList.remove('active');
                        } else {
                            btnDislike.classList.toggle('active', data.action === 'added');
                            btnLike.classList.remove('active');
                        }
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            };
        });

        // Función para eliminar publicación
        function eliminarModal(id) {
            document.getElementById('id_publicacion_eliminar').value = id;
            const modal = new bootstrap.Modal(document.getElementById('eliminarPublicacionModal'));
            modal.show();
        }
    </script>

    <style>
        .btn-like.active {
            background-color: #0d6efd;
            color: white;
        }
        .btn-dislike.active {
            background-color: #dc3545;
            color: white;
        }
    </style>
</body>
</html>
