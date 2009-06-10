<?php
require_once('datos/criterio/Restriccion.class.php');

class Eq extends Restriccion {

    function Eq($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "=";
        $this->operadorH = "es igual a";
    }
}
