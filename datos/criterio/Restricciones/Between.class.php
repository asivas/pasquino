<?php
require_once('datos/criterio/Restriccion.class.php');

class Between extends Restriccion {
    
    private $valor2;
    
    function Between($nombreProp,$valor1,$valor2) {
        $this->propiedad = $nombreProp;
        $this->valor     = $valor1;
        $this->valor2    = $valor2;
        $this->operador  = "BETWEEN"; 
        $this->operadorH = " entre ";
    }
    
    function toSqlString($clase=null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
        return "{$columna} {$this->operador} '{$this->valor}' AND '{$this->valor2}'"; 
    }
    
    function toString()
    {
        return "{$this->propiedad} {$this->operadorH} '{$this->valor}' y '{$this->valor2}'"; 
    }
}