<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Eq extends Restriccion {

    function __construct($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "=";
        $this->operadorH = "es igual a";
    }
}
