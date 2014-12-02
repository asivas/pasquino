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
    
}