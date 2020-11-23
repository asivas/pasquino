<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restricciones\Eq;

class IsEmpty extends Eq {

    function __construct($nombrePropiedad) {
        parent::__construct($nombrePropiedad,null);
    }
    
    function toString()
    {
    	return "{$this->propiedad} es vacio";
    }
    
    function toArray()
    {
    	return array("EMPTY"=>$this->propiedad);
    }
}
