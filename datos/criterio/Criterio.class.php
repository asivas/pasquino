<?php

require_once('datos/criterio/Conjuncion.class.php');
require_once('datos/criterio/Disjuncion.class.php');
require_once('datos/criterio/Restricciones.class.php');

class Criterio {
    
    protected $_expresiones;
    protected $_operador = "AND";
    protected $_operadorH = "y";
    
    static function getAND($expresion1,$expresion2){ return new Conjuncion($expresion1,$expresion2); }
    static function getOR($expresion1,$expresion2){ return new Disjuncion($expresion1,$expresion2); }
    
    function Criterio() {
        $this->_expresiones = array();
    }
    
    public function add($expresion) {
    	$this->_expresiones[] = $expresion;
        return $this;
    }
    
    /**
     * Genera la condición de SQL a partir de los datos que existen en $this->_expresiones
     * @return string la condición generada
     */
    function getCondicion($clase=null)
    {
    	$cond = "";
        foreach($this->_expresiones as $exp)
        {
        	if(!empty($cond)) $cond .= " {$this->_operador} ";
            
            if(is_string($exp))
            {   
                $cond .= $exp;
            }
            elseif(is_a($exp,"Restriccion"))
                $cond .= $exp->toSqlString($clase);
            elseif(is_a($exp,"Criterio")) //si no es string si o si debe ser alguna clase de Criterio
            	$cond .= "(". $exp->getCondicion() .")";
        }
        return $cond;
    }
    
    function disjuncion()
    {
    	$this->_operador = "OR";
    	$this->_operadorH = "o";
        $disj = clone $this;
        $this->_operador = "AND";
        $this->_operadorH = "y";
        return $disj;
    }
    
    /**
     * Genera una cadena entendible por los humanos del criterio
     */
    function toString()
    {
    	$str = "";
                
        foreach($this->_expresiones as $exp)
        {
            if(!empty($str)) $str .= " {$this->_operadorH} ";

            if(is_string($exp))
                $str .= $exp;
            else
                $str .= $exp->toString();
        }
        return $str;
    }
    
    function toArray()
    {
     	$a = array();
        $a[$this->_operador] = array();
        foreach($this->_expresiones as $exp)
        {
            if(!is_string($exp) && !is_array($exp)) $exp = $exp->toArray();
            $a[$this->_operador][] = $exp;
        }
        return $a;
    }
}
