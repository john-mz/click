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
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
            border-radius: 50%;
            background: var(--primary-color);
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
        .main-content {
            max-width: 700px;
            margin: 0 auto;
            padding: calc(var(--header-height) + 2rem) 1rem 2rem;
        }
        .comentario-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .comentario-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        .comentario-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }
        .comentario-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.3rem;
            overflow: hidden;
        }
        .comentario-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
        .comentario-info {
            display: flex;
            flex-direction: column;
        }
        .comentario-autor {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1rem;
        }
        .comentario-tiempo {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .comentario-contenido {
            font-size: 1.1rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        .comentario-acciones {
            display: flex;
            gap: 1rem;
        }
        .comentario-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1em;
            border-radius: 20px;
            padding: 6px 18px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .comentario-btn:hover {
            background: var(--hover-color);
            color: var(--primary-color);
        }
        .respuesta-form {
            margin-top: 1rem;
            background: var(--background-color);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border-color);
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
        .alert {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 700px) {
            .main-content {
                padding: calc(var(--header-height) + 1rem) 0.5rem 1rem;
            }
        }
        .app-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none !important;
        }
        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1;
        }
        .header-logo a.app-logo:visited,
        .header-logo a.app-logo:active {
            color: var(--primary-color);
            text-decoration: none !important;
        }
        .comentario-card.respuesta {
            border: none !important;
            box-shadow: none !important;
            margin-left: 2rem;
        }
        .at-mention {
            color: var(--primary-color);
            font-weight: bold;
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
                    <input type="hidden" name="view" value="comentario">
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
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Comentarios</h1>
                <a href="index.php?view=publicacion" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <div class="comentario-card mb-4">
                <?php if ($publicacion['success'] ?? false): ?>
                    <div class="comentario-header">
                        <div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>
                        <div class="comentario-info">
                            <span class="comentario-autor"><?php echo htmlspecialchars($publicacion['publicacion']['nombre_usuario']); ?></span>
                            <span class="comentario-tiempo"><?php echo $publicacion['publicacion']['fecha_creacion']; ?></span>
                        </div>
                    </div>
                    <div class="comentario-contenido">
                        <?php echo htmlspecialchars($publicacion['publicacion']['descripcion']); ?>
                    </div>
                    <?php if (!empty($publicacion['publicacion']['imagen_url'])): ?>
                        <img src="<?php echo htmlspecialchars($publicacion['publicacion']['imagen_url']); ?>" class="img-fluid mb-2" style="max-width:200px; border-radius:12px;">
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger">No se encontró la publicación.</div>
                <?php endif; ?>
            </div>

            <?php if ($publicacion['success'] ?? false): ?>
            <!-- Formulario para nuevo comentario -->
            <form method="post" action="index.php?view=comentario&id_publicacion=<?php echo $publicacion['publicacion']['id_publicacion']; ?>" class="mb-4">
                <div class="comentario-card p-3 d-flex align-items-start gap-3">
                    <div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>
                    <textarea class="form-control me-2" name="comentario" rows="2" placeholder="Añade un comentario..." required></textarea>
                    <button type="submit" class="btn btn-primary">Comentar</button>
                    <input type="hidden" name="parent_id" value="">
                    <input type="hidden" name="id_publicacion" value="<?php echo $publicacion['publicacion']['id_publicacion']; ?>">
                </div>
            </form>

            <!-- Listado de comentarios -->
            <div id="comentarios-lista">
                <?php if (empty($comentarios)): ?>
                    <div class="alert alert-info">No hay comentarios aún. ¡Sé el primero en comentar!</div>
                <?php else: ?>
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="comentario-card" id="comentario-<?php echo $comentario['id_comentario']; ?>">
                            <div class="comentario-header">
                                <div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>
                                <div class="comentario-info">
                                    <span class="comentario-autor"><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></span>
                                    <span class="comentario-tiempo"><?php echo $comentario['fecha_comentario']; ?></span>
                                </div>
                            </div>
                            <div class="comentario-contenido"><?php echo mostrarAt($comentario['comentario'], $usuario_actual['nombre']); ?></div>
                            <div class="comentario-acciones">
                                <button class="comentario-btn" onclick="mostrarFormularioRespuesta(<?php echo $comentario['id_comentario']; ?>, '<?php echo htmlspecialchars($comentario['nombre_usuario']); ?>', false)"><i class="bi bi-reply"></i> Responder</button>
                                <button class="comentario-btn" onclick="toggleRespuestas(<?php echo $comentario['id_comentario']; ?>)"><i class="bi bi-chat-dots"></i> Ver respuestas</button>
                                <?php if (puedeEliminarComentario($comentario, $usuario_actual, $publicacion)): ?>
                                    <button class="comentario-btn text-danger" onclick="eliminarComentario(<?php echo $comentario['id_comentario']; ?>)"><i class="bi bi-trash"></i> Eliminar</button>
                                <?php endif; ?>
                            </div>
                            <div class="respuestas mt-2" id="respuestas-<?php echo $comentario['id_comentario']; ?>" style="display:none;"></div>
                            <form method="post" action="index.php?view=comentario&id_publicacion=<?php echo $publicacion['publicacion']['id_publicacion']; ?>" class="mt-2 respuesta-form" id="respuesta-form-<?php echo $comentario['id_comentario']; ?>" style="display:none;">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="comentario-avatar"><i class="bi bi-person-fill"></i></div>
                                    <textarea class="form-control me-2" name="comentario" rows="2" placeholder="Responder a <?php echo htmlspecialchars($comentario['nombre_usuario']); ?>..." required></textarea>
                                    <button type="submit" class="btn btn-primary">Responder</button>
                                </div>
                                <input type="hidden" name="parent_id" value="<?php echo $comentario['id_comentario']; ?>">
                                <input type="hidden" name="id_publicacion" value="<?php echo $publicacion['publicacion']['id_publicacion']; ?>">
                            </form>
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
                var mainParent = form.closest('.comentario-card');
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
