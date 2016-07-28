<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restricciones\Eq;

class EqProperty extends Eq {

    function EqProperty($nombrePropiedad1,$nombrePropiedad2) {
        parent::__construct($nombrePropiedad1,$nombrePropiedad2);
    }
    
    function toSqlString($clase=null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
        $columna2 = $this->buscarNombreColumna($clase,$this->valor);
        // en el valor est� el nombre de la segunda propiedad por eso lo pongo sin ''
        return "{$columna} {$this->operador} {$columna2}";
    }
}
