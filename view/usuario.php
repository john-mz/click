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
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestión de Usuarios</h1>
            <a href="index.php?view=publicacion" class="btn btn-primary">
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
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#agregarUsuarioModal">
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
                                    <button type="button" class="btn btn-warning btn-sm" 
                                            onclick="editarModal(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['password']); ?>', '<?php echo htmlspecialchars($row['fecha_registro']); ?>')"
                                            data-bs-toggle="modal" data-bs-target="#editarUsuarioModal">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="agregar" class="btn btn-primary">Agregar</button>
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
                            <input type="datetime-local" class="form-control" name="fecha_registro" id="inputFechaRegistro" required>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar" class="btn btn-primary">Guardar</button>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="eliminar" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="view/js/usuario.js"></script>
</body>
</html>