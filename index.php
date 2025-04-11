<?php
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
        require_once 'controller/publicacion.php';
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
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="mb-4">Bienvenido a Click Administration</h1>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($mensaje); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Selecciona tu usuario</h5>
                            <form action="index.php" method="post" class="mb-3">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="buscarUsuario" placeholder="Buscar usuario...">
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div class="list-group" id="listaUsuarios">
                                    <?php foreach($usuarios as $usuario): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-person-circle"></i>
                                                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                                                    <span class="badge bg-<?php echo $usuario['rol'] === 'admin' ? 'primary' : 'secondary'; ?> ms-2">
                                                        <?php echo htmlspecialchars($usuario['rol']); ?>
                                                    </span>
                                                </div>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_usuario']; ?>">
                                                    <input type="hidden" name="usuario_nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                                                    <input type="hidden" name="usuario_rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>">
                                                    <button type="submit" name="seleccionar_usuario" class="btn btn-primary">
                                                        Seleccionar
                                                    </button>
                                                </form>
                                            </div>
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const buscarInput = document.getElementById('buscarUsuario');
                const listaUsuarios = document.getElementById('listaUsuarios');
                const items = listaUsuarios.getElementsByClassName('usuario-item');

                buscarInput.addEventListener('input', function() {
                    const busqueda = this.value.toLowerCase();
                    
                    Array.from(items).forEach(item => {
                        const nombre = item.dataset.nombre.toLowerCase();
                        const id = item.dataset.id;
                        const texto = `${nombre} ${id}`;
                        
                        if (texto.includes(busqueda)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        </script>
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
        default:
            header('Location: index.php');
            exit;
    }
}
?>