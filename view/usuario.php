<h1>usuario</h1>
<hr>
<?php 
require_once 'controller/usuario.php';
require_once 'controller/rol.php';

$res = $usuario->consultar();
$roles = $rol->consultar();
// pendiente hacer lo del select de roles escalable i.e que lo saque directamente de sql
print_r($roles);

if ($res->num_rows > 0) {
    echo "<table border='1'>"; // Start the table with a border for visibility
        echo "<tr>";
            echo "<th>ID Usuario</th>";
            echo "<th>Nombre</th>";
            echo "<th>Email</th>";
            echo "<th>Password</th>";
            echo "<th>Fecha Registro</th>";
            echo "<th>Rol ID</th>";
            echo "<th>Acciones</th>";
        echo "</tr>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
            echo "<td>" . $row['id_usuario'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['password'] . "</td>";
            echo "<td>" . $row['fecha_registro'] . "</td>";
            echo "<td>" . $row['nombre_rol'] . "</td>";
            echo "<td>";
            $nombre = json_encode($row['nombre']);
            $email = json_encode($row['email']);
            $password = json_encode($row['password']);
            $fecha = json_encode($row['fecha_registro']);
            $nombre_rol = json_encode($row['nombre_rol']);
            ?>
            <button type='button' onclick='editarModal(<?php echo "$nombre, $email, $password, $fecha, $nombre_rol"; ?>)' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#exampleModal1'>Editar</button>
            <?php
                // echo "<a href='index.php?view=usuario&&accion=agregar'>Agregar | </a>";
                // echo "<a href='index.php?view=usuario&&accion=editar&&id_usuario=" . $row['id_usuario'] . "'>Editar | </a>";
                // echo "<a href='index.php?view=usuario&&accion=eliminar&&id_usuario=" . $row['id_usuario'] . "'>Eliminar</a>";
            echo "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No results found";
}
?>

<!-- Modal Editar-->
<form action="" method="post">
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="inputNombre">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Email</label>
                <input type="email" class="form-control" id="inputEmail">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="inputPassword">
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Fecha Registro</label>
                <input type="text" class="form-control" id="inputFechaRegistro">
            </div>
            <div class="mb-3">
              <label for="exampleInputPassword1" class="form-label">Rol</label>
              <select class="form-select" aria-label="Default select example" id="inputRolId">
                <option selected>Open this select menu</option>
                <option value="1">admin</option>
                <option value="2">Invitado</option>
              </select>
            </div>
        </form>
      </div>
        
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="editar" class="btn btn-primary">Editar</button>
      </div>
    </div>
  </div>
</div>
</form>

<script src="view/js/usuario.js"></script>