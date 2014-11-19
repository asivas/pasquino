<?php
require_once "SistemaFCE/dao/DaoBase.class.php";

class DaoConfigurationProperty extends DaoBase{

	
	function __construct() {
		$this->_dieOnFindByError=false;
		parent::DaoBase();
	}

	protected function getKeyColumn(){
		return "propiedad";
	}
	
	function findByKey($propiedad)
	{
		$c = new Criterio();

		$c->add(Restricciones::eq($this->getKeyColumn(), $propiedad));

		return $this->findFirst($c);
	}
	
	protected function loadMapping() {
		$mapping = parent::loadMapping();
		if($this->_pathEntidad != '.class.php' && isset($this->_pathEntidad))
			return $mapping;
		else{
			$this->_pathEntidad = 'SistemaFCE/entidad/ConfigurationProperty.class.php';
			$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE mapping PUBLIC "-//FCEunicen//DTD Mapping//ES" "http://apps.econ.unicen.edu.ar/public/dtd/mapping.dtd" >
<mapping path="SistemaFCE/entidades">
	<clase nombre="ConfigurationProperty" tabla="configurationproperty">
    			<id columna="key" nombre="key" />
				<propiedad columna="value" nombre="value" />
	</clase>
</mapping>';
			return new SimpleXMLElement($xmlstr);
		}
	} 
}