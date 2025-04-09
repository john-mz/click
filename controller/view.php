<?php 
switch (isset($_GET['view'])) {
    case $_GET['view'] == 'publicacion':
        require_once 'view/publicacion.php';
        break;
    case $_GET['view'] == 'usuario':
        require_once 'view/usuario.php';
        break;
}
?>