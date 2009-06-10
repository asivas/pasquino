<?php
require_once('datos/criterio/Restriccion.class.php');

class Ge extends Restriccion {

    function Ge($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = ">=";
        $this->operadorH = "es mayor o igual que";
    }
}
