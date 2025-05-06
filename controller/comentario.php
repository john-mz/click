<?php
require_once 'model/comentario.php';

class ComentarioController {
    private $comentario;
    public function __construct() {
        $this->comentario = new Comentario();
    }

    public function obtenerComentarios($publicacion_id) {
        return $this->comentario->obtenerPorPublicacion($publicacion_id);
    }

    public function obtenerRespuestas($comentario_id) {
        return $this->comentario->obtenerRespuestas($comentario_id);
    }

    public function agregarComentario($comentario, $usuario_id, $publicacion_id, $parent_id = null) {
        return $this->comentario->insertar($comentario, $usuario_id, $publicacion_id, $parent_id);
    }

    public function eliminarComentario($id_comentario) {
        return $this->comentario->eliminarComentario($id_comentario);
    }
}
