<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Like extends Restriccion{

    function Like($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "LIKE";
        $this->operadorH = "es parecido a";
    }
}