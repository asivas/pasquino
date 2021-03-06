<?php
require_once('datos/criterio/Restriccion.class.php');

class IsNull extends Restriccion {

    function IsNull($nombrePropiedad) {
        parent::__construct($nombrePropiedad,null);
        $this->operador = "IS NULL";
    }
    
    function toSqlString($clase=null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
        return "{$columna} {$this->operador}";
    }
    
    function toString()
    {
        return "{$this->propiedad} es NULL";
    }
    
    function toArray()
    {
        return array("ISNULL"=>$this->propiedad);
    }
}
