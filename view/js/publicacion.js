// Funciones para manejar los modales
function editarModal(id_publicacion, descripcion, imagen_url, usuario_id) {
    document.getElementById("id_publicacion_editar").value = id_publicacion;
    document.getElementById("descripcion_editar").value = descripcion;
    document.getElementById("imagen_actual").value = imagen_url;
    document.getElementById("usuario_id_editar").value = usuario_id;
}

function eliminarModal(id_publicacion) {
    // Validar que el ID existe
    if (!id_publicacion) {
        console.error("Error: ID de publicación no proporcionado");
        return;
    }
    
    // Obtener el elemento del formulario
    const formEliminar = document.getElementById("formEliminar");
    if (!formEliminar) {
        console.error("Error: No se encontró el formulario de eliminación");
        return;
    }
    
    // Obtener el campo oculto del ID
    const inputId = document.getElementById("id_publicacion_eliminar");
    if (!inputId) {
        console.error("Error: No se encontró el campo id_publicacion_eliminar");
        return;
    }
    
    // Asignar el ID al campo oculto
    inputId.value = id_publicacion;
    console.log("ID a eliminar:", id_publicacion);
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('eliminarPublicacionModal'));
    modal.show();
    
    // Agregar evento al formulario para prevenir envío duplicado
    formEliminar.onsubmit = function(e) {
        e.preventDefault();
        if (confirm("¿Estás seguro de que deseas eliminar esta publicación?")) {
            this.submit();
        }
    };
}
