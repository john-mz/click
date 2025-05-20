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

        /* Header Estilo Twitter */
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
            justify-content: space-between;
        }

        .header-content {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .header-logo {
            flex: 0 0 auto;
            z-index: 2;
        }

        .header-search {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 400px;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .search-bar {
            width: 100%;
            max-width: 400px;
            background: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            padding: 0.5em 1.5em;
            color: var(--text-primary);
            font-size: 1.05em;
            outline: none;
            transition: border 0.2s;
        }

        .search-bar:focus {
            border-color: var(--primary-color);
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-left: auto;
            z-index: 2;
        }

        .header-avatar {
            width: 38px;
            height: 38px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
        }

        .header-username {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .header-role {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .app-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .app-logo img {
            width: 32px;
            height: 32px;
        }

        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .user-profile:hover {
            background: var(--hover-color);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* Sidebar Estilo YouTube */
        .app-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: 240px;
            height: calc(100vh - var(--header-height));
            background: var(--card-bg);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem 0;
            overflow-y: auto;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0 1rem;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: none;
            border: none;
            font-weight: 500;
            font-size: 1.05rem;
            cursor: pointer;
        }
        .sidebar-item.active, .sidebar-item.selected {
            background: var(--hover-color);
            color: var(--primary-color) !important;
            font-weight: 700;
        }
        .sidebar-item:hover {
            background: var(--hover-color);
            color: var(--primary-color);
        }

        .sidebar-item:disabled, .sidebar-item[disabled] {
            background: var(--card-bg);
            color: #b0b8c1;
            opacity: 0.7;
            cursor: not-allowed;
            box-shadow: none;
            border: 1.5px solid var(--border-color);
        }

        .sidebar-item:not(:disabled):not([disabled]) {
            box-shadow: 0 2px 8px rgba(29,161,242,0.05);
        }

        .sidebar-item i {
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            padding: calc(var(--header-height) + 2rem) 2rem 2rem;
        }

        /* Post Card Estilo Twitter */
        .post-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .post-author {
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
        }

        .post-author:hover {
            color: var(--primary-color);
        }

        .post-time {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .post-image {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .post-actions {
            display: flex;
            gap: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            background: none;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-button:hover {
            background: var(--hover-color);
            color: var(--primary-color);
        }

        .action-button.like.active {
            color: var(--like-color);
        }

        .action-button.dislike.active {
            color: #FF4500;
        }

        /* Botones principales */
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1a91da;
            transform: translateY(-2px);
        }

        /* Modal Estilo */
        .modal-content {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .form-control {
            background: var(--background-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px;
            border-radius: 12px;
        }

        .form-control:focus {
            background: var(--background-color);
            border-color: var(--primary-color);
            color: var(--text-primary);
            box-shadow: 0 0 0 2px rgba(29, 161, 242, 0.2);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .app-sidebar {
                width: 80px;
            }
            .sidebar-item span {
                display: none;
            }
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 900px) {
            .header-search {
                max-width: 200px;
            }
            .search-bar {
                max-width: 200px;
                font-size: 1em;
            }
        }

        @media (max-width: 600px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
            .header-search {
                position: static;
                transform: none;
                width: 100%;
                max-width: 100%;
                margin: 0 0 0.5rem 0;
            }
            .search-bar {
                width: 100%;
                max-width: 100%;
            }
            .header-user {
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-content">
            <div class="header-logo">
                <a href="index.php" class="app-logo">
                    <i class="bi bi-chat-dots-fill" style="color: var(--primary-color); font-size: 1.8rem;"></i>
                    <h1 class="app-title">Click</h1>
                </a>
            </div>
            <div class="header-search">
                <form method="get" action="index.php" style="width:100%;">
                    <input type="hidden" name="view" value="publicacion">
                    <input type="text" class="search-bar" name="q" placeholder="Buscar en Click..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                </form>
            </div>
            <div class="header-user ms-auto">
                <div class="header-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="header-username">
                        <?php echo htmlspecialchars($_SESSION['usuario_actual']['nombre'] ?? ''); ?>
                    </div>
                    <div class="header-role">
                        <?php echo htmlspecialchars($_SESSION['usuario_actual']['rol'] ?? ''); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <aside class="app-sidebar">
        <nav class="sidebar-nav">
            <a href="index.php?view=publicacion" class="sidebar-item <?php echo ($_GET['view'] ?? '') === 'publicacion' ? 'active' : ''; ?>" id="sidebar-inicio">
                <i class="bi bi-house-door-fill"></i>
                <span>Inicio</span>
            </a>
            <a href="index.php?view=usuario" class="sidebar-item <?php echo ($_GET['view'] ?? '') === 'usuario' ? 'active' : ''; ?>" id="sidebar-usuarios">
                <i class="bi bi-people-fill"></i>
                <span>Usuarios</span>
            </a>
            <button class="sidebar-item" id="verMisPublicaciones">
                <i class="bi bi-person-lines-fill"></i>
                <span>Mis Publicaciones</span>
            </button>
            <button class="sidebar-item" id="verTodasPublicaciones">
                <i class="bi bi-grid-fill"></i>
                <span>Todas</span>
            </button>
        </nav>
    </aside>

    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-end mb-4">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearPublicacionModal">
                    <i class="bi bi-plus-lg"></i> Nueva Publicación
                </button>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['usuario_actual']['rol'] === 'admin'): ?>
                <form method="post" action="index.php" style="display:inline-block; margin-bottom: 1.5rem;">
                    <button type="submit" name="eliminar_todas_publicaciones" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar TODAS las publicaciones?');">
                        <i class="bi bi-trash"></i> Eliminar todas las publicaciones
                    </button>
                </form>
            <?php endif; ?>

            <div id="contenedorPublicaciones">
                <?php if (empty($publicaciones)): ?>
                    <div class="alert alert-info">
                        No hay publicaciones disponibles.
                    </div>
                <?php else: ?>
                    <?php foreach($publicaciones as $publicacion): ?>
                    <div class="post-card" data-usuario-id="<?php echo $publicacion['usuario_id']; ?>">
                        <div class="post-header">
                            <div class="user-avatar">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <a href="#" class="post-author"><?php echo htmlspecialchars($publicacion['nombre_usuario']); ?></a>
                                <div class="post-time">
                                    <i class="bi bi-clock"></i> <?php echo $publicacion['fecha_creacion']; ?>
                                </div>
                            </div>
                        </div>
                        <div class="post-content">
                            <?php echo htmlspecialchars($publicacion['descripcion']); ?>
                        </div>
                        <?php if (!empty($publicacion['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($publicacion['imagen_url']); ?>" class="post-image" alt="Imagen de la publicación">
                        <?php endif; ?>
                        <div class="post-actions">
                            <a href="index.php?view=comentario&id_publicacion=<?php echo $publicacion['id_publicacion']; ?>" class="action-button">
                                <i class="bi bi-chat-dots"></i>
                                <span>Comentar</span>
                            </a>
                            <button class="action-button like" onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'like')">
                                <i class="bi bi-hand-thumbs-up"></i>
                                <span class="contador-likes"><?php echo $publicacion['meGusta'] ?? 0; ?></span>
                            </button>
                            <button class="action-button dislike" onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'dislike')">
                                <i class="bi bi-hand-thumbs-down"></i>
                                <span class="contador-dislikes"><?php echo $publicacion['noMeGusta'] ?? 0; ?></span>
                            </button>
                            <?php if ($_SESSION['usuario_actual']['rol'] === 'admin'): ?>
                                <button class="action-button text-danger" onclick="eliminarModal(<?php echo $publicacion['id_publicacion']; ?>)">
                                    <i class="bi bi-trash"></i>
                                    <span>Eliminar</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" name="crear">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const verMisPublicaciones = document.getElementById('verMisPublicaciones');
            const verTodasPublicaciones = document.getElementById('verTodasPublicaciones');
            const publicaciones = document.querySelectorAll('.post-card');
            const usuarioActualId = <?php echo $_SESSION['usuario_actual']['id']; ?>;
            const sidebarInicio = document.getElementById('sidebar-inicio');
            const sidebarUsuarios = document.getElementById('sidebar-usuarios');

            // Función para mostrar todas las publicaciones
            function mostrarTodasPublicaciones() {
                publicaciones.forEach(publicacion => {
                    publicacion.style.display = 'block';
                });
                verTodasPublicaciones.classList.add('active');
                verMisPublicaciones.classList.remove('active');
                sidebarInicio.classList.remove('active');
                sidebarUsuarios.classList.remove('active');
            }

            // Función para mostrar solo las publicaciones del usuario actual
            function mostrarMisPublicaciones() {
                publicaciones.forEach(publicacion => {
                    const usuarioId = parseInt(publicacion.dataset.usuarioId);
                    publicacion.style.display = usuarioId === usuarioActualId ? 'block' : 'none';
                });
                verMisPublicaciones.classList.add('active');
                verTodasPublicaciones.classList.remove('active');
                sidebarInicio.classList.remove('active');
                sidebarUsuarios.classList.remove('active');
            }

            // Event listeners para los botones
            verMisPublicaciones.addEventListener('click', mostrarMisPublicaciones);
            verTodasPublicaciones.addEventListener('click', mostrarTodasPublicaciones);

            // Al cargar la página, resaltar solo el botón de Inicio si la vista es 'publicacion'
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view') || 'publicacion';
            if (view === 'publicacion') {
                sidebarInicio.classList.add('active');
                verMisPublicaciones.classList.remove('active');
                verTodasPublicaciones.classList.remove('active');
                // Mostrar todas las publicaciones por defecto
                publicaciones.forEach(publicacion => {
                    publicacion.style.display = 'block';
                });
            }

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
                        const contadorLikes = document.querySelector(`.like[data-publicacion-id="${publicacionId}"] .contador-likes`);
                        const contadorDislikes = document.querySelector(`.dislike[data-publicacion-id="${publicacionId}"] .contador-dislikes`);
                        
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
                        const btnLike = document.querySelector(`.like[data-publicacion-id="${publicacionId}"]`);
                        const btnDislike = document.querySelector(`.dislike[data-publicacion-id="${publicacionId}"]`);
                        
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
</body>
</html>
