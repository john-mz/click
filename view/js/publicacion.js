// Funciones para manejar los modales
function editarModal(id_publicacion, descripcion, imagen_url, usuario_id) {
    document.getElementById("id_publicacion_editar").value = id_publicacion;
    document.getElementById("descripcion_editar").value = descripcion;
    document.getElementById("imagen_actual").value = imagen_url;
    document.getElementById("usuario_id_editar").value = usuario_id;
}

// Modo claro/oscuro global para todo el proyecto
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}

function toggleTheme() {
    const current = localStorage.getItem('theme') || 'light';
    setTheme(current === 'light' ? 'dark' : 'light');
}

// Inicializar tema al cargar
(function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
})();

// Exportar para uso global
window.toggleTheme = toggleTheme;
