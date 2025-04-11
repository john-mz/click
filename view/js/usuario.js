// email, password, fecha_registro, rol_id
function editarModal(id_usuario, nombre, email, password, fecha_registro){
    document.getElementById("inputIdUsuario").value = id_usuario;
    document.getElementById("inputNombre").value = nombre;
    document.getElementById("inputEmail").value = email;
    document.getElementById("inputPassword").value = password;
    document.getElementById("inputFechaRegistro").value = fecha_registro;
}

function eliminarModal(id_usuario){
    document.getElementById("inputIdUsuario1").value = id_usuario;
}