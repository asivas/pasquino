<?php

class Restriccion {
    
    protected $operador;
    protected $propiedad;
    protected $valor;
    
    protected $operadorH;
    
    
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
            $propiedades = $this->_xmlMapping->propiedad;
            foreach($propiedades as $prop)
            {
                $nombreProp = (string)$prop['nombre'];
                if($nombreProp == $nombrePropiedad)
                    return (string)$prop['columna'];
            }
        }
        return $nombrePropiedad;
    }  
    
    function toSqlString($clase=null)
    {
        $val = $this->valor;
        if(is_string($this->valor))
            $val = "'{$this->valor}'";

        $columna = $this->buscarNombreColumna($clase,$this->propiedad); 

        return "{$columna} {$this->operador} {$val}";	
    }
    
    function toString()
    {
    	return "{$this->propiedad} {$this->operadorH} {$this->valor}";
    }
    
    function toArray(){
    	return array($this->operador=>array($this->propiedad,$this->valor));
    }
    
}
