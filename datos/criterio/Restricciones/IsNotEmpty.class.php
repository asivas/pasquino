<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restricciones\Ne;

class IsNotEmpty extends Ne {

    function IsNotEmpty($nombrePropiedad) {
        parent::__construct($nombrePropiedad,null);
    }
    
    function toString()
    {
        return "{$this->propiedad} no es vacio";
    }
    
    function toArray()
    {
        return array("NOTEMPTY"=>$this->propiedad);
    }
}