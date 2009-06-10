<?php
require_once('../Restriccion.class.php');

class Like extends Restriccion{

    function Like($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "LIKE";
        $this->operadorH = "es parecido a";
    }
}