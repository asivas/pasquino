<?php
/**
 *
 * @author lucas.vidaguren
 * @since 06/10/2008
 */

/**
 * El calendario viejo,
 * @deprecated
 */
require_once('visual/jscalendar/FCEcalendar.class.php');
require_once('SistemaFCE/util/Session.class.php');
require_once('visual/xajax/xajax_core/xajax.inc.php');
require_once('datos/debug/DebugFacade.class.php');
require_once('SistemaFCE/modulo/smartyFacade.class.php');
require_once('SistemaFCE/util/properties/PropertiesManager.interface.php');

if(!class_exists('Smarty'))
	require_once('visual/smarty/libs/Smarty.class.php');

if(!class_exists('DaoUsuario')) //Si el sistema implementa otro DaoUsuario no lo defino
    require_once('SistemaFCE/dao/DaoUsuario.class.php');

require_once 'SistemaFCE/modulo/BaseForm.class.php';
require_once 'SistemaFCE/modulo/RESTMod.class.php';

// Pear Log
require_once 'Log.php';


class BaseMod implements PropertiesManager {

    var $smarty;
    var $_skinConfig;
    var $_orderListado;
    var $_sentidoOrderListado;
    var $_menuModTplPath;

    var $session;

    var $xajax;

    var $errors;

    var $_tilePath;

    var $_usuario;

    var $_form;

    var $_formFiltro;

    var $_dateFormat;
    var $_dateTimeFormat;
    var $_timeFormat;

    var $REST;

    protected $jsModulo;

    protected $pasquinoPath;

    /**
     * Arreglo que tiene la lista de los archivos js a agrregar en el head
     * @var array
     */
    protected $jsFilesList;
    /**
     * Arreglo que tiene la lista de los archivos css a agrregar en el head
     * @var array
     */
    protected $cssFilesList;

    /**
     * Log de acciones de sistema.
     * @var PEAR::Log
     */
    protected $logger=null;

    /**
     * @var PropertiesManagaer Manager para manejar propiedades del sistema
     */
    static protected $propertiesManager;

    private  $exitOnMensaje = true;

    protected $atribsMsgOk = array();


    /**
     *
     * Inicializa el BaseMod
     * @param string $skinDirName nombre de la skin/template utilizada
     * @param boolean $conXajax determina si se utilizar� xajax como motor de ajax
     */
    function __construct($skinName=null,$conXajax=true) {
        if(!isset($this->session))
            $this->session = new Session(Configuracion::getAppName());

        $this->_skinConfig = Configuracion::getTemplateConfigByDir($skinName);

		//@deprecated ya no se usa el calendario js, se prefiere el uso de jQuery
        $this->_calendar = new FCEcalendar('/js/jscalendar/', "es", "../../skins/".$this->_skinConfig['dir']."/css/cal", false);

		/*
		 if(function_exists('apache_request_headers'))
        	$this->REST = new RESTMod();
		*/



		//si se puede cargo el usuario
		$this->getUsuario();

        $this->_dateFormat = Configuracion::getDateFormat();
        $this->_dateTimeFormat = Configuracion::getDateTimeFormat();
        $this->_timeFormat = Configuracion::getTimeFormat();

        $this->pasquinoPath = dirname(dirname(__DIR__));

        $this->initSmarty();
        if($conXajax)
            $this->xajax = new xajax(null,'es');

        $this->_orderListado = $_SESSION[get_class($this)]['sort'];
        $this->_sentidoOrderListado = $_SESSION[get_class($this)]['sortSentido'];

        if(method_exists($this->smarty,'getTemplateVars'))
        	$this->_tilePath = $this->smarty->getTemplateVars('pQnDefaultTpl');
        else
        	$this->_tilePath = Configuracion::getDefaultTplPath($skinName);//'decorators/default.tpl';
        //seteo el path de donde está pasquino


        if($conXajax)
        {
            $this->registerXajax();

            $this->xajax->processRequest();

            $this->setTplVar('xajax',$this->xajax->getJavascript('/js'));
        }
        if (Configuracion::getLoggerClass()!= null)
        	$this->logger= Log::factory(Configuracion::getLoggerClass());
	}
    /**
     * Provee una referencia al formulario del modulo
     * Si el formualrio no est� aun creado lo crea
     */
    function getForm()
    {
    	if($this->_form==null)
            $this->crearForm();
        return $this->_form;
    }

    /**
     * Creaci�n del HTML_QuickForm y sus elementos
     */
    protected function crearForm()
    {
    	$this->_form = new BaseForm('form');
    }

    protected function registerXajax()
    {
        //metodos de xajax (se debe llamar a processRequest para que esto funcione)
        $this->xajax->register(XAJAX_FUNCTION,array('hideMensaje',&$this,'hideMensaje'));
    }

    static public function getSmartyObject($skinConfig = null) {
    	$smarty = new Smarty(); // Handler de smarty

    	$systemRoot = Configuracion::getSystemRootDir();
    	$config = Configuracion::getConfigXML();
    	$templates = $config->templates;
    	$skinsDirname = (string)$config->templates['path'];

    	if(empty($skinsDirname))
    		$skinsDirname = "skins";

    	if(isset($skinConfig))
        {
            $skinDirName = (string)$skinConfig['nombre'];
            if(empty($skinDirName))
                $skinDirName = (string)$skinConfig['dir'];
        }
        $skinConfig = Configuracion::getTemplateConfigByDir($skinDirName);
    	$smarty->template_dir = "{$systemRoot}/{$skinsDirname}/{$skinConfig['dir']}"; // configuro directorio de templates
    	$smarty->compile_dir = "{$systemRoot}/tmp/{$skinsDirname}/templates_c"; // configuro directorio de compilacion
    	$smarty->cache_dir = "{$systemRoot}/tmp/{$skinsDirname}/cache"; // configuro directorio de cache
    	$smarty->config_dir = "{$systemRoot}/{$skinsDirname}/configs"; // configuro directorio de configuraciones

    	$smarty->assign("skinsDirname",$skinsDirname);
    	return $smarty;
    }

    protected function initSmarty()
    {
        $this->smarty = $this->getSmartyObject($this->_skinConfig);
        $skinsDirname = $this->getTemplateVar('skinsDirname');

        $publicSkinDir = $this->_skinConfig['wwwdir'];
        if(empty($publicSkinDir))
        	$publicSkinDir = $this->_skinConfig['dir'];
        $this->setTplVar('skin',$publicSkinDir);
        $this->setTplVar('relative_images',"{$skinsDirname}/{$publicSkinDir}/images");
        $this->setTplVar('version',Configuracion::getVersion());
        $this->setTplVar('skinPath',Configuracion::getSystemRootDir()."/{$skinsDirname}/".$this->_skinConfig['dir']);
        $this->setTplVar('appName',Configuracion::getAppName());
		$this->setTplVar('cal_files',$this->_calendar->get_load_files_code());

        $this->setTplVar('dir_images',"{$skinsDirname}/{$publicSkinDir}/images");
        $this->setTplVar('dir_js',"{$skinsDirname}/{$publicSkinDir}/js");

        $this->assingSmartyMenu();

        $this->setTplVar('dateFormat',$this->_dateFormat);
        $this->setTplVar('timeFormat',$this->_timeFormat);
        $this->setTplVar('dateTimeFormat',$this->_dateTimeFormat);

        $this->assignSmartyTplVars();

        $this->setTplVar('facade',new smartyFacade($this));

        $this->setTplVar("ckeditorVersion", '4.4.1');

        $this->setTplVar('usuario',$this->getUsuario());
        $this->setTplVar('id_usuario_actual',$this->session->getIdUsuario());
    }

    protected function assingSmartyMenu() {
    	$mp = $this->getMenuPrincipal();
    	//menu
    	$this->setTplVar('menuItems',$mp);
    	$this->setTplVar('menu',$mp);
    }

    /**
     * Asigna variables
     */
    protected function assignSmartyTplVars() {

    	$tplsPath = "file:{$this->pasquinoPath}/SistemaFCE/tpls";
    	$cssPath = "file:{$this->pasquinoPath}/SistemaFCE/public/css";
    	$jsPath = "file:{$this->pasquinoPath}/SistemaFCE/js";

    	$this->setTplVar("pQnTplsPath","{$tplsPath}");

    	//Opciones de layout estandar
    	$this->setTplVar("pQnBaseTpl","{$tplsPath}/base.tpl");
    	$this->setTplVar("pQnDefaultTpl","{$tplsPath}/default.tpl");
    	$this->setTplVar("pQnAdminTpl","{$tplsPath}/admin/default.tpl");

    	//Partes generales de sistema/template
    	$this->setTplVar("pQnMenuTpl","{$tplsPath}/menu.tpl");
    	$this->setTplVar("pQnHeaderTpl","{$tplsPath}/header.tpl");
    	$this->setTplVar("pQnFooterTpl","{$tplsPath}/footer.tpl");
    	$this->setTplVar("pQnHeadTpl","{$tplsPath}/head.tpl");

    	//Partes estandar de admin
    	$this->setTplVar("pQnHeadAdminTpl","{$tplsPath}/admin/head.tpl");
    	$this->setTplVar("pQnFormFiltroTpl","{$tplsPath}/admin/filtro.tpl");
    	$this->setTplVar("pQnListaTpl","{$tplsPath}/admin/lista.tpl");
    	$this->setTplVar("pQnInfoTpl","{$tplsPath}/admin/info.tpl");
    	$this->setTplVar("pQnFormTpl","{$tplsPath}/admin/form.tpl");
    	$this->setTplVar("pQnListaAccionesTpl","{$tplsPath}/admin/listaAcciones.tpl");
    	$this->setTplVar("pQnPageHeader","{$tplsPath}/admin/pageHeader.tpl");

    	//Lista
    	$this->setTplVar("pQnGridTpl","{$tplsPath}/admin/lista/objGrid.tpl");
    	$this->setTplVar("pQnBotonAltaTpl","{$tplsPath}/admin/botonAlta.tpl");
    	$this->setTplVar("pQnHeaderListaTpl","{$tplsPath}/admin/lista/headerLista.tpl");
    	$this->setTplVar("pQnFooterListaTpl","{$tplsPath}/admin/lista/footerLista.tpl");
    	$this->setTplVar("pQnItemGridTpl","{$tplsPath}/admin/lista/itemGrid.tpl");

    	//Pantallas generales
    	$this->setTplVar("pQnFormLoginTpl","{$tplsPath}/formLogin.tpl");
    	$this->setTplVar("pQnSinPermisosTpl","{$tplsPath}/sinPermisos.tpl");

    	//CSS
    	$this->setTplVar("pQnDefaultCss","/sistemafce/css/default.css");
    	$this->setTplVar("pQnGridCss","/bootstrap/css/bootstrap-responsive.min.css");
    	$this->setTplVar("pQnJQueryCss","/css/jquery/Aristo/Aristo.css");
    	$this->setTplVar("pQnBootstrapCss", "/bootstrap/css/bootstrap.min.css");

    	//JS
    	$this->setTplVar("pQnJQueryJs","/js/jquery/jquery-1.9.1.min.js");
    	$this->setTplVar("pQnJQueryUiJs","/js/jquery/jquery-ui-1.10.0.custom.min.js");
    	$this->setTplVar("pQnBootstrapJs","/bootstrap/js/bootstrap.min.js");
    	$this->setTplVar('pQnBrowserUpdateJs','/sistemafce/js/browser-update.js');
    	
    	// prueba de variables default de las partes estandares (sys-names) de templates las que tiene definido el dtd
    	//TODO: ver si se peude leer del dtd con algo medio simple y armar el arreglo a recorrer
    	$sysNames = array('Base','Default','Lista','Formulario','Info','FormFiltro','Menu','Admin','Head','FormLogin');
    	foreach($sysNames as $sysName)
    	{
    		$sysNameTplFile = Configuracion::findTplPath($this->_skinConfig,$sysName);
    		if(!empty($sysNameTplFile))
    			$this->setTplVar("pQn".$sysName."Tpl",Configuracion::findTplPath($this->_skinConfig,$sysName));
    	}
    }

    protected function setTplVar($tplVar,$value)
    {
		$this->smarty->assign($tplVar,$value);
    }

    /**
     * Genera un arreglo con [url,tag,icon] si el operador tiene permisos
     */
    private function _getMenuItemArray($nombreModulo,$item)
    {
    	if(isset($item['mod']))
    		$nombreModulo = (string)$item['mod'];

    	$accion = (string)$item['accion'];

        $path = Configuracion::getGessedAppRelpath();

    	$murl = "{$path}?mod={$nombreModulo}&accion={$accion}";

    	if(!empty($item['alias']))
    	{
    		$aliasedItemConf = Configuracion::getMenuItemConfByName($item['alias']);
    		$accion = (string)$aliasedItemConf['accion'];
    		$nombreModulo = (string)$aliasedItemConf['mod'];

    		$murl = "{$path}?alias={$item['alias']}";
    	}

    	$tienePermiso = true;
        if(!empty($item->permisos))
        {
            if(!isset($this->_usuario))
            	$tienePermiso = false;
            else
            {
	        	foreach($item->permisos->permiso as $perm)
	                $tienePermiso &= $this->_usuario->tienePermiso((string)$perm);
            }
        }

        $permAccion = $this->checkPermisoAccion($accion,$nombreModulo);
        $tienePermiso &= $permAccion;

        if(!$tienePermiso)
            return null;

        $mtag = (string)$item['tag'];

        $murl = "?mIt={$item['name']}";

        if(!empty($item['url']))
        	$murl = (string)$item['url'];

        if(!empty($item['icon']))
        	$micon = (string)$item['icon'];

        return array('url'=>$murl,'tag'=>$mtag,'icon'=>$micon,'name'=>"{$item['name']}");
    }

    /**
     * Genera a partir de una config de menu un arreglo para crear un menu en smarty
     * @return array Arreglo para que smarty pueda generar el menu definido en $menuConf
     * @param String $nombreModulo Nombre del modulo al cual pertenece el men�
     * @param object $menuConf Configuraci�n en SimpleXML de menu
     */
    private function _getMenuModuloArray($nombreModulo,$menuConf)
    {
        $menuItems = array();
        $menu = $menuConf;
        if(!empty($menu))
        {
            if(($mItem = $this->_getMenuItemArray($nombreModulo,$menu))!=null)
            {
            	$menuItems['_'] = $mItem;
                $c = 0;
	            foreach($menu->menuItem as $item)
	            {
	                if(($mItem = $this->_getMenuItemArray($nombreModulo,$item))==null)
	                    continue;

                    $mItem['id'] = ++$c;

	                $name = (string) $item['name'];
	                if(isset($item->menuItem))
	                    $menuItems[$name] = $this->_getMenuModuloArray($nombreModulo,$item);
	                else
	                    $menuItems[$name] = $mItem;

	           }
            }
        }
        return $menuItems;
    }

    /**
     * @return array Arreglo para que smarty pueda generar el Menu Principal
     */
    protected function getMenuPrincipal()
    {
    	$modulosConfig = Configuracion::getModulosConfig();
    	$c = 0;
        foreach($modulosConfig->modulo as $mod)
        {
            $n = (string)$mod['nombre'];
            $m = $this->_getMenuModuloArray($n,$mod->menuPrincipal);
            if(!empty($m)) {
            	$m['_']['id'] = ++$c;
              	$menuPpal[$n] = $m;
            }
        }
        return $menuPpal;
    }

    /**
     * Obtiene la configuaci�n del m�dulo
     */
    protected function getConfigModulo($nombreMod = null)
    {
    	if(!isset($nombreMod))
            $nombreMod = get_class($this);

        return Configuracion::getConfigModulo($nombreMod);
    }

    protected function addError($strError)
    {
    	$this->errors[] = $strError;
    }

    protected function ajaxNoPermisos(&$objResponse)
    {
        if(isset($objResponse))
            $this->displayError($objResponse,"No tiene permisos suficientes para esa acci�n");
    }

    /**
     * Chequeo de permisos que de haber vencido la sesión en una llamada por xajax
     * muestra un error recarga la pantalla en un tiempo
     * @param $objResponse objeto response de xajax
     * @deprecated como todas las cosas de xajax están tendiendo a sacarse de pasquino por el uso de jQuery
     */
    protected function ajaxCheckPermisos(&$objResponse=null)
    {
        if(!$this->LogIn())
        {
            if(isset($objResponse))
            {
                $this->displayError($objResponse,"Debe reingresar al sistema");
                $objResponse->script("setTimeout('location.href=\'index.php\'',3100)");
            }
            return false;
        }

        return true;
    }

    private function _esPublica($accion,$nombreModulo=null)
    {
    	//si es una de las acciones publicas implementadas en BaseMod ni miro el config
    	if($this->esAccionPublicaDeBase($accion))
    		return true;

    	// chequeo a partir de la config del m�dulo
        $conf = $this->getConfigModulo($nombreModulo);
        //   Busco los permisos para la acci�n
        $acciones = $conf->acciones;
        if(!isset($acciones->accion)) return false;

        foreach($acciones->accion as $acc)
        {
            $nombreAccion = (string)$acc['nombre'];

            if($nombreAccion == $accion)
            {
                $perms = $acc->permisos;
                //si no tiene restricciones cualquiera tiene permisos
                if(!empty($perms->permiso))
                {
                    foreach($perms->permiso as  $p)
                    {
                        $perm = (string)$p;
                        if($perm == 'publico' || $perm == 'publica')
                            return true;
                    }
                }
            }
        }
        return false;
    }

    protected function checkPermisoAccion($accion,$nombreModulo=null,$usuario=null)
    {
        if(!isset($usuario))
        	$usuario = $this->getUsuario();

    	if($this->_esPublica($accion,$nombreModulo))
        	return true;
    	if(!isset($usuario))
            return false;
        // chequeo a partir de la config del m�dulo
        $conf = $this->getConfigModulo($nombreModulo);

        //   Busco los permisos para la acci�n
        $acciones = $conf->acciones;
        if(!isset($acciones->accion)) return false;

        foreach($acciones->accion as $acc)
        {
        	$nombreAccion = (string)$acc['nombre'];

            if($nombreAccion == $accion)
            {
                $tienePermiso = true;
                $perms = $acc->permisos;

                //si no tiene restricciones cualquiera tiene permisos
                if(!empty($perms->permiso))
                    foreach($perms->permiso as  $p)
                    {
                        $perm = (string)$p;
                        $tienePermiso &= $usuario->tienePermiso($perm);
                    }
                return $tienePermiso;
            }
        }
        return false;
    }

    public function formLogin()
    {	if (is_callable(array($this->session, 'loggingIn')) && ($this->session->loggingIn())) {
    		$this->setTplVar("errorLogin",true);
    	}

    	$this->_tilePath = Configuracion::getBaseTplPath($this->_skinConfig['nombre']);
    	if(method_exists($this->smarty,'getTemplateVars'))
    	{
    		$tpl = $this->smarty->getTemplateVars('pQnFormLoginTpl');
    		$this->_tilePath = $this->smarty->getTemplateVars('pQnBaseTpl');
    	}
    	else
    	{
    		$tpl = $this->smarty->get_template_vars('pQnFormLoginTpl');
    		$this->_tilePath = $this->smarty->get_template_vars('pQnBaseTpl');
    	}
    	$this->addJsFile("/sistemafce/js/login.js");
        $this->setTplVar('action',Configuracion::getGessedAppRelpath());
    	$this->mostrar($tpl);
        exit();
    }

    public function sinPermisos()
    {
        $this->_menuModTplPath = '';
        if(method_exists($this->smarty,'getTemplateVars'))
        	$tpl = $this->smarty->getTemplateVars('pQnSinPermisosTpl');
        else
        	$tpl = $this->smarty->get_template_vars('pQnSinPermisosTpl');

        $this->mostrar($tpl,$_REQUEST['display']);
        die();
    }

    protected function LogIn()
    {
    	$bLoged = $this->session->LogIn();
    	$usr = $this->_usuario;

    	$this->setTplVar('usuario',$this->getUsuario());

    	if($usr!=$this->getUsuario())
    		$this->assingSmartyMenu();

    	return $bLoged;
   	}

   	/**
   	 * Evalua si una accion dada es una accion publica y definida completamente en el baseMod
   	 * Este tipo de funciones no hace falta que estén en el config
   	 * @param string $accion la accion sobre la que se está consultado
   	 * @return boolean
   	 */
   	protected final function esAccionPublicaDeBase($accion) {

   		$accionesPublicasDeBaseMod = array(
   				'checkSessionTimeout'
   		);

   		return array_search($accion, $accionesPublicasDeBaseMod) !== FALSE;
   	}


    protected function checkPermisos($req)
    {
     	if(!$this->_esPublica($req['accion']))
    	{
    		$isLogedIn = $this->LogIn();

    		if(!$isLogedIn)
	        {
	            $this->formLogin();
	        }

	        if( !$this->ajaxCheckPermisos() || !$this->checkPermisoAccion($req['accion']) )
	        {
	        	$this->sinPermisos();
	        }
    	}

        return true;
    }

    /**
     * Devuelve la ruta de un tile (plantilla) a partir de un
     * tipo de display
     * @param string $displayType
     */
    protected function getTilePathForDisplayType($displayType) {
    	if(!isset($displayType) || $displayType=='full')
    		return $this->_tilePath;

    	return "";
    }

    /**
     * Agrega un archivo (nombre de archivo) js para inluirlos de manera dinamica
     * @param string $jsFile
     * @param string $sortKey
     * @param string $version
     */
    protected function addJsFile($jsFile,$sortKey=null,$version=null) {
		if(isset($version))
			$jsFile = "$jsFile?v={$version}";    	
    	if($sortKey !== null)
    	{
    		if($sortKey===0 && is_array($this->jsFilesList))
    			array_unshift($this->jsFilesList, $jsFile);
    		else
    			$this->jsFilesList[$sortKey] = $jsFile;
    	}
    	else    	
    		$this->jsFilesList[] = $jsFile;
    }

    /**
     * Genera y asigna en smarty una variable a partir del listado de los jsFileList
     */
    private function assignHeadJs()
    {
    	$jsIncludes = "";
    	
    	$defaultJsTemplateVars = array("pQnJQueryJs","pQnBrowserUpdateJs","pQnJQueryUiJs","pQnBootstrapJs");
    	
    	foreach($defaultJsTemplateVars as $jsFileNameVar)
    	{
    		if(($jsFileName = $this->getTemplateVar($jsFileNameVar))!='')
    			$jsIncludes .= "\n	<script type=\"text/javascript\" src=\"{$jsFileName}\"></script>";
    	}
    	
    	if(($jsBaseName = $this->getTemplateVar('jsModulo'))!='')
    		$jsIncludes .= "\n	<script type=\"text/javascript\" src=\"js/{$jsBaseName}.js\"></script>";
    	
    	//TODO: aca se puede hacer optimización de los archivos listados concatenandolos y
    	// poniendolos minified
    	if(!empty($this->jsFilesList) && is_array($this->jsFilesList))
    	{
    		foreach($this->jsFilesList as $jsFileName)
    		{
    			$jsIncludes .= "\n	<script type=\"text/javascript\" src=\"{$jsFileName}\"></script>";
    		}
    		//TODO: agarrar de configuración el tpl que esté como head, corroborar que tenga jsIncludes
    		// si, no meterle a la fuerza la variable {$jsIncludes}
    	}
    	$this->setTplVar('jsIncludes',$jsIncludes);
    }

    /**
     * Agrega un archivo (nombre de archivo) css para inluirlos de manera dinamica
     * @param string $cssFile
     * @param string $sortKey
     * @param string $version
     */
    protected function addCssFile($cssFile,$sortKey=null,$version=null) {
    	if(isset($version))
    		$cssFile = "$cssFile?v={$version}";
    	if(!empty($sortKey)) // FIX: pregunto si no es empty porque sino le pone un index de cadena vacia
    		$this->cssFilesList[$sortKey] = $cssFile;
    	else
    		$this->cssFilesList[] = $cssFile;
    }

    /**
     * Genera y asigna en smarty una variable a partir del listado de los cssFileList
     */
    private function assignHeadCss()
    {
    	$cssIncludes = "";

    	$defaultCssTemplateVars = array("pQnBootstrapCss","pQnDefaultCss","pQnThemeCss","pQnGridCss","pQnJQueryCss","cssModulo");

    	foreach($defaultCssTemplateVars as $cssFileNameVar)
    	{
    		if(($cssFileName = $this->getTemplateVar($cssFileNameVar))!='')
    			$cssIncludes .= "\n	<link rel=\"stylesheet\" href=\"{$cssFileName}\" type=\"text/css\" />";
    	}

    	//TODO: aca se puede hacer optimización de los archivos listados concatenandolos y
    	// poniendolos minified
    	if(!empty($this->cssFilesList) && is_array($this->cssFilesList))
    	{
    		foreach($this->cssFilesList as $cssFileName)
    		{
    			$cssIncludes .= "\n	<link rel=\"stylesheet\" href=\"{$cssFileName}\" type=\"text/css\" />";
    		}

    		//TODO: agarrar de configuración el tpl que esté como head, corroborar que tenga jsIncludes
    		// si, no meterle a la fuerza la variable {$jsIncludes}
    	}
    	$this->setTplVar('cssIncludes',$cssIncludes);
    }

    /**
     *
     * Muestra por pantalla el tpl con el tipo de display seleccionado o si no hay tipo se muestra
     * con la plantilla (tile) default
     * @param unknown_type $tpl
     * @param unknown_type $type
     */
    protected function mostrar($tpl,$type=null)
    {
        if(!empty($this->errors))
            $this->setTplVar('errores',$this->errors);

        if (isset($this->jsModulo))
        	$this->addDateVersionedJsFile("js/{$this->jsModulo}.js",0);
        //$this->setTplVar("jsModulo",$this->jsModulo);
        
        $this->assignHeadJs();
        $this->assignHeadCss();

        $this->setTplVar('menuMod',$this->_menuModTplPath);
        $this->setTplVar('pantalla',$tpl);
        if($this->xajax!=null)
        	$this->setTplVar('ajax',$this->xajax->getJavascript('js/'));


		$disp = $this->getTilePathForDisplayType($type);

		

        if(empty($disp))
        	$disp = $tpl;

        $this->smarty->Display($disp);
    }

    /**
     * Retorna un string el criterio y senditdo de ordenamiento en los listados a partir del request
     * @param array $req Arreglo del request
     * @return string Cadena de "criterio sentido" de ordenamiento (tipo SQL)
     */
    protected function getOrder($req){
        if(!empty($req['sort']))
        {	
        	if($req['sort']!=$this->_orderListado)
            {
                $this->_orderListado = $req['sort'];
                $this->_sentidoOrderListado = "ASC";
            }
            elseif(strpos($this->_orderListado, "ASC")===FALSE && strpos($this->_orderListado, "DESC")===FALSE) { // si no se envío un orden en el sort                 
            	if($this->_sentidoOrderListado == "ASC")
                    $this->_sentidoOrderListado = "DESC";
                else
                    $this->_sentidoOrderListado = "ASC";
            }
        }
        else
        {
            $this->_orderListado = null;
            $this->_sentidoOrderListado = null;
            return null;
        }

        if(isset($req['sortSentido']))
        {
        	$this->_sentidoOrderListado = $req['sortSentido'];
        }

        $_SESSION[get_class($this)]['sort'] = $this->_orderListado;
        $_SESSION[get_class($this)]['sortSentido'] = $this->_sentidoOrderListado;

        $this->setTplVar('sort',$this->_orderListado);
        $this->setTplVar('sortSentido',$this->_sentidoOrderListado);

        $order = '';
        $multi = split(',',$this->_orderListado);
        foreach($multi as $orden)
        {
        	if(!empty($order)) $order .= ',';
        	if(stripos($orden, "ASC")===FALSE && stripos($orden, "DESC")===FALSE)
            	$order .= "{$orden} {$this->_sentidoOrderListado}";
        	else 
        		$order .= "{$orden}";
        }

        return $order;
    }

    /**
     * Genera un objeto Criterio a partir de los filtros pasados por request
     * @param array $req
     */
    protected function getFiltro($req){
    	if(isset($this->mainDao))
    		return $this->mainDao->getCriterioBase();
    	return new Criterio();
    }

    /**
     * redirecciona a la home del modulo
     */
    protected function redirectHomeModulo($req=null)
    {
        if(!isset($req)) $req = $_GET;

        $path = Configuracion::getGessedAppRelpath();

        header("Location: {$path}?mod={$req['mod']}");
        exit();
    }

    /**
     * Redirecciona a la home del sistema
     */
    protected function redirectHomeSistema()
    {
        setcookie('mod',null);

        $path = Configuracion::getGessedAppRelpath();

        header("Location: {$path}");
        exit();
    }

    protected function getAccionPredeterminada()
    {
    	return Configuracion::getAccionPerdeterminada(get_class($this));
    }


    /**
     * Asigna valores a las variables miembro que guardan informaci�n recibida de request
     * @param array $req
     */
    protected function setMiembros($req) { }

    /**
     * Ejecuta la acci�n del modulo, a partir de la variable accion recibida por request
     * @param array $req
     */
    function ejecutar($req)
    {
    	if(empty($req["accion"])) $req["accion"] = $this->getAccionPredeterminada();

    	$accion = $req["accion"];

        if(isset($req['logout']) || $accion == 'logout')
        {
            $this->session->LogOut();
            $this->redirectHomeSistema();
        }


        if(!is_null($this->REST) && $this->REST->esUriRecurso())
        {
        	$rec = $this->REST->ejecutar($req);
        }
        else
        {
        	$this->checkPermisos($req);
        	$this->setMiembros($req);
        	$this->setTplVar('accion',$accion);

	        $metodoAccion = "accion".ucfirst($accion);

	        if(!method_exists($this,$metodoAccion) && $accion != $this->getAccionPredeterminada())
	        {
	        	$req['accion'] = $this->getAccionPredeterminada();
	            $this->ejecutar($req);
	            return;
	        }

	        $this->$metodoAccion($req);
        }

    }

    /**
     * Ejecuta una acci�n de alta de un elemento
     * lo guarda llamando al metodo alta si viene por post,
     * sino muestra el formulario
     */
    protected function accionAlta($req)
    {
        if(!empty($_POST) && $_POST['accion']=='alta')
        {
            $this->alta($_POST);
            $this->redirectHomeModulo($req);
        }
        $this->form($req);
    }

    /**
     * Ejecuta una acci�n de modificaci�n de un elemento,
     * lo guarda llamando al metodo modificacion si viene por post,
     * sino muestra el formulario
     */
    protected function accionModif($req)
    {
        if(!empty($_POST) && $_POST['accion']=='modif')
        {
        	$this->modificacion($req);
            $this->redirectHomeModulo($req);
        }
        $this->form($req);
    }

    /**
     * Ejecuta una acci�n de baja de un elemento llamando al metodo baja
     * luego redirecciona a la home del m�dulo
     */
    protected function accionBaja($req)
    {
    	$this->baja($req);
        $this->redirectHomeModulo();
    }

    /**
     * Ejecuta una acci�n de informaci�n de un elemento llamando al metodo info
     */
    protected function accionInfo($req)
    {
    	$this->info($req);
    }

    /**
     * Ejecuta una acci�n de listar los elementos llamando al metodo lista
     */
    protected function accionListar($req)
    {
    	 $this->lista($req);
    }

    /* funciones abstractas */
    /**
     * Metodo llamado cuando se ejecuta la acci�n alta via post
     * @param array $req arreglo de variables enviadas en el request
     */
    protected function alta($req){}

    /**
     * Metodo llamado cuando se ejecuta la acci�n baja
     * @param array $req arreglo de variables enviadas en el request
     */
    protected function baja($req){}

    /**
     * Metodo llamado cuando se ejecuta la acci�n listar
     * @param array $req arreglo de variables enviadas en el request
     */
    protected function lista($req=null){
    	if(method_exists($this,'listar'))
          $this->listar($req);
    }

    /**
     * Metodo llamado cuando se ejecutan las acciones alta o modif sin ser enviado por post
     * @param array $req arreglo de variables enviadas en el request
     */
    protected function form($req=null){}

    /**
     * Metodo llamado cuando se ejecuta la acci�n modif y es enviado por post
     * @param array $req arreglo de variables enviadas en el request
     */
    protected function modificacion($req){
    	if(method_exists($this,'modif'))
          $this->modif($req);
    }

    protected function caracteres_html($str)
    {
    	return $this->getForm()->caracteres_html($str);
    }

    /**
     * Obtiene el c�digo html de un template utilizando smarty
     * @param string $tpl nombre de archivo del template
     * @return string c�digo html procesado por smarty
     */
    protected function fetch($tpl)
    {
    	return $this->caracteres_html($this->smarty->fetch($tpl));
    }

    /**
     * Muestra un mensaje usando xajax
     * @deprecated los mensajes se usan via jquery
     */
    protected function displayMensaje(&$xajaxObjResponse,$mensaje,$className='message',$xPos=null,$yPos=null,$idDiv='message')
    {
        $xajaxObjResponse->script("if(document.getElementById('{$idDiv}')==null) { var _body = document.getElementsByTagName('body') [0]; var _div = document.createElement('div'); _div.id = '{$idDiv}'; _body.appendChild(_div);}");
    	$xajaxObjResponse->script("clearTimeout(tMsg)");
        $xajaxObjResponse->assign($idDiv,"innerHTML", "<div style='float:right; font-size:5px;'><button onclick='xajax_hideMensaje()'>X</button></div>".$this->caracteres_html($mensaje));
        $xajaxObjResponse->assign($idDiv,"className", $className);
        if(isset($xPos))
            $xajaxObjResponse->assign($idDiv,"style.left", $xPos+"px");
        if(isset($yPos))
            $xajaxObjResponse->assign($idDiv,"style.top", $yPos+"px");
        $xajaxObjResponse->script("tMsg = setTimeout('xajax_hideMensaje()',3000)");
    }

    /**
     * Muestra un mensaje de error usando xajax
     * @deprecated los mensajes se usan via jquery
     */
    protected function displayError(&$xajaxObjResponse,$mensaje)
    {
        $this->displayMensaje($xajaxObjResponse,$mensaje,'error');
    }

    /**
     * Oculta un mensaje mostrado utilizando xajax
     * @deprecated los mensajes se usan via jquery
     */
    function hideMensaje($idDiv='message')
    {
    	// Instantiate the xajaxResponse object
        $objResponse = new xajaxResponse();

        $objResponse->script("clearTimeout(tMsg)");
        $objResponse->assign($idDiv,"className", "");
        $objResponse->assign($idDiv,"innerHTML", "");

        return $objResponse;
    }

   /**
     * Crea el input con el calendario selector de fecha
     * @return String con el html listo para insertar en el template
     * @deprecated como se usa jquery ya no tiene sentido
     */
    protected function getCalendarInput($name, $value = "", $format = null, $baseID = null)
	{
		if(is_null($format)) $format = $this->_dateFormat;
        return $this->getForm()->getCalendarInput($this->_calendar,$name,$value,$format,$baseID);
	}

    /**
     * Genera un arreglo con opciones para un select
     * @param array $listaElementos Lista de elementos que deben tener getId y getNombre definidos
     * @param integre $vacio si se debe crear una opcion vacia
     * @param integre $otro si se debe crear una opcion de "Otro", si est� definido el nro ser� el id
     * @return array arreglo asociativo id => nombre
     * @deprecated 1.3- 05/06/2009
     */
    protected function getArregloSelect($listaElementos,$vacio=true,$otro=null,$otroLabel='Otra')
    {
    	return $this->getForm()->getArregloSelect($listaElementos,$vacio,$otro,$otroLabel);
    }

    /**
     * Obtiene el c�digo HTML de un input select
     * @param string $name
     * @param array $options opciones compatibles con las opciones de HTML_QuickForm_select
     * @param mixed $attributes atributos compatibles con los atributos de HTML_QuickForm_select
     * @deprecated 1.3- 05/06/2009
     */
	protected function getSelectInput($name,$options,$attributes,$selected=null)
	{
        return $this->getForm()->getSelectInput($this->smarty,$name,$options,$attributes,$selected);
	}


    /**
     * Asigna el formulairo pasado para smarty a la variable fomrulario de $this->smarty
     * @param string $nombreVarSmarty Nombre de la variable que ser� asignada en smarty con el contenido del formulario
     */
    protected function renderForm($nombreVarSmarty = 'formulario',$form=null)
    {
    	if(!isset($form))
            $form = $this->getForm();

    	$rf = $form->renderSmarty($this->smarty);
        $this->setTplVar($nombreVarSmarty,$rf);
        return $rf;
    }

    /**
     * Devuelve el usuario actual que usa el sitio
     * @return Ambigous <NULL, Usuario>
     */
    protected function getUsuario()
    {
    	if(!isset($this->_usuario) && $this->session->IsLoged())
    	{
    		$entidadUsuario = Configuracion::getEntidadUsuarioClass();
	    	$claseDaoUsuario = 'Dao'.$entidadUsuario;
	    	$daoU = $claseDaoUsuario::getInstance();
	    	$this->_usuario = $daoU->findById($this->session->getIdUsuario());
    	}

    	return $this->_usuario;
    }

    /**
     * Decodifica cualquier tag html encontrado en los strings del arreglo de entrada
     * @param array $array Arreglo con datos de formulrio con html de simbolos embebido
     */
     function arrayHtmlDecode($array)
	{
		foreach($array as $key => $value)
		{
			$array[$key] = html_entity_decode($value,ENT_NOQUOTES,'ISO-8859-1');
		}
		return $array;
	}

	/**
	 *
	 * Define que el modulo tendrá un archivo js con el nombre de $aJsName
	 * @param string $aJsName el nombre del archivo js del modulo sin la extensi�n
	 */
	protected function setJsModulo($aJsName){
		$this->jsModulo=$aJsName;
	}

	/**
	 * Envía una respuesta JSON al cliente del objeto (o arreglo) $jsonable
	 * @param mixed $jsonable objeto o array para enviarlo como json
	 */
	protected function responseJson($jsonable,$finishExecution=true) {
		if (!headers_sent())
			header('Content-type: application/json');
		$json = json_encode($jsonable);
		if($finishExecution && $this->exitOnMensaje)
			die($json);
		else
			print $json;
	}

	static public function resaltar($str,$filtro,$parameter = array('background-color' => '#FFFFBF'))
	{
		$str = htmlentities($str,ENT_COMPAT | ENT_HTML5,  'UTF-8');
		$filtro = htmlentities($filtro);
		$res = $str;
		//var_dump($filtro);
		if(isset($filtro) && trim($filtro)!="")
		{
			$filtros = explode(" ",$filtro);
			foreach($filtros as $f)
			{
				$offset=0;
				$lengthf = strlen($f);
				$lengthStr = strlen($str);
				//var_dump($str);
				while(($pos = stripos($str,$f,$offset))!==FALSE && $offset<strlen($str))
				{
					//var_dump($pos);
					$res = substr($str,0,$pos).chr(254).
					substr($str,$pos,$lengthf).chr(255).substr($str,$pos+$lengthf);
					$str = $res;
					$offset = $pos + $lengthf + 2 ;
				}
			}

			/*
			 * en el caso que el parametro sea un arreglo de parametros:valor
			*/
			if(is_array($parameter))
			{
				$modify = '';
				foreach ($parameter as $modifier => $value)
					$modify .= "{$modifier}:{$value};";
				$res = str_replace(chr(254),"<span style='{$modify}'>",$res);
				$res = str_replace(chr(255),"</span>",$res);
			}
			elseif(is_string($parameter))
			{
				$res = str_replace(chr(254),"<span class='{$parameter}'>",$res);
				$res = str_replace(chr(255),"</span>",$res);
			}
		}
		return $res;
	}

	/**
	 * Envia (haciendo display) un mensaje de status usando el tpl de msgStatus
	 * @param string $status
	 * @param string $mensaje
	 * @param array $otros
	 */
	protected function mensaje($status,$mensaje,$otros=null)
	{
		$this->setTplVar("status",$status);
		$this->setTplVar("msg",$mensaje);
		$this->setTplVar("otros",$otros);

		if(isset($this->smarty->_version))
			$sv = $this->smarty->_version;
		else
		{
			$tmp = explode(" ",Smarty::SMARTY_VERSION);
			$sv = $tmp[1];
		}
		if($sv[0]>2)
			$this->smarty->display('string:<status msg="{$msg}" status="{$status}" {foreach from=$otros key=k item=valor} {$k}="{$valor}" {/foreach}></status>');
		else
		{
			$out = "<status msg=\"{$mensaje}\" status=\"{$status}\"";
			foreach($otros as $k => $valor)
				$out .= "{$k}={$valor} ";
			$out .= "></status>";
			print $out;
		}
		if($this->exitOnMensaje)
			die();
	}

	/**
	 * Agrega un atributo para el proximo mensaje OK que se enviará
	 */
	protected function addAtribMensajeOk($key,$value) {
		$this->atribsMsgOk[$key] = $value;
	}

	/**
	 * Elimina un atributo dada su clave, para el próximo mensaje OK que se enviará
	 * @param string|int $key
	 */
	protected function removeAtribMensajeOk($key) {
		unset($this->atribsMsgOk[$key]);
	}

	/**
	 * Obtiene o genera los atributos del mensajeOK del guardar
	 * @param unknown $aObj
	 * @return multitype:NULL
	 */
	protected function getAtribsMensajeOK() {
		return $this->atribsMsgOk;
	}

	/**
	 * Envia un mensaje de OK en xml usando mensaje
	 * @param string $mensaje el mensaje
	 * @param array $otros arreglo de otros atributos para agregarle al <status>
	 */
	protected function mensajeOK($mensaje,$otros=null)
	{
		if(!isset($otros))
			$otros =  $this->getAtribsMensajeOK();
		else
			$otros = array_merge($otros,$this->getAtribsMensajeOK());

		$this->mensaje("OK", $mensaje,$otros);
		$this->atribsMsgOk=array();
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

	public function accionCheckSessionTimeout() {
		$remainingSeconds = $this->session->getRemainingTime();

		if($remainingSeconds!==FALSE)
		{
			$resp = array('remaining'=>$remainingSeconds);
			if($remainingSeconds<0)
				$resp['expired'] =true;
		}
		else
			$resp = array('noExpire'=>true);

		$this->responseJson($resp);
	}

	protected function log(Entidad &$aEntity,$aAction = null){}

	/**
	 * Setea el PropertiesManager del modulo.
	 * @param PropertiesManager $propertiesManager
	 */
	public function setPropertiesManager(PropertiesManager $propertiesManager) {
		self::$propertiesManager = $propertiesManager;
	}

	/* INTERFACE PropertiesManager */

	static public function getPropertyValue($propertyKey, $dafaultValue = null) {
		if (isset(self::$propertiesManager))
			return self::$propertiesManager->getPropertyValue($propertyKey, $dafaultValue);
		return $dafaultValue;
	}

	static public function setPropertyValue($propertyKey, $value) {
		if (isset(self::$propertiesManager))
			 self::$propertiesManager->setPropertyValue($propertyKey, $value);
	}

	static public function deleteProperty($propertyKey) {
		if (isset(self::$propertiesManager))
			self::$propertiesManager->deleteProperty($propertyKey);
	}

	static public function existsProperty($propertyKey) {
		if (isset(self::$propertiesManager))
			return self::$propertiesManager->existsProperty($propertyKey);
		return false;
	}

	public function setExitOnMensaje($bExit) {
		$this->exitOnMensaje = $bExit;
	}

	/**
	 * Asigna la variable de theme para incluir el archivo css inmediatamente despues de defaultCss
	 * @param string $themeCssFilePath la ruta (client side) del archivo css
	 */
	public function setThemeCss($themeCssFilePath) {
		$this->setTplVar("pQnThemeCss", $themeCssFilePath);
	}

	/**
	 * Obtiene el valor asignado en la template (smarty) a la variable dada
	 * @param string $varname
	 * @return Ambigous <multitype:, NULL>
	 */
	public function getTemplateVar($varname) {
		if(method_exists($this->smarty,'getTemplateVars'))
			return $this->smarty->getTemplateVars($varname);

		return $this->smarty->get_template_vars($varname);
	}
		
	public function getFilemdatetime($filePath) {		
		return date("YmdHis",filemtime($filePath));		
	}
	
	public function addDateVersionedJsFile($jsFile,$sortKey=null) {
		$jsFilePath = Configuracion::getPublicDir().DIRECTORY_SEPARATOR.$jsFile;
		if(file_exists($jsFilePath))
			$this->addJsFile($jsFile,$sortKey,$this->getFilemdatetime($jsFilePath));
	}
	
	public function addDateVersionedCssFile($cssFile,$sortKey=null) {
		$cssFilePath = Configuracion::getPublicDir().DIRECTORY_SEPARATOR.$cssFile;
		if(file_exists($cssFilePath))
			$this->addCssFile($cssFile,$sortKey,$this->getFilemdatetime($cssFilePath));
	}
}
