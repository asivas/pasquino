<?php
namespace pQn\datos\criterio;
//require_once('datos/criterio/Criterio.class.php');

class Disjuncion extends Criterio {

    function Disjuncion($expresion1=null,$expresion2=null) 
    {
        parent::__construct();
        $this->_operador = "OR";
        $this->_operadorH = "O";
        if(isset($expresion1))
        $this->_expresiones[] = $expresion1;
        if(isset($expresion2))
        $this->_expresiones[] = $expresion2;
    }
}
