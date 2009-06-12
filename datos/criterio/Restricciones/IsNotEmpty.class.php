<?php
require_once('Ne.class.php');

class IsNotEmpty extends Ne {

    function IsNotEmpty($nombrePropiedad) {
        parent::__construct($nombrePropiedad,"");
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