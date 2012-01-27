<?php
require_once 'SistemaFCE/util/Configuracion.class.php';

class Entidad implements Serializable{
	protected $_id;
	
	/**
	 * Valor que representa si el objeto esta en proceso de edicion
	 * @var boolean
	 * true:la entidad esta siendo modificada
	 * null/false:la entidad no esta siendo modificada 
	 */
	protected $_edicion;
	
		
	
	function setEdicion($valor)
	{
		$this->_edicion = $valor;
	}
	
	function getEdicion()
	{
		return $this->_edicion;
	}
	
	function serialize()
	{
		//print get_class($this);
		$ref = new ReflectionObject($this);
		$props = $ref->getProperties();
		$result = array();
	    foreach ($props as $pro) {
	        //false && $pro = new ReflectionProperty();	        
	        $prop = $pro->getName();
	        if($prop != '_id')
	        {
		        if(strcasecmp($prop,'_edicion')==0)
		        	$prop = 'edicion';
	        	$getFn = "get".ucfirst($prop);	        
		        $result[$prop] = $this->$getFn();
	        }
	    }
		return serialize($result);
	}
	
	public function unserialize($data) {
        $props = unserialize($data);
		foreach($props as $k => $v)
        {
        	$setFn = "set".ucfirst($k);
        	$this->$setFn($v);
        }
    }
    
    function getId() {
    	if(!isset($this->_id))
    	{
	    	$mapping = Configuracion::getMappingClase(get_class($this));
	    	$cantIds= count($mapping->clase->id);		
	    	if($cantIds == 1)
	    	{
	    		$nombreProp = (string)$mapping->clase->id['nombre'];    					
				$getFn = "get".ucfirst($nombreProp);
				if(method_exists($this, $getFn))
					$this->_id = $this->$getFn();
				elseif(isset($this->$nombreProp))
					$this->_id = $this->$nombreProp;
	    	}
	    	elseif($cantIds>1)
	    	{
	    		foreach($mapping->clase->id as $prop)
				{
					$col = (string)$prop['columna'];
					$nombreProp = (string)$prop['nombre'];
					$getFn = "get".ucfirst($nombreProp);
					
					if(method_exists($this, $getFn))
						$arrId[$col] = $this->$getFn();			
					elseif(isset($this->$nombreProp))
						$arrId[$col] = $this->$nombreProp;
				}
				$this->_id = $arrId;
	    	}
    	}
    	return $this->_id;
    }
    
    /**
     * 
     * Asignación de la id
     * @param unknown_type $newId
     */
    function setId($newId)
    {
    	if($newId!=$this->_id)
    	{
	    	$mapping = Configuracion::getMappingClase(get_class($this));
	    	$cantIds= count($mapping->clase->id);		
	    	if($cantIds == 1)
	    	{
	    		$nombreProp = (string)$mapping->clase->id['nombre'];    					
				$setFn = "set".ucfirst($nombreProp);
				if(method_exists($this, $getFn))
					 $this->$setFn($newId);
				elseif(isset($this->$nombreProp))
					 $this->$nombreProp = $newId;
	    	}
	    	elseif($cantIds>1)
	    	{
	    		foreach($mapping->clase->id as $prop)
				{
					$col = (string)$prop['columna'];
					$nombre = (string)$prop['nombre'];
					$setFn = "set".ucfirst($nombreProp);
					
					if(method_exists($this, $getFn))
						$this->$setFn($newId[$col]);			
					elseif(isset($this->$nombreProp))
						$this->$nombreProp = $newId[$col];
				}
	    	}
	    	
	    	$this->_id = $newId;
    	}
    }
}