<?php
namespace pQn\SistemaFCE\entidad;

use pQn\SistemaFCE\util\Configuracion;


class Entidad implements \Serializable{
	protected $_id;
	/**
	 * Arreglo buffer de entidades relacionadas,
	 *
	 * para evitar la busqueda repetida de entidades relacionadas
	 * @var array
	 */
	private $relacCache;

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
		$ref = new \ReflectionObject($this);
		$props = $ref->getProperties();
		$result = array();
	    foreach ($props as $pro) {
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
	    	$mapping = $this->getMapping();
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
	    		$arrId = array();
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
	    	$mapping = $this->getMapping();
	    	$cantIds= count($mapping->clase->id);
	    	$this->_id = $newId;

	    	if($cantIds == 1)
	    	{
	    		$nombreProp = (string)$mapping->clase->id['nombre'];
				$setFn = "set".ucfirst($nombreProp);
				if(method_exists($this, $setFn))
					 $this->$setFn($newId);
				elseif(isset($this->$nombreProp))
					 $this->$nombreProp = $newId;

	    	}
	    	elseif($cantIds>1)
	    	{
	    		foreach($mapping->clase->id as $prop)
				{
					$col = (string)$prop['columna'];
					$nombreProp = (string)$prop['nombre'];
					$setFn = "set".ucfirst($nombreProp);

					if(method_exists($this, $setFn))
						$this->$setFn($newId[$col]);
					elseif(isset($this->$nombreProp))
						$this->$nombreProp = $newId[$col];
				}
	    	}


    	}
    }

    /**
     *
     * Muestra un string que identifica al elemento a nivel humano
     */
    function toString() { return get_class($this) . " #". $this->getId(); }

    /**
     * Agrega una variable de entidad relacionada al cache
     * @param string $key
     * @param object $value
     */
    protected function setCacheRelacionado($key,$value) {
    	$this->relacCache[$key] = $value;
    }

    /**
     * Obtiene el elemento cacheado con la clave dada
     * @param string $key
     * @return multitype:
     */
    protected function getCacheRelacionado($key) {
    	return $this->relacCache[$key];
    }

    /**
     * Limpia el cache de la clave dada
     * @param string $key
     */
    protected function clearCacheRelacionado($key) {
    	unset($this->relacCache[$key]);
    }

    /**
     * Obtiene una entidad relacionada dado su id y la clase que se espera que sea
     * @param int|array $relFk id de la entidad relacionada
     * @param string $relClass nombre de la clase del objeto que se quiere obtenr
     * @return Entidad
     */
    protected function getEntidadRelacionada($relFk,$relClass) {

    	if(($rel = $this->getCacheRelacionado($relClass))==null)
    	{
    		$daoClass = 'Dao'.$relClass;
    		$dao = $daoClass::getInstance();
    		$rel = $dao->findById($relFk);
    		$this->setCacheRelacionado($relClass,$rel);
    	}
    	return $rel;
    }
    
    /**
     * Obtiene el mapping (simple_xml_object) asociado al objeto para ORM
     */
    protected function getMapping() {
    	return Configuracion::getMappingClase(get_class($this));
    }
    
}