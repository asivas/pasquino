<?php
require_once('datos/criterio/Restriccion.class.php');

class Le extends Restriccion {

    function Le($nombrePropiedad,$valor) {
        parent::__construct($nombrePropiedad,$valor);
        $this->operador = "<=";
        $this->operadorH = "es menor o igual que";
    }
}
