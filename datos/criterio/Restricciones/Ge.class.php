<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Ge extends Restriccion {

    function Ge($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = ">=";
        $this->operadorH = "es mayor o igual que";
    }
}
