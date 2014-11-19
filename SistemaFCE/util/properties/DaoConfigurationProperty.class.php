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
}