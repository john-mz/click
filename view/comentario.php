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
    <style>
        .comentario-box { margin-bottom: 1.5rem; }
        .respuesta-box { margin-left: 3rem; margin-top: 0.5rem; }
        .btn-responder { font-size: 0.9em; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: #eee; display: inline-block; text-align: center; line-height: 40px; font-size: 1.5em; color: #888; margin-right: 10px; }
        .btn-ver-respuestas { color: #0d6efd; background: none; border: none; padding: 0; }
        .at-mention { color: #00bfff; font-weight: bold; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">COMENTARIOS</h1>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Publicación</h5>
            <?php if ($publicacion['success'] ?? false): ?>
                <p><strong><?php echo htmlspecialchars($publicacion['publicacion']['descripcion']); ?></strong></p>
                <?php if (!empty($publicacion['publicacion']['imagen_url'])): ?>
                    <img src="<?php echo htmlspecialchars($publicacion['publicacion']['imagen_url']); ?>" class="img-fluid mb-2" style="max-width:200px;">
                <?php endif; ?>
                <p class="mb-0"><small class="text-muted">Por: <?php echo htmlspecialchars($publicacion['publicacion']['nombre_usuario']); ?> | Fecha: <?php echo $publicacion['publicacion']['fecha_creacion']; ?></small></p>
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
                                <!-- El campo at_usuario solo se llenará por JS si es respuesta a una respuesta -->
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <a href="index.php?view=publicacion" class="btn btn-outline-secondary mt-4"><i class="bi bi-arrow-left"></i> Volver a publicaciones</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
