<?php
namespace pQn\SistemaFCE\util\properties;

use pQn\SistemaFCE\dao\DaoBase;
use pQn\datos\criterio\Restricciones;
use pQn\datos\criterio\Criterio;

class DaoConfigurationProperty extends DaoBase{


	function __construct() {
		$this->_dieOnFindByError=false;
		parent::__construct();
	}

	protected function getKeyColumn(){
		return "property";
	}

	function findByKey($propiedad)
	{
		$c = new Criterio();

		$c->add(Restricciones::eq($this->getKeyColumn(), $propiedad));

		return $this->findFirst($c);
	}

	public static function getDefaultMapping() {
		$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE mapping PUBLIC "-//FCEunicen//DTD Mapping//ES" "http://apps.econ.unicen.edu.ar/public/dtd/mapping.dtd" >
<mapping path="SistemaFCE/entidades">
	<clase nombre="ConfigurationProperty" tabla="configurationproperty">
    			<id columna="property" nombre="property" />
				<propiedad columna="value" nombre="value" />
	</clase>
</mapping>';
		$map=new \SimpleXMLElement($xmlstr);
		return $map;
	}

	protected function loadMapping() {
		$mapping = parent::loadMapping();
		if(stream_resolve_include_path($this->_pathEntidad))
			return $mapping;
		else{
			$this->_pathEntidad = 'SistemaFCE/entidad/ConfigurationProperty.class.php';
			$map = $this->getDefaultMapping();
			$this->_xmlMapping=$map->clase;
			return $map;
		}
	}
}