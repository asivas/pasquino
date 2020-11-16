<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class In extends Restriccion {

    function In($nombrePropiedad,$valores) {
        parent::__construct($nombrePropiedad,$valores);
    }
    
    private function _getListaIn()
    {
    	$listaIn = "";
    	if(is_string($this->valor))
    		$listaIn = $this->valor;
    	else
	        foreach($this->valor as $v)
	        {
	            if(!empty($listaIn)) $listaIn .= ",";
	            
	            if(is_string($v))
	                $v = "'{$v}'"; 
	            
	            $listaIn .= $v; 
	        }
        return $listaIn;
    }
    
    function toSqlString($clase=null)
    {
    	$listaIn = $this->_getListaIn();
        $columna = $this->buscarNombreColumna($clase,$this->propiedad);
        return "{$columna} IN ($listaIn)";
    }
    
    function toString()
    {
        $listaIn = $this->_getListaIn();
        return "{$this->propiedad} est� en la lista ($listaIn)";
    }
    
    function toArray(){
        $valores = array();
        foreach($this->valor as $v)
        {
            $valores[] = $v;
        }
        return array($this->operador=>array($this->propiedad,$valores));
    }
}