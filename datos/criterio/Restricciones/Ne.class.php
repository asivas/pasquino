<?php
require_once('datos/criterio/Restriccion.class.php');

class Ne extends Restriccion {

    function Ne($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<>";
        $this->operadorH = "es distinto de";
    }
}
