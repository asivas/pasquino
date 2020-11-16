<?php
namespace pQn\datos\criterio;

class Conjuncion extends Criterio{

    function Conjuncion($expresion1,$expresion2) 
    {
        parent::__construct();
        $this->_expresiones[] = $expresion1;
        $this->_expresiones[] = $expresion2;
    }
}
