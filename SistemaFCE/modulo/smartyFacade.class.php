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
	
	static function getPropiedadMod($property,$object = null)
	{
		$mod = $this->getMod();
		$method = 'get'.ucfirst($property);
		if(method_exist($mod,$method))
			return $mod->$method();
		if($object && method_exist($object,$method))
			return $object->$method();
		return 'undefined';
	}
	
}