<?php
require_once("HTML/QuickForm.php");
require_once("HTML/QuickForm/Renderer/ArraySmarty.php");

class BaseForm extends HTML_QuickForm {
    
    function BaseForm($nombre, $metodo='POST', $accion='',$target='',$attributos='') {
    	
    	if(empty($accion)) $accion = $_SERVER['PHP_SELF'];
    	parent::__construct($nombre,$metodo,$accion,$target,$attributos);
    }
    
    /**
     * Genera el arreglo renderizado para smarty del form
     * @return array arreglo renderizado con el renderer de array de smarty
     */
    protected function renderSmarty($smarty)
    {
        $renderer= new HTML_QuickForm_Renderer_ArraySmarty($smarty);// creacion del renderer para smarty
        
        $this->accept($renderer);// inclusion en el form del renderer
        
        return $renderer->toArray();// pasaje a arreglo del renderer   	
    }
}