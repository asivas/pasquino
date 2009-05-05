<?php

require_once('datos/criterio/Conjuncion.class.php');
require_once('datos/criterio/Disjuncion.class.php');

class Criterio {
    
    protected $_expresiones;
    protected $_operador = "AND";
    
    static function getAND($expresion1,$expresion2){ new Conjuncion($expresion1,$expresion2); }
    static function getOR($expresion1,$expresion2){ new Disjuncion($expresion1,$expresion2); }
    
    function Criterio() {
        $_expresiones = array();
    }
    
    function add($expresion) {
    	$this->_expresiones[] = $expresion;
    }
    
    /**
     * Genera la condición de SQL a partir de los datos que existen en $this->_expresiones
     * @return string la condición generada
     */
    function getCondicion()
    {
    	$cond = "";
        foreach($this->_expresiones as $exp)
        {
        	if(!empty($cond)) $cond .= " {$this->_operador} ";
            
            if(is_string($exp))
                $cond .= $exp;
            else //si no es string si o si debe ser alguna clase de Criterio
            	$cond .= "(". $exp->getCondicion() .")";
        }
        return $cond;
    }
    
    function disjuncion()
    {
    	$this->_operador = "OR";
        $disj = clone $this;
        $this->_operador = "AND";
        var_dump($disj);
        return $disj;
    }
}
