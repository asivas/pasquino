<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Ne extends Restriccion {

    function Ne($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<>";
        $this->operadorH = "es distinto de";
    }
}