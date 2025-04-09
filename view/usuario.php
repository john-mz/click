<h1>usuario</h1>
<hr>
<?php
require_once 'controller/usuario.php';
require_once 'controller/rol.php';

$res = $usuario->consultar();
$roles = $rol->consultar();
// pendiente hacer lo del select de roles escalable i.e que lo saque directamente de sql
print_r($roles);
?>
<br>
<!-- agregar modal -->
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal12">
  Insertar
</button>
<?php

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
    $id_usuario = json_encode($row['id_usuario']);
    $nombre = json_encode($row['nombre']);
    $email = json_encode($row['email']);
    $password = json_encode($row['password']);
    $fecha = json_encode($row['fecha_registro']);
?>
    <button type='button' onclick='editarModal(<?php echo $id_usuario . ", $nombre, $email, $password, $fecha"; ?>)' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#exampleModal1'>Editar</button>
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

<!-- editar modal -->
<form action="" method="post">
  <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Datos</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <input type="hidden" class="form-control" name="id_usuario" id="inputIdUsuario" required="required">
          </div>
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="inputNombre" required="required">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="inputEmail" required="required">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="inputPassword" required="required">
          </div>
          <div class="mb-3">
            <label for="" class="form-label">Fecha Registro</label>
            <input type="datetime-local" class="form-control" name="fecha_registro" id="inputFechaRegistro">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Rol</label>
            <select class="form-select" aria-label="Default select example" name="rol" id="inputRolId" required="required">
              <option selected value="1">admin</option>
              <option value="2">Invitado</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" id="editar" name="editar" class="btn btn-primary">Editar</button>
        </div>
      </div>
    </div>
  </div>
</form>



<!-- agregar Modal -->
<form action="" method="post">
<div class="modal fade" id="exampleModal12" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
            <input type="hidden" class="form-control" name="id_usuario" id="" required="required">
          </div>
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="" required="required">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="" required="required">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="" required="required">
          </div>
          <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Rol</label>
            <select class="form-select" aria-label="Default select example" name="rol" id="" required="required">
              <option selected value="1">admin</option>
              <option value="2">Invitado</option>
            </select>
          </div>
        </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" name="agregar">Agregar</button>
      </div>
    </div>
  </div>
</div>
</form>

<script src="view/js/usuario.js"></script>