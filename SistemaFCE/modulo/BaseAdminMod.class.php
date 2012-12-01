<?php
// si no se definió en otro lado cargo el smarty 3, para que no se cargue el 2 en baseMod
if(!class_exists('Smarty'))
	require_once('visual/smarty3/libs/Smarty.class.php');

require_once 'SistemaFCE/modulo/BaseMod.class.php';


/**
 * Modulo Esqueleto de administración
 *
 * Tiene los tpls de lista, form y un dao principal. Tambien se definen los
 * metodos de alta, baja y modificación asi como el que genera el form y tiene un guardar
 * que devuelve por ajax un mensaje de success
 * @author lucas
 *
 */
abstract class BaseAdminMod extends BaseMod {
	protected $mainDao;
	protected $_tplLista;
	protected $_tplForm;

	function __construct($form, $dao, $skinDirname=null, $listaTplPath=null, $formTplPath=null, $tilePathName = 'Admin', $sessionHandler=null)
	{
		if(isset($sessionHandler))	$this->session = $sessionHandler;

		parent::__construct($skinDirname,false); // como se usa mayormente jquery se pasa por defecto el conXajax en false

		$this->_form = $form;
		$this->mainDao = $dao;

		$this->_tplLista = $listaTplPath;
		$this->_tplForm = $formTplPath;

		$tConf = Configuracion::getTemplateConfigByDir($templateDir);
		$this->_tilePath = Configuracion::findTplPath($tConf,$tilePathName);

		//busco el dir esperado por defecto de los tpls del modulo
		$dir = strtolower( str_replace("Mod", "", get_class($this)) );
		//si no está seteado el tpl lista cargo un path por defecto con el dir y lista.tpl
		if(!isset($this->_tplLista)) $this->_tplLista = "{$dir}/lista.tpl";
		//si no está seteado el tpl form cargo un path por defecto con el dir y form.tpl
		if(!isset($this->_tplForm)) $this->_tplForm = "{$dir}/form.tpl";

		if(isset($nada)) //TODO: borrar esta linea y la siguiente, está para que autocomplete
			$this->mainDao = new DaoBase();
	}

	/**
	 * Envia (haciendo display) un mensaje de status usando el tpl de msgStatus
	 * @param string $status
	 * @param string $mensaje
	 * @param array $otros
	 */
	private function mensaje($status,$mensaje,$otros=null)
	{
		$this->smarty->assign("status",$status);
		$this->smarty->assign("msg",$mensaje);
		$this->smarty->assign("otros",$otros);
		$this->smarty->display('string:<status msg="{$msg}" status="{$status}" {foreach from=$otros key=k item=valor} {$k}={$valor} {/foreach}></status>');
		die();
	}

	/**
	 * Envia un mensaje de OK en xml usando mensaje
	 * @param string $mensaje el mensaje
	 * @param array $otros arreglo de otros atributos para agregarle al <status>
	 */
	protected function mensajeOK($mensaje,$otros=null)
	{
		$this->mensaje("OK", $mensaje, $otros);
	}

	/**
	 *
	 * Envia un mensaje de ERR (error) via xml usando mensaje
	 * @param string $mensaje el mensaje
	 * @param array $otros arreglo de otros atributos
	 */
	protected function mensajeERR($mensaje,$otros=null)
	{
		$this->mensaje("ERR", $mensaje, $otros);
	}

	/**
	 * Obtiene el id del item que se desea buscar/generar a partir del req
	 * @param array $req
	 */
	protected function getItemId($req) {
		$id = $this->mainDao->getIdElementoDeArreglo($req);
		return $id;
	}


	/**
	 * Valida si el arreglo req tiene las variables completas necesrias como para definir un id
	 * del objeto de ABM
	 * @param array $req
	 */
	protected function validateId($req){
		return isset($req['id']);
	}


	/* (non-PHPdoc)
	 *
	 * @see BaseMod::getFiltro()
	 */
	function getFiltro($req) {
		//TODO: ver si hace falta que sea publica o puede ser protected como la parent
		$c = parent::getFiltro($req);
		if(isset($req['filtroNombre'])){
			$this->smarty->assign('filtroNombre',$req['filtroNombre']);
			$c->add(Restricciones::like('nombre',"%{$req['filtroNombre']}%"));
		}
		return $c;
	}

	/**
	 * Guarda las propiedades extra que no dependen del mapping y
	 * son agregadas en addExtraProps
	 * @param $aObj
	 */
	function guardarExtraProps($aObj){
	}

	/**
	 * Guarda un objeto usando su dao (el $this->mainDao)
	 * @param object $aObj
	 */
	protected function guardar($aObj) {
		if(method_exists($aObj, "toString"))
			$strInfo = $aObj->toString();
		else
			$strInfo = "El elemento de tipo ".get_class($aObj);
		if($this->mainDao->save($aObj))
		{
			$this->guardarExtraProps($aObj);
			$this->mensajeOK("{$strInfo} fue guardado con exito",array('id'=>$aObj->getId()));
		}
		else
			$this->mensajeERR("{$strInfo} no se pudo guardar ". $this->mainDao->getLastError());
	}

	/**
	 * Envía un mensaje de errror ante fallo de validaci�n del formulario
	 */
	protected function sendValidateErrorMsg() {
		$strErr = "No se cumplen las reglas de validación:";
		$f = $this->renderForm();
		foreach($f['errors'] as $nombreRegla=>$err)
			$strErr .= "\n".$err;
		$this->mensajeERR($strErr);
	}

	/**
	 *
	 * En este metodo se pueden agregar (probablemente desde el req) las opciones extra que no
	 * vienen de mapeo antes de guardar
	 * @param array $req
	 * @param mixed $aObj
	 */
	protected function addExtraProps($req,&$aObj){}

	/* (non-PHPdoc)
	 * @see BaseMod::alta()
	 */
	public function alta($req) {

		if($this->getForm()->validate())
		{
			$aObj = $this->mainDao->crearDesdeArreglo($req);
			$this->addExtraProps($req, $aObj);
			$this->guardar($aObj);
		}
		else
			$this->sendValidateErrorMsg();
	}

	/* (non-PHPdoc)
	 * @see BaseMod::modificacion()
	 */
	public function modificacion($req) {
		if($this->getForm()->validate())
		{
			$aObj = $this->mainDao->crearDesdeArreglo($req);
			// en caso de que el id no sea simple o no est�
			// pasado con el mismo nombre de la propiedad
			$aObj->setId($this->getItemId($req));
			$this->addExtraProps($req, $aObj);
			$this->guardar($aObj);
		}
		else
			$this->sendValidateErrorMsg();
	}

	/* (non-PHPdoc)
	 * @see BaseMod::baja()
	 */
	public function baja($req){
		$id = $this->getItemId($req);

		$aObj = $this->mainDao->findById($id);
		if(method_exists($aObj, "toString") && $aObj->toString()!='')
			$strInfo =  $aObj->toString();
		else
			$strInfo = "el elemento de tipo ".get_class($aObj);

		if ($this->mainDao->deletePorId($id))
			$this->mensajeOK("Se pudo eliminar {$strInfo} #{$id} ");
		else
			$this->mensajeERR("No se puede eliminar {$strInfo} [Error {$this->mainDao->getLastError()}]");
	}

	/* (non-PHPdoc)
	 * @see BaseMod::lista()
	 */
	public function lista($req){
		$aObjs = $this->mainDao->findBy($this->getFiltro($req),$req['sort']);

		$nombreClase="";
		if(count($aObjs)>0)
		{
			$aObj = current($aObjs);
			$nombreClase = get_class($aObj);
		}
		$this->smarty->assign('lista'.$nombreClase,$aObjs);
		$this->mostrar($this->_tplLista,$req['display']);
	}

	/* (non-PHPdoc)
	 * @see BaseMod::form()
	 */
	public function form($req){
		$id = $this->getItemId($req);
		if($this->validateId($req))
		{
			$a = $this->mainDao->findById($id);
			$this->smarty->assign(get_class($a),$a);
			$this->getForm()->setDefaults($a);
		}
		$this->renderForm();
		$this->mostrar($this->_tplForm,$req['display']);
	}

}