<?php
// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
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

        if (!empty($descripcion) && !empty($imagen_url) && !empty($usuario_id)) {
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
</head>
<body>
    <div class="container mt-5">
        <h1>Publicaciones</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearPublicacionModal">
            Crear Nueva Publicación
        </button>

        <div class="row">
            <?php if (empty($publicaciones)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No hay publicaciones disponibles.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($publicaciones as $publicacion): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($publicacion['imagen_url']); ?>" class="card-img-top" alt="Imagen de la publicación">
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($publicacion['descripcion']); ?></p>
                            <p class="card-text"><small class="text-muted">
                                Publicado por: <?php echo htmlspecialchars($publicacion['nombre_usuario']); ?><br>
                                Fecha: <?php echo $publicacion['fecha_creacion']; ?>
                            </small></p>
                            <button class="btn btn-danger btn-sm" onclick="eliminarModal(<?php echo $publicacion['id_publicacion']; ?>)">
                                Eliminar
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
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen</label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger" name="eliminar">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función temporal para mostrar información de depuración
        function eliminarModal(id) {
            console.log("=== Información de Depuración ===");
            console.log("ID de la publicación a eliminar:", id);
            console.log("Tipo de ID:", typeof id);
            console.log("Valor del campo oculto:", document.getElementById("id_publicacion_eliminar")?.value);
            console.log("=== Fin de Información ===");
            
            // Asignar el ID al campo oculto
            const inputId = document.getElementById("id_publicacion_eliminar");
            if (inputId) {
                inputId.value = id;
                console.log("ID asignado al campo oculto:", inputId.value);
            }
            
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('eliminarPublicacionModal'));
            modal.show();
        }
    </script>
    <script src="../view/js/publicacion.js"></script>
</body>
</html>
