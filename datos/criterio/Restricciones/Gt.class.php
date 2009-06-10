<?php
require_once('datos/criterio/Restriccion.class.php');

class Gt extends Restriccion {

    function Gt($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = ">";
        $this->operadorH = "es mayor que";
    }
}
