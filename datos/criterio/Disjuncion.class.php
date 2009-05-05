<?php
require_once('datos/criterio/Criterio.class.php');

class Disjuncion extends Criterio {

    function Disjuncion($expresion1,$expresion2) 
    {
        parent::__construct();
        $this->_operador = "OR";
        $this->_expresiones[] = $expresion1;
        $this->_expresiones[] = $expresion2;
    }
}
