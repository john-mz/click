<?php
require_once 'controller/usuario.php';
require_once 'controller/rol.php';

$usuarioController = new UsuarioController();
$usuarios = $usuarioController->obtenerUsuarios();
$roles = ['usuario', 'admin'];

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
    <title>Click - Gestión de Usuarios</title>
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
            display: flex;
            align-items: center;
            width: 100%;
        }
        .header-logo {
            flex: 0 0 auto;
        }
        .header-user {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-left: auto;
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
        @media (max-width: 768px) {
            .header-user {
                gap: 0.3rem;
            }
            .header-username {
                font-size: 1rem;
            }
            .header-avatar {
                width: 32px;
                height: 32px;
                font-size: 1.1rem;
            }
        }

        .app-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .main-content {
            padding-top: calc(var(--header-height) + 2rem);
            padding: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .user-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
        }

        .user-select-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
        }
        .table-modern thead tr {
            background: linear-gradient(90deg, #1DA1F2 0%, #192734 100%);
        }
        .table-modern th {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 1rem 0.75rem;
            border: none;
            letter-spacing: 0.5px;
        }
        .table-modern tbody tr {
            transition: background 0.2s;
            border-bottom: 1px solid var(--border-color);
        }
        .table-modern tbody tr:last-child {
            border-bottom: none;
        }
        .table-modern tbody tr:hover {
            background: var(--hover-color);
        }
        .table-modern td {
            color: var(--text-primary);
            font-size: 1rem;
            padding: 0.85rem 0.75rem;
            border: none;
            vertical-align: middle;
        }
        .badge-admin {
            background: linear-gradient(90deg, #1DA1F2 60%, #0a8cd8 100%);
            color: #fff;
            border-radius: 8px;
            padding: 0.4em 1em;
            font-size: 0.95em;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .badge-invitado {
            background: #8899A6;
            color: #fff;
            border-radius: 8px;
            padding: 0.4em 1em;
            font-size: 0.95em;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-action {
            border-radius: 30px;
            font-weight: 600;
            padding: 0.5em 1.2em;
            font-size: 1em;
            margin-right: 0.5em;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-action:last-child {
            margin-right: 0;
        }
        .btn-action.btn-primary {
            background: var(--primary-color);
            border: none;
            color: #fff;
        }
        .btn-action.btn-primary:hover {
            background: #1a91da;
            transform: translateY(-2px);
        }
        .btn-action.btn-danger {
            background: #e0245e;
            border: none;
            color: #fff;
        }
        .btn-action.btn-danger:hover {
            background: #b81b4b;
            transform: translateY(-2px);
        }
        .volver-btn-container {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 2rem;
        }
        .volver-btn {
            background: linear-gradient(90deg, #1DA1F2 60%, #0a8cd8 100%);
            color: #fff;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            padding: 0.7em 2em;
            font-size: 1.1em;
            box-shadow: 0 2px 8px rgba(29,161,242,0.10);
            transition: background 0.2s, transform 0.2s;
        }
        .volver-btn:hover {
            background: #1a91da;
            color: #fff;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .user-card {
                padding: 1.5rem;
            }
            .volver-btn-container {
                justify-content: center;
            }
            .volver-btn {
                width: 100%;
                margin-bottom: 1.5rem;
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

    <main class="main-content">
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="user-card">
            <div class="volver-btn-container" style="margin-bottom: 1.5rem;">
                <a href="index.php?view=publicacion" class="volver-btn"><i class="bi bi-arrow-left"></i> Volver a Publicaciones</a>
            </div>
            <h2 class="user-select-title mb-4">Gestión de Usuarios</h2>
            <?php if ($_SESSION['usuario_actual']['rol'] === 'admin'): ?>
                <form method="post" action="index.php" style="display:inline-block; margin-bottom: 1.5rem;">
                    <button type="submit" name="eliminar_todos_usuarios" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar TODOS los usuarios?');">
                        <i class="bi bi-trash"></i> Eliminar todos los usuarios
                    </button>
                </form>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td>
                                    <?php if ($usuario['rol'] === 'admin'): ?>
                                        <span class="badge-admin">admin</span>
                                    <?php else: ?>
                                        <span class="badge-invitado">invitado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn-action btn-primary me-2" 
                                            onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn-action btn-danger" 
                                            onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para editar usuario -->
        <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Editar Usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" action="index.php?view=usuario&action=update">
                        <div class="modal-body">
                            <input type="hidden" name="id_usuario" id="edit_id_usuario">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_rol" class="form-label">Rol</label>
                                <select class="form-select" id="edit_rol" name="rol" required>
                                    <option value="usuario">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para eliminar usuario -->
        <div class="modal fade" id="eliminarUsuarioModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Eliminar Usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" action="index.php?view=usuario&action=delete">
                        <div class="modal-body">
                            <input type="hidden" name="id_usuario" id="delete_id_usuario">
                            <p>¿Estás seguro de que deseas eliminar este usuario?</p>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarUsuario(usuario) {
            document.getElementById('edit_id_usuario').value = usuario.id_usuario;
            document.getElementById('edit_nombre').value = usuario.nombre;
            document.getElementById('edit_rol').value = usuario.rol;
            new bootstrap.Modal(document.getElementById('editarUsuarioModal')).show();
        }

        function eliminarUsuario(id) {
            document.getElementById('delete_id_usuario').value = id;
            new bootstrap.Modal(document.getElementById('eliminarUsuarioModal')).show();
        }
    </script>
</body>
</html>