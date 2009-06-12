<?php
require_once('datos/criterio/Restriccion.class.php');
require_once('datos/criterio/Criterio.class.php');

class Not extends Restriccion {
    
    private $_restriccion;
    
    function Not($restriccion) {
        $this->_restriccion = $restriccion; 
    }
    
    function toSqlString()
    {
    	$slqANegar = "";
        
        if(is_a($this->_restriccion,'Restriccion'))
            $slqANegar = $this->_restriccion->toSqlString();
        elseif(is_a($this->_restriccion,'Criterio'))
            $slqANegar = $this->_restriccion->getCondicion();
        
        return " NOT (".$slqANegar.")";
    }
    
    function toString()
    {
    	$aNegar = $this->_restriccion->toString();
        
        return " no cumple (".$aNegar.")";
    }
    
    function toArray()
    {
        return array("NOT"=>$this->_restriccion->toArray());
    }
}
