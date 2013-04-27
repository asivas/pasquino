<?php
require_once 'SistemaFCE/modulo/BaseForm.class.php';
class EntidadForm extends BaseForm {
	protected function addElements() {
		parent::addElements();
		$this->addElement('submit','guardar','Guardar');
		$this->addElement('button','cancelar','cancelar');
		$this->addElement('hidden','mod',$_REQUEST['mod']);
		$this->addElement('hidden','id',null);

	}
	
	protected function getDefaultsArray(Entidad $entidad) {
		$defs = parent::getDefaultsArray($entidad);
		$defs['id'] = $entidad->getId();
		return $defs;
	}
}


