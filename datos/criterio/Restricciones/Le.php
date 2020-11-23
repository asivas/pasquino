<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Le extends Restriccion {

    function __construct($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<=";
        $this->operadorH = "es menor o igual que";
    }
}
