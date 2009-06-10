<?php
require_once('Eq.class.php');

class IsEmpty extends Eq {

    function IsEmpty($nombrePropiedad) {
        parent::__construct($nombrePropiedad,"");
    }
    
    function toString()
    {
    	return "{$this->propiedad} es vacio";
    }
}
