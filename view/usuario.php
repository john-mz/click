<?php
require_once 'controller/usuario.php';
require_once 'controller/rol.php';

$usuario = new Usuario();
$rol = new Rol();

$res = $usuario->consultar();
$roles = $rol->consultar();

// Verificar si hay mensajes de sesión
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';

// Limpiar mensajes de sesión
unset($_SESSION['mensaje']);
unset($_SESSION['tipo_mensaje']);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
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
        .table thead th {
            background: #222223;
            color: #fff;
        }
        .table, .modal-content {
             background: #232324;
             color: #fff;
         }
        .table-striped>tbody>tr:nth-of-type(odd),
        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #232324;
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
        .table, .table-striped, .table-striped>tbody>tr, .table-striped>tbody>tr>td, .table-striped>tbody>tr>th, .table thead th {
            background-color: #232324 !important;
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
        <div class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestión de Usuarios</h1>
                <a href="index.php?view=publicacion" class="btn btn-reddit">
                    <i class="bi bi-arrow-left"></i> Volver a Publicaciones
                </a>
            </div>
            <hr>
            
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Botón para agregar nuevo usuario -->
            <button type="button" class="btn btn-click mb-3" data-bs-toggle="modal" data-bs-target="#agregarUsuarioModal">
                <i class="bi bi-person-plus"></i> Agregar Usuario
            </button>

            <!-- Tabla de usuarios -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Fecha Registro</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res->num_rows > 0): ?>
                            <?php while ($row = $res->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['password']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fecha_registro']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nombre_rol']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-click-dark" 
                                                onclick="editarModal(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['password']); ?>', '<?php echo htmlspecialchars($row['fecha_registro']); ?>')"
                                                data-bs-toggle="modal" data-bs-target="#editarUsuarioModal">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-click" 
                                                onclick="eliminarModal(<?php echo $row['id_usuario']; ?>)"
                                                data-bs-toggle="modal" data-bs-target="#eliminarUsuarioModal">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay usuarios registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para agregar usuario -->
        <div class="modal fade" id="agregarUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="index.php?view=usuario" method="post">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="fecha_registro" class="form-label">Fecha Registro</label>
                                <input type="datetime-local" class="form-control" name="fecha_registro" required>
                            </div>
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" name="rol" required>
                                    <option value="1">Administrador</option>
                                    <option value="2">Invitado</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-click-dark" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="agregar" class="btn btn-click">Agregar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para editar usuario -->
        <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="index.php?view=usuario" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="id_usuario" id="inputIdUsuario">
                            <div class="mb-3">
                                <label for="inputNombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="inputNombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="inputEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="inputEmail" required>
                            </div>
                            <div class="mb-3">
                                <label for="inputPassword" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" id="inputPassword" required>
                            </div>
                            <div class="mb-3">
                                <label for="inputFechaRegistro" class="form-label">Fecha Registro</label>
                                <input type="datetime-local" class="form-control" name="fecha_registro" id="inputFechaRegistro" step="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" name="rol" required>
                                    <option value="1">Administrador</option>
                                    <option value="2">Invitado</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-click-dark" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="editar" class="btn btn-click">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para eliminar usuario -->
        <div class="modal fade" id="eliminarUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="index.php?view=usuario" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="id_usuario" id="inputIdUsuario1">
                            <p>¿Está seguro de que desea eliminar este usuario?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-click-dark" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="eliminar" class="btn btn-click">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/usuario.js"></script>
</body>
</html>