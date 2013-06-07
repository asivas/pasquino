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

	function __construct($form, $dao, $skinName=null, $listaTplPath=null, $formTplPath=null, $tilePathName = 'Admin', $sessionHandler=null)
	{
		if(isset($sessionHandler))	$this->session = $sessionHandler;

		parent::__construct($skinName,false); // como se usa mayormente jquery se pasa por defecto el conXajax en false

		$this->_form = $form;
		$this->mainDao = $dao;

		$this->_tplLista = $listaTplPath;
		$this->_tplForm = $formTplPath;

		$tConf = Configuracion::getTemplateConfigByNombre($skinName);
		$this->_tilePath = Configuracion::findTplPath($tConf,$tilePathName);

		if(!isset($this->_tilePath) && method_exists($this->smarty,'getTemplateVars')) //smarty3 y el tilePath está vacio
			$this->_tilePath = $this->smarty->getTemplateVars('pQn'.$tilePathName.'Tpl');

		//busco el dir esperado por defecto de los tpls del modulo		
		$lowerNombreEntidad = strtolower( str_replace("Mod", "", get_class($this)) );
		
		//si no está seteado el tpl lista cargo el de pasquino
		if(!isset($this->_tplLista)) $this->_tplLista = $this->smarty->getTemplateVars('pQnListaTpl');
		//si no está seteado el tpl lista cargo un path por defecto con el dir y lista.tpl
		if(!isset($this->_tplLista)) $this->_tplLista = "{$lowerNombreEntidad}/lista.tpl";
		
		//si no está seteado el tpl form cargo el de pasquino
		if(!isset($this->_tplForm)) $this->_tplForm = $this->smarty->getTemplateVars('pQnFormTpl');
		//si no está seteado el tpl form cargo un path por defecto con el dir y form.tpl
		if(!isset($this->_tplForm)) $this->_tplForm = "{$lowerNombreEntidad}/form.tpl";

		//TODO: ver de poner el js por defecto del mod
		if(isset($nada)) //TODO: borrar esta linea y la siguiente, está para que autocomplete
			$this->mainDao = new DaoBase();
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
			$this->log($aObj);
			$this->mensajeOK("{$strInfo} fue guardado con exito",array('id'=>$aObj->getId()));
		}
		else
			$this->mensajeERR("{$strInfo} no se pudo guardar ". $this->mainDao->getLastError());
	}

	/**
	 * Envía un mensaje de errror ante fallo de validación del formulario
	 * @param BaseForm $form Opcional el formulario al que fallá la validación, si no se pasa se usa el del modulo
	 */
	protected function sendValidateErrorMsg($form=null) {
		$strErr = "No se cumplen las reglas de validación:";
		$f = $this->renderForm('formulario',$form);
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
	protected function alta($req) {

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
	protected function modificacion($req) {
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
	protected function baja($req){
		$id = $this->getItemId($req);

		$aObj = $this->mainDao->findById($id);
		if(method_exists($aObj, "toString") && $aObj->toString()!='')
			$strInfo =  $aObj->toString();
		else
			$strInfo = "el elemento de tipo ".get_class($aObj);

		if ($this->mainDao->deletePorId($id)){
			$this->log($aObj,'ELIMINO');
			$this->mensajeOK("Se pudo eliminar {$strInfo} #{$id} ");
		}else
			$this->mensajeERR("No se puede eliminar {$strInfo} [Error {$this->mainDao->getLastError()}]");
	}

	/* (non-PHPdoc)
	 * @see BaseMod::lista()
	 */
	protected function lista($req){
		$aObjs = $this->mainDao->findBy($this->getFiltro($req),$req['sort']);

		$nombreClase="";
		if(count($aObjs)>0)
		{
			$aObj = current($aObjs);
			$nombreClase = get_class($aObj);
		}
		$this->smarty->assign('lista'.$nombreClase,$aObjs);
		$this->smarty->assign('listaColumnas',$this->getColumnsList());
		$this->smarty->assign('laLista',$aObjs);
		$this->smarty->assign('claseEntidad',$nombreClase);
		$this->smarty->assign('modName',strtolower( str_replace("Mod", "", get_class($this)) ));
		$this->mostrar($this->_tplLista,$req['display']);
	}

	/**
	 * Devuelve la lista de todas las propiedades de la Entidad Principal
	 * en forma de arreglo para mostrar el listado de administracion
	 *
	 * @return array 'columnName' => 'nombre_propiedad'
	 */
	protected function getColumnsList()
	{
		//TODO: generar columnas con toda la lista de propiedades de la entidad
		return array();
	}

	/* (non-PHPdoc)
	 * @see BaseMod::form()
	 */
	protected function form($req){
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

	/**
	 * Devuelve la ruta del tpl de la lista de acciones para efectuar sobre una entidad
	 * @return string
	 */
	protected function getListaAccionesTpl() {
		return $this->smarty->getTemplateVars('pQnListaAccionesTpl');
	}

	/**
	 * Carga el pedazo de html de los botones de accion para la administración de una entidad
	 * @param Entidad $entidad la entidad sobre la cual se harán las posibles acciones
	 * @param string $listaAccionesTpl la ruta del tpl de la lista de acciones, por defecto se pide a getListaAccionesTpl
	 * @return Ambigous <string, void, boolean, string, mixed>
	 */
	function getGridAccionesItem(Entidad $entidad,$listaAccionesTpl=null)
	{
		if(!isset($listaAccionesTpl))
			$listaAccionesTpl = $this->getListaAccionesTpl();
		$lowerModName = strtolower( str_replace("Mod", "", get_class($this)) );
		$this->smarty->assign('entidad',$entidad);
		$this->smarty->assign('modName',$lowerModName);
		return $this->smarty->fetch($listaAccionesTpl);
	}

	/**
	 * Edicion y Baja se loguean como warning
	 * Altas como notice
	 * @see BaseMod::log()
	 */
	protected function log(Entidad &$aEntity,$aAction = null){
		if ($this->logger != null){
			if ($aAction==null){
				if ($aEntity->getEdicion()){
					$aAction="MODIFICO";
				}else{
					$aAction="AGREGO";
					$this->logger->notice('El usuario # '.$this->session->getIdUsuario() .' '.$aAction.' '. $aEntity->toString());
					return ; 				
				}
			}
			$this->logger->warning('El usuario # '.$this->session->getIdUsuario() .' '.$aAction.' '. $aEntity->toString());
			return ;
		}
	}
}