<?php
require_once('datos/criterio/Restriccion.class.php');

class Lt extends Restriccion {

    function Lt($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<";
        $this->operadorH = "es menor que";
    }
}
