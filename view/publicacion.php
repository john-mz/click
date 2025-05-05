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
</head>
<body>

    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Click RedSocial</a>
            <div class="d-flex align-items-center">
                <!-- Botón para volver a selección de usuarios -->
                <a href="index.php" class="btn btn-outline-light me-3">
                    <i class="bi bi-arrow-left"></i> Volver a Usuarios
                </a>
                
                <?php if ($_SESSION['usuario_actual']['rol'] === 'admin'): ?>
                    <a href="index.php?view=usuario" class="btn btn-outline-light me-3">
                        <i class="bi bi-people"></i> Gestionar Usuarios
                    </a>
                <?php endif; ?>
                
                <!-- Información del usuario seleccionado -->
                <div class="text-white">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-person-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['usuario_actual']['nombre']); ?></span>
                        </div>
                        <div class="me-3">
                            <i class="bi bi-person-badge"></i>
                            <span><?php echo htmlspecialchars($_SESSION['usuario_actual']['rol']); ?></span>
                        </div>
                        <div>
                            <i class="bi bi-hash"></i>
                            <span><?php echo $_SESSION['usuario_actual']['id']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    

    <div class="container mt-5">
        <!-- Botones de filtrado -->
        <div class="mb-3">
            <button class="btn btn-primary me-2" id="verMisPublicaciones">
                <i class="bi bi-person-lines-fill"></i> Ver mis publicaciones
            </button>
            <button class="btn btn-secondary" id="verTodasPublicaciones">
                <i class="bi bi-people-fill"></i> Ver todas las publicaciones
            </button>
        </div>

        <h1>Publicaciones</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearPublicacionModal" id="btnCrearPublicacion">
            Crear Nueva Publicación
        </button>
        <button class="btn btn-warning mb-3">
            Ver Tendencias 🔥
        </button>

        <div class="row" id="contenedorPublicaciones">
            <?php if (empty($publicaciones)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No hay publicaciones disponibles.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($publicaciones as $publicacion): ?>
                <div class="col-md-4 mb-4 publicacion" data-usuario-id="<?php echo $publicacion['usuario_id']; ?>">
                    <div class="card">
                        <?php if (!empty($publicacion['imagen_url'])): ?>
                            <img src="<?php echo htmlspecialchars($publicacion['imagen_url']); ?>" class="card-img-top" alt="Imagen de la publicación">
                        <?php endif; ?>
                        <div class="card-body">
                            <p class="card-text"><?php echo htmlspecialchars($publicacion['descripcion']); ?></p>
                            <p class="card-text"><small class="text-muted">
                                Publicado por: <?php echo htmlspecialchars($publicacion['nombre_usuario']); ?><br>
                                Fecha: <?php echo $publicacion['fecha_creacion']; ?>
                            </small></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm btn-like" 
                                            data-publicacion-id="<?php echo $publicacion['id_publicacion']; ?>"
                                            onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'like')">
                                        <i class="bi bi-hand-thumbs-up"></i>
                                        <span class="contador-likes"><?php echo $publicacion['meGusta'] ?? 0; ?></span>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm btn-dislike" 
                                            data-publicacion-id="<?php echo $publicacion['id_publicacion']; ?>"
                                            onclick="reaccionar(<?php echo $publicacion['id_publicacion']; ?>, 'dislike')">
                                        <i class="bi bi-hand-thumbs-down"></i>
                                        <span class="contador-dislikes"><?php echo $publicacion['noMeGusta'] ?? 0; ?></span>
                                    </button>
                                </div>
                                <?php if ($_SESSION['usuario_actual']['rol'] === 'admin' || $_SESSION['usuario_actual']['id'] == $publicacion['usuario_id']): ?>
                                    <button class="btn btn-danger btn-sm btn-eliminar" onclick="eliminarModal(<?php echo $publicacion['id_publicacion']; ?>)">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                <?php endif; ?>
                            </div>
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
<?php 
if ($controller->consultarTendencias()) {
    $row = $controller->consultarTendencias()->fetch_row();
    $jsonString = $row[0];

    $publicaciones = json_decode($jsonString);
    print_r($publicaciones);
}?>
</body>
</html>
