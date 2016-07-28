<?php
namespace pQn\SistemaFCE\entidad;

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