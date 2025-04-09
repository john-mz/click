<?php
session_start();
require_once 'controller/publicacion.php';
$controller = new PublicacionController();


// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    
    if (!isset($_SESSION['id_usuario'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $titulo = $_POST['titulo'] ?? '';
            $contenido = $_POST['contenido'] ?? '';
            $id_usuario = $_SESSION['id_usuario'];

            if (empty($titulo) || empty($contenido)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
                exit;
            }

            $controller->crear($titulo, $contenido, $id_usuario);
            echo json_encode(['success' => true]);
            exit;

        case 'actualizar':
            $id_publicacion = $_POST['id_publicacion'] ?? '';
            $titulo = $_POST['titulo'] ?? '';
            $contenido = $_POST['contenido'] ?? '';
            $id_usuario = $_SESSION['id_usuario'];

            if (empty($id_publicacion) || empty($titulo) || empty($contenido)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
                exit;
            }

            $controller->actualizar($id_publicacion, $titulo, $contenido, $id_usuario);
            echo json_encode(['success' => true]);
            exit;

        case 'eliminar':
            $id_publicacion = $_POST['id_publicacion'] ?? '';
            if (empty($id_publicacion)) {
                echo json_encode(['success' => false, 'message' => 'ID de publicación no proporcionado']);
                exit;
            }
            $controller->eliminar($id_publicacion);
            echo json_encode(['success' => true]);
            exit;
    }
}

// Obtener publicaciones para mostrar
$publicaciones = $controller->index();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Publicaciones</h1>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearPublicacionModal">
            Crear Nueva Publicación
        </button>

        <div class="row">
            <?php while($publicacion = $publicaciones->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($publicacion['descripcion']); ?></h5>
                        <p class="card-text"><small class="text-muted">
                            Publicado por: <?php echo htmlspecialchars($publicacion['nombre']); ?><br>
                            Fecha: <?php echo $publicacion['fecha_creacion']; ?>
                        </small></p>
                        <button class="btn btn-warning btn-sm" onclick="editarPublicacion(<?php echo $publicacion['id_publicacion']; ?>)">
                            Editar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="eliminarPublicacion(<?php echo $publicacion['id_publicacion']; ?>)">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
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
                    <form id="crearPublicacionForm">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido</label>
                            <textarea class="form-control" id="contenido" name="contenido" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="crearPublicacion()">Crear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar publicación -->
    <div class="modal fade" id="editarPublicacionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editarPublicacionForm">
                        <input type="hidden" id="id_publicacion_editar" name="id_publicacion">
                        <div class="mb-3">
                            <label for="titulo_editar" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo_editar" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="contenido_editar" class="form-label">Contenido</label>
                            <textarea class="form-control" id="contenido_editar" name="contenido" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarPublicacion()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function crearPublicacion() {
            const form = document.getElementById('crearPublicacionForm');
            const formData = new FormData(form);
            formData.append('action', 'crear');

            fetch('publicacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al crear la publicación');
                }
            });
        }

        function editarPublicacion(id) {
            fetch(`publicacion.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('id_publicacion_editar').value = data.publicacion.id_publicacion;
                    document.getElementById('titulo_editar').value = data.publicacion.titulo;
                    document.getElementById('contenido_editar').value = data.publicacion.contenido;
                    new bootstrap.Modal(document.getElementById('editarPublicacionModal')).show();
                } else {
                    alert(data.message || 'Error al cargar la publicación');
                }
            });
        }

        function actualizarPublicacion() {
            const form = document.getElementById('editarPublicacionForm');
            const formData = new FormData(form);
            formData.append('action', 'actualizar');

            fetch('publicacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error al actualizar la publicación');
                }
            });
        }

        function eliminarPublicacion(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta publicación?')) {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id_publicacion', id);

                fetch('publicacion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al eliminar la publicación');
                    }
                });
            }
        }
    </script>
</body>
</html>
