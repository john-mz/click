<?php
class Rol {
    public $id_rol, $nombre_rol;
    public $conn;
    function __construct(){
        $this->conn = new mysqli('localhost', 'root', '', 'clickupdated');
    }

    function consultar(){
        $sql = "SELECT * FROM rol";
        $res = $this->conn->query($sql);
        return $res;
    }
}
?>