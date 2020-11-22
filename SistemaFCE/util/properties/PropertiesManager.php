<?php
namespace pQn\SistemaFCE\util\properties;

interface PropertiesManager {
	
	/**
	 * Returns the value of a given property.
	 * @param $propertyKey Property identifying key
	 * @param $dafaultValue Default value returned if not exists property key
	 */
	static public function getPropertyValue($propertyKey, $dafaultValue = null);
	
	/**
	 * Set the value of a given property. If property key does not exist, it is created.
	 * @param $propertyKey Property identifying key
	 * @param $value Value of the property
	 */
	static public function setPropertyValue($propertyKey, $value);
	
	/**
	 * Delete a given property.
	 * @param $propertyKey Property identifying key
	 */
	static public function deleteProperty($propertyKey);
	
	
	/**
	 * Determines the existence of a property.
	 * @param $propertyKey
	 * @return Bool Boolean value with the result
	 */
	static public function existsProperty($propertyKey);
}