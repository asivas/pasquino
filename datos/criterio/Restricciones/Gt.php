<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Gt extends Restriccion {

    function Gt($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = ">";
        $this->operadorH = "es mayor que";
    }
}