// email, password, fecha_registro, rol_id
function editarModal(nombre, email, password, fecha_registro, nombreRol){
    document.getElementById("inputNombre").value = nombre;
    document.getElementById("inputEmail").value = email;
    document.getElementById("inputPassword").value = password;
    document.getElementById("inputFechaRegistro").value = fecha_registro;
    // document.getElementById("inputNombreRol").value = nombreRol;
}