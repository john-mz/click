<?php
// Mostrar título y datos básicos de la publicación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- <script src="view/js/publicacion.js"></script> -->
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
            background: #ff4d4f;
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
        .btn-reddit {
            background: var(--reddit-orange);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 6px 18px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-reddit:hover {
            background: #d93a00;
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
        .card, .comentario-box {
            background: #232324;
            color: #fff;
        }
        .comentario-box {
            background: #232324;
            border-radius: 14px;
            box-shadow: none;
            margin-bottom: 18px;
            padding: 1.1rem 1.3rem 0.7rem 1.3rem;
            display: flex;
            flex-direction: column;
        }
        .comentario-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.2rem;
        }
        .comentario-header .autor {
            color: var(--reddit-orange);
            font-weight: 600;
        }
        .comentario-header .tiempo {
            color: #bbb;
            font-size: 0.98rem;
            margin-left: 8px;
        }
        .comentario-contenido {
            margin-bottom: 0.7rem;
            font-size: 1.08rem;
            color: #fff;
            word-break: break-word;
        }
        .comentario-acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 0.5rem;
        }
        .comentario-info, .comentario-titulo, .comentario-acciones, .badge {
            color: #fff !important;
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
        .form-control::placeholder {
            color: #bbb !important;
            opacity: 1;
        }
        .btn-responder { font-size: 0.9em; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #eee; display: inline-block; text-align: center; line-height: 40px; font-size: 1.5em; color: #888; margin-right: 10px; }
        .btn-ver-respuestas { color: #0d6efd; background: none; border: none; padding: 0; }
        .at-mention { color: #00bfff; font-weight: bold; }
        .respuestas {
            margin-left: 0 !important;
            padding-left: 0 !important;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Comentarios</h1>
            <a href="index.php?view=publicacion" class="btn btn-reddit">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <?php if ($publicacion['success'] ?? false): ?>
                    <div class="flex-grow-1">
                        <div class="comentario-header">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="autor"><?php echo htmlspecialchars($publicacion['publicacion']['nombre_usuario']); ?></span>
                            <span class="tiempo"><?php echo $publicacion['publicacion']['fecha_creacion']; ?></span>
                        </div>
                        <div class="comentario-contenido">
                            <?php echo htmlspecialchars($publicacion['publicacion']['descripcion']); ?>
                        </div>
                        <?php if (!empty($publicacion['publicacion']['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($publicacion['publicacion']['imagen_url']); ?>" class="img-fluid mb-2" style="max-width:200px;">
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">No se encontró la publicación.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($publicacion['success'] ?? false): ?>
        <!-- Formulario para nuevo comentario -->
        <form method="post" action="index.php?view=comentario&id_publicacion=<?php echo $publicacion['publicacion']['id_publicacion']; ?>" class="mb-4">
            <div class="d-flex align-items-start">
                <div class="avatar"><i class="bi bi-person-circle"></i></div>
                <textarea class="form-control me-2" name="comentario" rows="2" placeholder="Añade un comentario..." required></textarea>
                <button type="submit" class="btn btn-primary">Comentar</button>
            </div>
            <input type="hidden" name="parent_id" value="">
            <input type="hidden" name="id_publicacion" value="<?php echo $publicacion['publicacion']['id_publicacion']; ?>">
        </form>

        <!-- Listado de comentarios -->
        <div id="comentarios-lista">
            <?php if (empty($comentarios)): ?>
                <div class="alert alert-info">No hay comentarios aún. ¡Sé el primero en comentar!</div>
            <?php else: ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario-box" id="comentario-<?php echo $comentario['id_comentario']; ?>">
                        <div class="d-flex align-items-start">
                            <div class="avatar"><i class="bi bi-person-circle"></i></div>
                            <div>
                                <strong><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></strong><br>
                                <span><?php echo mostrarAt($comentario['comentario'], $usuario_actual['nombre']); ?></span><br>
                                <small class="text-muted"><?php echo $comentario['fecha_comentario']; ?></small>
                                <div>
                                    <button class="btn btn-link btn-responder p-0" onclick="mostrarFormularioRespuesta(<?php echo $comentario['id_comentario']; ?>, '<?php echo htmlspecialchars($comentario['nombre_usuario']); ?>', false)">Responder</button>
                                    <button class="btn-ver-respuestas" onclick="toggleRespuestas(<?php echo $comentario['id_comentario']; ?>)">Ver respuestas</button>
                                    <button class="btn btn-danger btn-sm ms-2" style="display:<?php echo puedeEliminarComentario($comentario, $usuario_actual, $publicacion) ? 'inline-block' : 'none'; ?>" onclick="eliminarComentario(<?php echo $comentario['id_comentario']; ?>)"><i class="bi bi-trash"></i> Eliminar</button>
                                </div>
                                <div class="respuestas mt-2" id="respuestas-<?php echo $comentario['id_comentario']; ?>" style="display:none;"></div>
                                <form method="post" action="index.php?view=comentario&id_publicacion=<?php echo $publicacion['publicacion']['id_publicacion']; ?>" class="mt-2 respuesta-form" id="respuesta-form-<?php echo $comentario['id_comentario']; ?>" style="display:none;">
                                    <div class="d-flex align-items-start">
                                        <div class="avatar"><i class="bi bi-person-circle"></i></div>
                                        <textarea class="form-control me-2" name="comentario" rows="2" placeholder="Responder a <?php echo htmlspecialchars($comentario['nombre_usuario']); ?>..." required></textarea>
                                        <button type="submit" class="btn btn-primary">Responder</button>
                                    </div>
                                    <input type="hidden" name="parent_id" value="<?php echo $comentario['id_comentario']; ?>">
                                    <input type="hidden" name="id_publicacion" value="<?php echo $publicacion['publicacion']['id_publicacion']; ?>">
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    </main>
    <script>
    function mostrarFormularioRespuesta(id, usuario, esRespuesta) {
        document.querySelectorAll('.respuesta-form').forEach(f => f.style.display = 'none');
        var form = document.getElementById('respuesta-form-' + id);
        form.style.display = 'block';
        var textarea = form.querySelector('textarea');
        var atInput = form.querySelector('input[name=at_usuario]');
        if (atInput) atInput.remove(); // Limpia campo anterior si existe
        if (esRespuesta) {
            textarea.value = '@' + usuario + ' ';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'at_usuario';
            input.value = usuario;
            form.appendChild(input);
            // Cambiar el parent_id al del comentario principal
            var parentInput = form.querySelector('input[name=parent_id]');
            if (parentInput) {
                var mainParent = form.closest('.comentario-box');
                if (mainParent) {
                    parentInput.value = mainParent.id.replace('comentario-', '');
                }
            }
        } else {
            textarea.value = '';
        }
    }
    function toggleRespuestas(id) {
        const contenedor = document.getElementById('respuestas-' + id);
        if (contenedor.style.display === 'none') {
            // Cargar respuestas por AJAX
            fetch('index.php?view=comentario&id_publicacion=<?php echo $publicacion['publicacion']['id_publicacion']; ?>&parent_id=' + id)
                .then(r => r.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    contenedor.style.display = 'block';
                });
        } else {
            contenedor.style.display = 'none';
        }
    }
    function eliminarComentario(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = window.location.href;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'eliminar_comentario';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
