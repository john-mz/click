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

        /* Mejoras visuales para el formulario y la tabla */
        .user-form-enhanced .input-group-text {
            border-radius: 8px 0 0 8px;
            border: none;
            font-size: 1.2rem;
        }
        .user-form-enhanced input, .user-form-enhanced select {
            border-radius: 0 8px 8px 0;
            border: none;
            background: #202b38;
            color: #fff;
        }
        .user-form-enhanced input:focus, .user-form-enhanced select:focus {
            box-shadow: 0 0 0 2px #1DA1F2;
            background: #232b36;
            color: #fff;
        }
        .user-form-enhanced .form-control, .user-form-enhanced .form-select {
            min-height: 44px;
            font-size: 1rem;
        }
        .user-form-enhanced .btn-primary {
            font-size: 1.1rem;
            padding: 0.7em 2.2em;
            border-radius: 10px;
            background: linear-gradient(90deg, #1DA1F2 60%, #0a8cd8 100%);
            border: none;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
        }
        .user-form-enhanced .btn-primary:hover {
            background: #1a91da;
            transform: translateY(-2px);
        }
        .user-table-container {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem 1rem;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
            overflow: hidden;
        }
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
        }
        .user-table thead tr {
            background: linear-gradient(90deg, #1DA1F2 0%, #192734 100%);
        }
        .user-table th {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 1rem 0.75rem;
            border: none;
            letter-spacing: 0.5px;
            background: transparent;
        }
        .user-table tbody {
            background: var(--card-bg);
        }
        .user-table td {
            color: #fff;
            font-size: 1rem;
            padding: 0.85rem 0.75rem;
            border: none;
            vertical-align: middle;
            background: transparent;
        }
        .user-table tbody tr:nth-child(even) {
            background: #1a202c;
        }
        .user-table tbody tr:nth-child(odd) {
            background: #232b36;
        }
        .badge-admin {
            background: #1da1f2;
            color: #fff;
            border-radius: 8px;
            padding: 0.4em 1em;
            font-size: 0.95em;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
        }
        .badge-invitado {
            background: #8899A6;
            color: #fff;
            border-radius: 8px;
            padding: 0.4em 1em;
            font-size: 0.95em;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
        }
        .user-table .btn-action.btn-primary {
            background: #ecc94b;
            color: #232b36;
            border: none;
        }
        .user-table .btn-action.btn-danger {
            background: #e53e3e;
            color: #fff;
            border: none;
        }
        .user-table .btn-action.btn-primary:hover {
            background: #ffd700;
            color: #232b36;
        }
        .user-table .btn-action.btn-danger:hover {
            background: #b81b4b;
            color: #fff;
        }
        @media (max-width: 768px) {
            .user-form-enhanced .input-group, .user-form-enhanced .col-md-3 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            .user-table-container {
                padding: 0.5rem 0.2rem;
            }
            .user-table th, .user-table td {
                font-size: 0.95rem;
                padding: 0.5rem 0.3rem;
            }
        }
        .user-form-enhanced input::placeholder {
            color: #fff;
            opacity: 1;
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

            <!-- Formulario de creación de usuarios -->
            <div class="user-card mb-4">
                <h3 class="user-select-title mb-4">Crear Nuevo Usuario</h3>
                <form id="createForm" class="row g-3 user-form-enhanced">
                    <div class="col-md-3 input-group mb-2">
                        <span class="input-group-text bg-dark text-info"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" required>
                    </div>
                    <div class="col-md-3 input-group mb-2">
                        <span class="input-group-text bg-dark text-info"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="col-md-3 input-group mb-2">
                        <span class="input-group-text bg-dark text-info"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="1">Administrador</option>
                            <option value="2">Usuario</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn-action btn-primary btn-lg px-4">
                            <i class="bi bi-person-plus"></i> Agregar Usuario
                        </button>
                    </div>
                    <div id="formMessage" class="mt-2"></div>
                </form>
            </div>

            <!-- Tabla de usuarios mejorada -->
            <div class="user-table-container table-responsive">
                <h3 class="user-select-title mb-3" style="font-size:1.4rem;">Lista de Usuarios en Tiempo Real</h3>
                <table class="user-table table-modern table align-middle mb-0">
                    <thead style="position:sticky;top:0;z-index:1;">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Contraseña</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí se cargan los usuarios dinámicamente con JS -->
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
                    <form id="editUserForm">
                        <div class="modal-body">
                            <input type="hidden" name="id_usuario" id="edit_id_usuario">
                            <div class="mb-3">
                                <label for="edit_nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_password" class="form-label">Contraseña</label>
                                <input type="text" class="form-control" id="edit_password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_fecha_registro" class="form-label">Fecha de Registro</label>
                                <input type="text" class="form-control" id="edit_fecha_registro" name="fecha_registro" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_rol" class="form-label">Rol</label>
                                <select class="form-select" id="edit_rol" name="rol_id" required>
                                    <option value="1">Administrador</option>
                                    <option value="2">Usuario</option>
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
                    <form id="deleteUserForm">
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

    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const socket = io("http://localhost:3000");

        function editarUsuario(usuario) {
            console.log('Editar usuario:', usuario);
            document.getElementById('edit_id_usuario').value = usuario.id_usuario;
            document.getElementById('edit_nombre').value = usuario.nombre;
            document.getElementById('edit_email').value = usuario.email;
            document.getElementById('edit_password').value = usuario.password;
            document.getElementById('edit_fecha_registro').value = usuario.fecha_registro;
            document.getElementById('edit_rol').value = usuario.rol_id || usuario.rol;
            new bootstrap.Modal(document.getElementById('editarUsuarioModal')).show();
        }

        function eliminarUsuario(id) {
            document.getElementById('delete_id_usuario').value = id;
            new bootstrap.Modal(document.getElementById('eliminarUsuarioModal')).show();
        }

        // Escuchar eventos de Socket.IO
        socket.on("user_created", (data) => {
            const userTableBody = document.querySelector(".user-table tbody");
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${data.id_usuario}</td>
                <td>${data.nombre}</td>
                <td>${data.email}</td>
                <td>${data.password}</td>
                <td>
                    ${data.rol === 'admin' ? 
                        '<span class="badge-admin">admin</span>' : 
                        '<span class="badge-invitado">invitado</span>'}
                </td>
                <td>
                    <button type="button" class="btn-action btn-primary me-2" 
                            onclick="editarUsuario(${JSON.stringify(data)})">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <button type="button" class="btn-action btn-danger" 
                            onclick="eliminarUsuario(${data.id_usuario})">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </td>
            `;
            userTableBody.prepend(tr);
        });

        socket.on("user_deleted", (data) => {
            const userTableBody = document.querySelector(".user-table tbody");
            const rows = userTableBody.querySelectorAll("tr");
            rows.forEach(row => {
                const id = row.querySelector("td").innerText;
                if (id == data.id_usuario) {
                    userTableBody.removeChild(row);
                }
            });
        });

        socket.on("user_updated", (data) => {
            const userTableBody = document.querySelector(".user-table tbody");
            const rows = userTableBody.querySelectorAll("tr");
            rows.forEach(row => {
                const id = row.querySelector("td").innerText;
                if (id == data.id_usuario) {
                    row.innerHTML = `
                        <td>${data.id_usuario}</td>
                        <td>${data.nombre}</td>
                        <td>${data.email}</td>
                        <td>${data.password}</td>
                        <td>
                            ${data.rol === 'admin' ? 
                                '<span class="badge-admin">admin</span>' : 
                                '<span class="badge-invitado">invitado</span>'}
                        </td>
                        <td>
                            <button type="button" class="btn-action btn-primary me-2" 
                                    onclick="editarUsuario(${JSON.stringify(data)})">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <button type="button" class="btn-action btn-danger" 
                                    onclick="eliminarUsuario(${data.id_usuario})">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </td>
                    `;
                }
            });
        });

        // Manejar el envío del formulario de creación
        document.getElementById("createForm").addEventListener("submit", e => {
            e.preventDefault();
            const name = document.getElementById("name").value;
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            const rol = document.getElementById("rol").value;

            const formData = new FormData();
            formData.append("name", name);
            formData.append("email", email);
            formData.append("password", password);
            formData.append("rol", rol);

            fetch("/proyectos/click/php-api/create_user.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta del servidor:', data);
                if (data.success) {
                    // Limpiar el formulario
                    document.getElementById("name").value = "";
                    document.getElementById("email").value = "";
                    document.getElementById("password").value = "";
                    document.getElementById("rol").value = "1";
                    
                    // Recargar la tabla manualmente si el socket no funciona
                    loadUsers();
                } else {
                    alert('Error al crear el usuario: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear el usuario. Por favor, intente nuevamente.');
            });
        });

        // Función para cargar usuarios
        function loadUsers() {
            fetch("/proyectos/click/php-api/get_users.php")
                .then(res => res.json())
                .then(users => {
                    console.log('Usuarios cargados:', users);
                    const userTableBody = document.querySelector(".user-table tbody");
                    userTableBody.innerHTML = "";
                    users.forEach(user => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${user.id_usuario}</td>
                            <td>${user.nombre}</td>
                            <td>${user.email}</td>
                            <td>${user.password}</td>
                            <td>
                                ${(user.rol === 'admin' || user.rol_id === '1') ? 
                                    '<span class="badge-admin">admin</span>' : 
                                    '<span class="badge-invitado">invitado</span>'}
                            </td>
                            <td>
                                <button type="button" class="btn-action btn-primary me-2" 
                                    onclick='editarUsuario({
                                        id_usuario: "${user.id_usuario}",
                                        nombre: "${user.nombre}",
                                        email: "${user.email}",
                                        password: "${user.password}",
                                        fecha_registro: "${user.fecha_registro}",
                                        rol_id: "${user.rol_id}"
                                    })'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button type="button" class="btn-action btn-danger" 
                                    onclick="eliminarUsuario(${user.id_usuario})">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </td>
                        `;
                        userTableBody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar usuarios:', error);
                });
        }

        // Cargar usuarios al iniciar la página
        loadUsers();

        // Verificar conexión con Socket.IO
        socket.on('connect', () => {
            console.log('Conectado al servidor Socket.IO');
        });

        socket.on('connect_error', (error) => {
            console.error('Error de conexión con Socket.IO:', error);
        });

        // Envío AJAX para editar usuario
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/proyectos/click/php-api/edit_user.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log('Respuesta editar:', data);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal')).hide();
                    loadUsers();
                } else {
                    alert('Error al editar usuario');
                }
            });
        });
        // Envío AJAX para eliminar usuario
        document.getElementById('deleteUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('/proyectos/click/php-api/delete_user.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('eliminarUsuarioModal')).hide();
                    loadUsers();
                } else {
                    alert('Error al eliminar usuario');
                }
            });
        });
    </script>
</body>
</html>