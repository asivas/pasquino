<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class Between extends Restriccion {
    
    private $valor2;
    
    function Between($nombreProp,$valor1,$valor2) {
        $this->propiedad = $nombreProp;
        $this->valor     = $valor1;
        $this->valor2    = $valor2;
        $this->operador  = "BETWEEN"; 
        $this->operadorH = " entre ";
    }
    
    function toSqlString($clase=null,$paramNameMin=null,$paramNameMax= null)
    {
    	$columna = $this->buscarNombreColumna($clase,$this->propiedad);
    	
    	$sqlString = "{$columna} {$this->operador} '{$this->valor}' AND '{$this->valor2}'";    	
    	if(isset($paramNameMin,$paramNameMax))
    		$sqlString = "{$columna} {$this->operador} :{$paramNameMin} AND :{$paramNameMax}";
        
    	return $sqlString; 
    }
    
    function toString()
    {
        return "{$this->propiedad} {$this->operadorH} '{$this->valor}' y '{$this->valor2}'"; 
    }
    
    function toArray(){
        
        return array($this->operador=>array($this->propiedad,array($this->valor,$this->valor2)));
    }
    
    function getValorMin() {
    	return $this->valor;
    }
    
    function getValorMax() {
    	return $this->valor2;
    }
}