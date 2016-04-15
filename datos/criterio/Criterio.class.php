<?php

require_once('datos/criterio/Conjuncion.class.php');
require_once('datos/criterio/Disjuncion.class.php');
require_once('datos/criterio/Restricciones.class.php');

class Criterio{

    protected $_expresiones;
    protected $_operador = "AND";
    protected $_operadorH = "Y";

    protected $bindParameters = array();

    static function getAND($expresion1,$expresion2){ return new Conjuncion($expresion1,$expresion2); }
    static function getOR($expresion1,$expresion2){ return new Disjuncion($expresion1,$expresion2); }

    function Criterio() {
        $this->_expresiones = array();
    }

    public function add($expresion) {
    	$this->_expresiones[] = $expresion;
        return $this;
    }

    public function del($posicion) {
    	$cantExpresiones = $this->cantExpresiones();
    	if($posicion < $cantExpresiones)
    	{
    		for($x=$posicion;$x<$cantExpresiones-1;$x++)
    		{
    			$this->_expresiones[$x] = $this->_expresiones[$x+1];
    		}
    		$this->_expresiones[$cantExpresiones-1] = null;
    		unset($this->_expresiones[$cantExpresiones-1]);
    	}
    }

    public function insert($posicion,$expresion) {
    	$cantExpresiones = $this->canExpresiones();
    	if($posicion <= $cantExpresiones)
    	{
    		for($x=$posicion;$x<$cantExpresiones;$x++)
    		{
    			$this->_expresiones[$x+1] = $this->_expresiones[$x];
    		}
    		$this->_expresiones[$posicion] = $expresion;
    	}
    }

    public function cantExpresiones()
    {
    	return count($this->_expresiones);
    }

    /**
     * Genera la condición de SQL a partir de los datos que existen en $this->_expresiones
     * @return string la condición generada
     */
    function getCondicion($clase=null,&$parametized=false)
    {
    	$cond = "";
        foreach($this->_expresiones as $exp)
        {
        	if(!empty($cond)) $cond .= " {$this->_operador} ";

            if(is_string($exp))
            {
                $cond .= $exp;
            }
            elseif(is_a($exp,"In")) {
				$cond .= $exp->toSqlString($clase);
            }
            elseif(is_a($exp,"Between")) {
            	$paramMin = $parametized;
            	$cond .= $exp->toSqlString($clase,$parametized!==false?$parametized:null,$parametized!==false?++$parametized:null);
            	if($parametized!==false && $exp->getValorMin()!==null && $exp->getValorMax()!==null)
            	{
            		$this->bindParameters[$paramMin] = $exp->getValorMin();
            		$this->bindParameters[$parametized++] = $exp->getValorMax();
            	}
			}elseif(is_a($exp,"Restriccion")) {
                $cond .= $exp->toSqlString($clase,$parametized!==false?$parametized:null);
                if($parametized!==false && $exp->getValor()!==null)
                	$this->bindParameters[$parametized++] = $exp->getValor();
            }
            elseif(is_a($exp,"Criterio")) //si no es string si o si debe ser alguna clase de Criterio
            {
            	//TODO: parece que sería saludable corroborar que  $exp != $this
            	$cond .= "(". $exp->getCondicion($clase,$parametized) .")";
            	$this->bindParameters = array_merge($this->bindParameters,$exp->getBindValues());
            }
        }
        return $cond;
    }


    function disjuncion()
    {
    	$this->_operador = "OR";
    	$this->_operadorH = "O";
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
    	//FIXME: revisar parentesis

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

    function toArraySerialize()
    {
     	$a = array();
        $a[$this->_operador] = array();
        foreach($this->_expresiones as $exp)
        {
            if(!is_string($exp) && !is_array($exp)) $exp = $exp->toArraySerialize();
            $a[$this->_operador][] = $exp;
        }
        return $a;
    }

	function serialize()
	{
		$array = $this->toArraySerialize();
		return serialize($array);
	}

	static function unserialize($data)
	{
		$array = unserialize($data);
		$oper = key($array);

		if($oper!="OR" && $oper!="AND")
			return Restriccion::fromArraySerialize($array);

		$c = new Criterio();

		if($oper== "OR")
			$c = $c->disjunction();

		foreach($array[$oper] as $exp)
			$c->add(Criterio::unserialize(serialize($exp)));

		return $c;
	}

	function getBindValues() {
		return $this->bindParameters;
	}
}
