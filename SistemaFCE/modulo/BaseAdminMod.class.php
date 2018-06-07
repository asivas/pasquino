<?php
// si no se definió en otro lado cargo el smarty 3, para que no se cargue el 2 en baseMod
if(!class_exists('Smarty'))
	require_once('visual/smarty3/libs/Smarty.class.php');

require_once 'SistemaFCE/modulo/BaseMod.class.php';
require_once 'formato/xls/PHPExcel.php';

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
	protected $defaultPaginationLimitCount;
	protected $columnsList;

	function __construct($form, $dao, $skinName=null, $listaTplPath=null, $formTplPath=null, $tilePathName = 'Admin', $sessionHandler=null)
	{
		if(isset($sessionHandler))	$this->session = $sessionHandler;

		parent::__construct($skinName,false); // como se usa mayormente jquery se pasa por defecto el conXajax en false

		$this->_form = $form;
		$this->mainDao = $dao;

		$this->_tplLista = $listaTplPath;
		$this->_tplForm = $formTplPath;

		$this->defaultPaginationLimitCount = 30;

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

		$this->initListColumns();
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
	 * Agrega los atributos necesarios para eniar el mensaje de guardar
	 * @param objet $aObj objeto que se estaría guardando
	 */
	protected function addAtribsMensajeOkGuardar($aObj) {
		$this->addAtribMensajeOk('id', $aObj->getId());
		$this->addAtribMensajeOk('entidad', get_class($aObj));
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
			$aObj->setEdicion(false);
			$this->log($aObj);
			$this->addAtribsMensajeOkGuardar($aObj);
			$this->mensajeOK("{$strInfo} fue guardado con exito");
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
			// en caso de que el id no sea simple o no esté
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

	protected function getDefaultPaginationLimitCount() {
		return $this->defaultPaginationLimitCount;
	}

	protected function getPaginationLimitCount($req) {
		if(isset($req['count'])) return $req['count'];

		if($req['display']=='xls')
			return null;

		return $this->getDefaultPaginationLimitCount();
	}

	protected function getPageOffset($req) {
		if(isset($req['pag'])) {
			$limit = $this->getPaginationLimitCount($req);
			return $limit * ($req['pag']-1);
		}
		return null;
	}

	/* (non-PHPdoc)
	 * @see BaseMod::lista()
	 */
	protected function lista($req){
		$filtro = $this->getFiltro($req);
		$limitCount = $this->getPaginationLimitCount($req);
		$aObjs = $this->getListElements($filtro, $req, $limitCount);

		if($req['display']=='xls')
			$this->descargarListaExcel($aObjs, $req);

		$nombreClase="";
		if(count($aObjs)>0)
		{
			$aObj = current($aObjs);
			$nombreClase = get_class($aObj);
		}
		$this->smarty->assign('lista'.$nombreClase,$aObjs);
		$this->smarty->assign('listaColumnas',$this->getColumnsList($req));
		$this->smarty->assign('laLista',$aObjs);
		$this->smarty->assign('claseEntidad',$nombreClase);
		$this->smarty->assign('modName',strtolower( str_replace("Mod", "", get_class($this)) ));

		$this->smarty->assign('paginationCurrentPage',isset($req['pag'])?$req['pag']:1);
		$this->smarty->assign('paginationLimitCount',$limitCount);
		$this->smarty->assign('paginationCantEntidades',$this->mainDao->count($filtro));
		$this->smarty->assign('paginationFiltro',$filtro);

		$this->mostrar($this->_tplLista,$req['display']);
	}

	/**
	 * Obtiene la lista de elementos para mostrar en el listado, dados un filtro y las variables de request
	 * @param Criterio $filtro el criterio de filtro del listado
	 * @param array $req variables de request preprocesadas
	 * @param integer $limitCount limite de elementos que se mostrarán por página
	 */
	protected function getListElements($filtro,$req,$limitCount=null) {
		return $this->mainDao->findBy($filtro,$this->getOrder($req),$limitCount,$this->getPageOffset($req));
	}

	/**
	 * Devuelve la lista de todas las propiedades de la Entidad Principal
	 * en forma de arreglo para mostrar el listado de administracion
	 *
	 * @return array 'columnName' => 'nombre_propiedad'
	 */
	protected function getColumnsList($req = null)
	{
		return $this->columnsList;
	}

	/* (non-PHPdoc)
	 * @see BaseMod::form()
	 */
	protected function form($req){
		$id = $this->getItemId($req);
		if($this->validateId($req))
		{
			$a = $this->mainDao->findById($id);
			if($a != null)
			{
				$this->smarty->assign(get_class($a),$a);
				$this->getForm()->setDefaults($a);
			}
			else
				die;
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
					$this->logger->notice('El usuario '.$this->getUsuario()->toString().' '.$aAction.' '. $aEntity->toString());
					return ;
				}
			}
			$this->logger->warning('El usuario '.$this->getUsuario()->toString().' '.$aAction.' '. $aEntity->toString());
			return ;
		}
	}

	public function addPropertyColumnToList($propertyName,$propertyLabel) {
		$this->columnsList[$propertyLabel] = $propertyName;

	}

	protected function addColumnAcciones() {
		$this->addPropertyColumnToList('gridAccionesItem', 'Acciones');
	}

	/**
	 * Inicializa la lista de columnas del listado estandar (agregando la columna de acciones)
	 */
	protected function initListColumns() {
		if(!isset($this->columnsList))
			$this->resetListColumns();

		$this->addColumnAcciones();
	}

	/**
	 * Reinicializa dejando vacía la lista de columnas para el listado estandar
	 */
	protected function resetListColumns() {
		$this->columnsList = array();
	}

	/**
	 * Genera el listado en fomrmato de excel y devuelve el objeto PHPExcel que lo representa
	 * @param array $aObjs listado de entidades
	 * @param array $req
	 */
	protected function getListaExcel($aObjs,$req) {
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);


		$objHeaderStyle = new PHPExcel_Style();
		$objHeaderStyle->getFont()->setBold(true);

		$xlscol = 0;
		$xlscolName = "A";
		//escribo los encabezados
		foreach($this->columnsList as $label => $prop)
		{
			if(strtolower($prop)!='gridaccionesitem')
			{
				$objPHPExcel->getActiveSheet()
				->getColumnDimensionByColumn($xlscol)
				->setAutoSize(true);


				$objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow($xlscol++,1,$label);
				$xlscolName++;
			}
		}

		$objPHPExcel->getActiveSheet()->setSharedStyle($objHeaderStyle,"A1:{$xlscolName}1");

		$xlsrow = 2;
		foreach ($aObjs as $ent) {
			$xlscol = 0;
			foreach($this->columnsList as $label => $prop)
			{
			    $val = "";
				$getFn = 'get'.ucfirst($prop);
				if($getFn != 'getGridAccionesItem')
				{
					if(method_exists($this, $getFn))
						$val = $this->$getFn($ent);
					else if(method_exists($ent, $getFn))
						$val = $ent->$getFn();

					$objPHPExcel->getActiveSheet()
						->setCellValueByColumnAndRow($xlscol++,$xlsrow,$val);
				}

			}
			$xlsrow++;
		}

		$objPHPExcel->getProperties()->setTitle("Lista ".get_class($ent));

		$objPHPExcel->getActiveSheet()->setTitle("Lista ".get_class($ent));

		return $objPHPExcel;
	}
	/**
	 * Descarga la lista en formato excel
	 */
	protected function descargarListaExcel($aObjs,$req) {

		$objPHPExcel = $this->getListaExcel($aObjs,$req);

		$title = $objPHPExcel->getProperties()->getTitle();
		if(isset($req['title']))
			$title = $req['title'];

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * Asigna un titulo al modulo (se le asigna en el template como tituloModulo)
	 */
	protected function setTitulo($titulo) {
		$this->setTplVar('tituloModulo', $titulo);
	}

	/**
	 * Asigna una descripción al modulo (se le asigna en el template como descripcionModulo)
	 */
	protected function setDescripcion($descripcion) {
		$this->setTplVar('descripcionModulo', $descripcion);
	}
}