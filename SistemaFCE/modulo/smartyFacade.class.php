<?php
/**
 * Conjunto de metodos de interacion entre smarty y un modulo asociado 
 * 
 * @author Diego
 *
 */
class smartyFacade
{
	var $mod;
	
	/**
	 * Recibe el modulo sobre el cual se inicializa el facade,
	 * y lo carga en una variable miembro
	 * 
	 * @param Modulo $mod modulo asociado
	 */
	function __construct($mod)
	{
		$this->mod = $mod;
	}
	
	/**
	 * Devuelve una propiedad definida en el modulo asociado, una propiedad del objeto, o undefined si no existe get{$property}() en el modulo o el objeto
	 * 
	 * @param string $property nombre de la propiedad del objeto, o de una pseudo-propiedad definida en el modulo
	 * @param Entidad $object entidad a la cual se pide la propiedad get{$property}() en caso de no estar definida en el modulo asociado
	 * @return string 
	 */
	function getPropiedadMod($property,$object = null)
	{
		$method = 'get'.ucfirst($property);
		if(method_exists($this->mod,$method))
			return $this->mod->$method($object);
		if($object && method_exists($object,$method))
			return $object->$method();
		return 'undefined';
	}
	
}