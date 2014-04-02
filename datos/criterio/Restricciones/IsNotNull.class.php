<?php
require_once('datos/criterio/Restriccion.class.php');

class IsNotNull extends Restriccion {

    function IsNotNull($nombrePropiedad) {
        parent::__construct($nombrePropiedad,null);
        $this->operador = "IS NOT NULL";
    }
    
    function toSqlString($clase=null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
        return "{$columna} {$this->operador}";
    }
    
    function toString()
    {
        return "{$this->propiedad} no es NULL";
    }
    
    function toArray()
    {
        return array("ISNOTNULL"=>$this->propiedad);
    }
}
