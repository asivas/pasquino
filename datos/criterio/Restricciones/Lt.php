<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Lt extends Restriccion {

    function __construct($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<";
        $this->operadorH = "es menor que";
    }
}
