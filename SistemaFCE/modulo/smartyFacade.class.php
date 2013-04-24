<?php 
class smartyFacade
{
	
	var $modName;
	
	function __construct($modName)
	{
		$this->modName = $modName;
	}
	
	function getMod()
	{
		return new $this->modName(); 
	}
	
	function getPropiedadMod($property,$object = null)
	{
		$mod = $this->getMod();
		$method = 'get'.ucfirst($property);
		if(method_exists($mod,$method))
			return $mod->$method($object);
		if($object && method_exists($object,$method))
			return $object->$method();
		return 'undefined';
	}
	
}