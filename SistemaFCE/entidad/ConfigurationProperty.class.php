<?php
require_once 'SistemaFCE/util/Configuracion.class.php';
require_once 'SistemaFCE/entidad/Entidad.class.php';

class ConfigurationProperty extends Entidad {

	private $property;
	private $value;
	
	public function getProperty() {
		return $this->property;
	}
	
	public function setProperty($newKey) {
		$this->property = $newKey;
	}

	public function getValue() {
		return $this->value;
	}
	
	public function setValue($newValue) {
		$this->value = $newValue;
	}
    
    /**
     * Obtiene el mapping (simple_xml_object) asociado al objeto para ORM
     */
    protected function getMapping() {
    	$configurationFound=@Configuracion::getMappingClase(get_class($this));
    	if ($configurationFound)
    		return $configurationFound;
    	$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE mapping PUBLIC "-//FCEunicen//DTD Mapping//ES" "http://apps.econ.unicen.edu.ar/public/dtd/mapping.dtd" >
<mapping path="SistemaFCE/entidades">
	<clase nombre="ConfigurationProperty" tabla="configurationproperty">		
    			<id columna="property" nombre="property" />
				<propiedad columna="value" nombre="value" />
	</clase>
</mapping>';
    	return new SimpleXMLElement($xmlstr);
    }
    
}