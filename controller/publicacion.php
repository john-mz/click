<?php
require_once 'model/publicacion.php';

class PublicacionController {
    private $publicacion;

    function __construct() {
        $this->publicacion = new Publicacion();
    }

    function index() {
        $publicaciones = $this->publicacion->consultar();
        return $publicaciones;
    }

    function crear($titulo, $contenido, $id_usuario) {
        $fecha_publicacion = date('Y-m-d H:i:s');
        $this->publicacion->insertar($titulo, $contenido, $fecha_publicacion, $id_usuario);
    }

    function actualizar($id_publicacion, $titulo, $contenido, $id_usuario) {
        $fecha_publicacion = date('Y-m-d H:i:s');
        $this->publicacion->editar($id_publicacion, $titulo, $contenido, $fecha_publicacion, $id_usuario);
    }

    function eliminar($id_publicacion) {
        $this->publicacion->eliminar($id_publicacion);
    }
}
