<?php
require_once('Ne.class.php');

class NeProperty extends Ne {

    function NeProperty($nombrePropiedad1,$nombrePropiedad2) {
        parent::__construct($nombrePropiedad1,$nombrePropiedad2);
    }
    
    function toSqlString($clase=null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
        $columna2 = $this->buscarNombreColumna($clase,$this->valor);
        // en el valor está el nombre de la segunda propiedad por eso lo pongo sin ''
        return "{$columna} {$this->operador} {$columna2}";
    }
}
