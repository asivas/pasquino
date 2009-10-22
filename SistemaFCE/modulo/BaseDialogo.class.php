<?php
/**
* @author Martinez Diaz, Diego
* @since 04/06/09
*/
require_once('SistemaFCE/modulo/BaseMod.class.php');

abstract class BaseDialogo extends BaseMod
{
	public $objResponse;
    public $nombreDlg;
	
	function __construct()
	{
		parent::__construct(null,false);
		$this->nombreDlg = str_replace('Dlg','',get_class($this));
        $this->objResponse = new xajaxResponse();
        if($_POST['xjxcls']==get_class($this))
            $this->crearForm();
	}
	
    /**
     * Asigna las variables de smarty
     */
    abstract protected function assignsSmarty($assigns = null);
    /**
     * Guarda las cosas 
     */
    abstract protected function guardar($itemsForm);
	/**
	 * Actualiza el contenedor
	 */
    abstract protected function actualizarGrid(&$objResponse);
    
    /**
     * Devuelve la ruta del tpl del dialogo      
     */
    abstract protected function getTplPath();

    protected function crearDialogoConFondo($nombre='')
    {
        if(empty($nombre)) $nombre = $this->nombreDlg;
        
        $script = "agregarDialogo('{$nombre}')";
        $this->objResponse->script($script);
    }
    	
	function cargarDialogo($idItem = 0)
	{	
		$this->crearDialogoConFondo($this->nombreDlg);
		
		$f = $this->getForm();
		
		if($idItem > 0)
		{
			$f->setDefaults($idItem);
		}
		
		$this->assignsSmarty();
		
		$this->renderForm();
		
		$this->objResponse->assign($this->nombreDlg,"innerHTML",$this->fetch($this->getTplPath()));
		
		return $this->objResponse;
	}
    

    function aceptar($itemsForm)
    {
        $this->guardar($itemsForm);
        $this->actualizarGrid($this->objResponse);        
        $this->objResponse->script("cerrarDialogo();");
        return $this->objResponse;
    }
    
    function cancelar($itemsForm)
    {
        return $this->objResponse;	
    }
}