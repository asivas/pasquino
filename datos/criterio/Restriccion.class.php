<?php

class Restriccion{
    
    protected $operador;
    protected $propiedad;
    protected $valor;
    
    protected $operadorH;
    
    public function getOperador()
    {
    	return $this->operador;
    }
    
    public function getPropiedad()
    {
    	return $this->propiedad;
    }
    
    public function getValor()
    {
    	return $this->valor;
    }
    
    function Restriccion($nombrePropiedad,$valor) {
        $this->propiedad = $nombrePropiedad;
        $this->valor = $valor;   
    }
    
    protected function buscarNombreColumna($clase,$nombrePropiedad)
    {
    	if(!empty($clase))
        {
            $m = Configuracion::getMappingClase($clase);
            $mc = $m->clase;
            $propiedades = $m->propiedad;
            if(is_array($propiedades))
            foreach($propiedades as $prop)
            {
                $nombreProp = (string)$prop['nombre'];
                if($nombreProp == $nombrePropiedad)
                    return (string)$prop['columna'];
            }
        }
        return $nombrePropiedad;
    }  
    
    function toSqlString($clase=null,$paramName=null)
    {
        $val = $this->valor;
        if(is_string($this->valor))
            $val = "'{$this->valor}'";

        $columna = $this->buscarNombreColumna($clase,$this->propiedad); 
    	if(strpos($columna,' ')!==false && $columna!=$this->propiedad)
    		$columna = "`{$columna}`";
    	
    	$sqlString = "{$columna} {$this->operador} {$val}";
    	if(isset($paramName))
    		$sqlString = "{$columna} {$this->operador} :{$paramName}";
        
    	return $sqlString;	
    }
    
    function toString()
    {
    	return "{$this->propiedad} {$this->operadorH} {$this->valor}";
    }
    
    function toArray(){
    	return array($this->operador=>array($this->propiedad,$this->valor));
    }
    
    function toArraySerialize()
	{
		return array(get_class($this)=>array($this->propiedad,$this->valor));
	}
	
	static function fromArraySerialize($array)
	{
		$clase = key($array);
		include_once('datos/criterio/Restricciones/'.$clase.'.class.php');
		
		if($clase == 'AllEq' || $clase == 'IsEmpty' || $clase == 'IsNotEmpty' 
		|| $clase == 'IsNull' || $clase == 'IsNotNull' || $clase == 'Not') 
		{
			$obj = new $clase($array[key($array)][0]);
		}
		else
		{
			$obj = new $clase($array[key($array)][0],$array[key($array)][1]);
		}
		
		return $obj;
	}
}
