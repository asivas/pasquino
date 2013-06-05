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

if(!class_exists('Smarty'))
	require_once('visual/smarty/libs/Smarty.class.php');

if(!class_exists('DaoUsuario')) //Si el sistema implementa otro DaoUsuario no lo defino
    require_once('SistemaFCE/dao/DaoUsuario.class.php');

require_once 'SistemaFCE/modulo/BaseForm.class.php';
require_once 'SistemaFCE/modulo/RESTMod.class.php';

class BaseMod {

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

		$this->REST = new RESTMod();

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

            $this->smarty->assign('xajax',$this->xajax->getJavascript('/js'));
        }
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

    protected function initSmarty()
    {
    	$systemRoot = Configuracion::getSystemRootDir();

    	$config = Configuracion::getConfigXML();
        $templates = $config->templates;
        $skinsDirname = (string)$config->templates['path'];

        if(empty($skinsDirname))
        	$skinsDirname = "skins";

        $this->smarty = new Smarty(); // Handler de smarty
        $this->smarty->template_dir = "{$systemRoot}/{$skinsDirname}/{$this->_skinConfig['dir']}"; // configuro directorio de templates
        $this->smarty->compile_dir = "{$systemRoot}/tmp/{$skinsDirname}/templates_c"; // configuro directorio de compilacion
        $this->smarty->cache_dir = "{$systemRoot}/tmp/{$skinsDirname}/cache"; // configuro directorio de cache
        $this->smarty->config_dir = "{$systemRoot}/{$skinsDirname}/configs"; // configuro directorio de configuraciones

        $publicSkinDir = $this->_skinConfig['wwwdir'];
        if(empty($publicSkinDir))
        	$publicSkinDir = $this->_skinConfig['dir'];
        $this->smarty->assign('skin',$publicSkinDir);
        $this->smarty->assign('relative_images',"{$skinsDirname}/{$publicSkinDir}/images");
        $this->smarty->assign('version',Configuracion::getVersion());
        $this->smarty->assign('skinPath',$systemRoot."/{$skinsDirname}/".$this->_skinConfig['dir']);
        $this->smarty->assign('appName',Configuracion::getAppName());
		$this->smarty->assign('cal_files',$this->_calendar->get_load_files_code());

        $this->smarty->assign('dir_images',"{$skinsDirname}/{$publicSkinDir}/images");
        $this->smarty->assign('dir_js',"{$skinsDirname}/{$publicSkinDir}/js");

        $this->assingSmartyMenu();

        $this->smarty->assign('dateFormat',$this->_dateFormat);
        $this->smarty->assign('timeFormat',$this->_timeFormat);
        $this->smarty->assign('dateTimeFormat',$this->_dateTimeFormat);

        $this->assignSmartyTplVars();

        $this->smarty->assign('facade',new smartyFacade($this));

        $this->smarty->assign('usuario',$this->_usuario);
        $this->smarty->assign('id_usuario_actual',$this->session->getIdUsuario());
    }
    
    protected function assingSmartyMenu() {
    	$mp = $this->getMenuPrincipal();
    	//menu
    	$this->smarty->assign('menuItems',$mp);
    	$this->smarty->assign('menu',$mp);
    }

    /**
     * Asigna variables
     */
    protected function assignSmartyTplVars() {

    	$tplsPath = "file:{$this->pasquinoPath}/SistemaFCE/tpls";
    	$cssPath = "file:{$this->pasquinoPath}/SistemaFCE/public/css";
    	$jsPath = "file:{$this->pasquinoPath}/SistemaFCE/js";
    	
    	$this->smarty->assign("pQnTplsPath","{$tplsPath}");
    	
    	//Opciones de layout estandar
    	$this->smarty->assign("pQnBaseTpl","{$tplsPath}/base.tpl");
    	$this->smarty->assign("pQnDefaultTpl","{$tplsPath}/default.tpl");
    	$this->smarty->assign("pQnAdminTpl","{$tplsPath}/admin/default.tpl");

    	//Partes generales de sistema/template
    	$this->smarty->assign("pQnMenuTpl","{$tplsPath}/menu.tpl");
    	$this->smarty->assign("pQnHeaderTpl","{$tplsPath}/header.tpl");
    	$this->smarty->assign("pQnFooterTpl","{$tplsPath}/footer.tpl");
    	$this->smarty->assign("pQnHeadTpl","{$tplsPath}/head.tpl");

    	//Partes estandar de admin
    	$this->smarty->assign("pQnHeadAdminTpl","{$tplsPath}/admin/head.tpl");
    	$this->smarty->assign("pQnFormFiltroTpl","{$tplsPath}/admin/filtro.tpl");
    	$this->smarty->assign("pQnListaTpl","{$tplsPath}/admin/lista.tpl");
    	$this->smarty->assign("pQnInfoTpl","{$tplsPath}/admin/info.tpl");
    	$this->smarty->assign("pQnFormTpl","{$tplsPath}/admin/form.tpl");
    	$this->smarty->assign("pQnListaAccionesTpl","{$tplsPath}/admin/listaAcciones.tpl");
    	$this->smarty->assign("pQnPageHeader","{$tplsPath}/admin/pageHeader.tpl");
    	 
    	
    	
    	$this->smarty->assign("pQnGridTpl","{$tplsPath}/admin/lista/objGrid.tpl");
    	$this->smarty->assign("pQnBotonAltaTpl","{$tplsPath}/admin/botonAlta.tpl");

    	//Pantallas generales
    	$this->smarty->assign("pQnFormLoginTpl","{$tplsPath}/formLogin.tpl");
    	$this->smarty->assign("pQnSinPermisosTpl","{$tplsPath}/sinPermisos.tpl");
		
    	//CSS
    	$this->smarty->assign("pQnDefaultCss","/sistemafce/css/default.css");
    	$this->smarty->assign("pQnGridCss","/bootstrap/css/bootstrap-responsive.min.css");
    	$this->smarty->assign("pQnJQueryCss","/css/jquery/Aristo/Aristo.css");
    	 
    	//JS
    	$this->smarty->assign("pQnJQueryJs","/js/jquery/jquery-1.9.1.min.js");
    	$this->smarty->assign("pQnJQueryUiJs","/js/jquery/jquery-ui-1.10.0.custom.min.js");
    	 
    	
    	
    	// prueba de variables default de las partes estandares (sys-names) de templates las que tiene definido el dtd
    	//TODO: ver si se peude leer del dtd con algo medio simple y armar el arreglo a recorrer
    	$sysNames = array('Base','Default','Lista','Formulario','Info','FormFiltro','Menu','Admin','Head','FormLogin');
    	foreach($sysNames as $sysName)
    	{
    		$sysNameTplFile = Configuracion::findTplPath($this->_skinConfig,$sysName);
    		if(!empty($sysNameTplFile))
    			$this->smarty->assign("pQn".$sysName."Tpl",Configuracion::findTplPath($this->_skinConfig,$sysName));
    	}
    }

    protected function setTplVar($tplVar,$value)
    {
		$this->smarty->assign($tplVar,$value);    	
    }
    
    /**
     * Genera un arreglo con [url,tag] si el operador tiene permisos
     */
    private function _getMenuItemArray($nombreModulo,$item)
    {
    	$tienePermiso = false;
        if(!empty($item->permisos))
        {
            foreach($item->permisos->permiso as $perm)
            {
                $tienePermiso |= $this->_usuario->tienePermiso((string)$perm);
            }
        }
        $permAccion = $this->_checkPermisoAccion((string)$item['accion'],$nombreModulo);
        $tienePermiso |= $permAccion;

        if(!$tienePermiso)
            return null;

        $mtag = (string)$item['tag'];
        $murl = "{$_SERVER['PHP_SELF']}?mod={$nombreModulo}&accion={$item['accion']}";
        if(!empty($item['url']))
            $murl = (string)$item['url'];

        return array('url'=>$murl,'tag'=>$mtag);
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
            if(($mItem = $this->_getMenuItemArray($nombreModulo,$menu))==null)
                return $menuItems;

            $menuItems['_'] = $mItem;
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
        return $menuItems;
    }

    /**
     * @return array Arreglo para que smarty pueda generar el Menu Principal
     */
    protected function getMenuPrincipal()
    {
    	$modulosConfig = Configuracion::getModulosConfig();
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

    private function _checkPermisoAccion($accion,$nombreModulo=null)
    {
        if($this->_esPublica($accion,$nombreModulo))
        	return true;
    	if(!isset($this->_usuario))
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
                        $tienePermiso &= $this->_usuario->tienePermiso($perm);
                    }
                return $tienePermiso;
            }
        }
        return false;
    }

    public function formLogin()
    {
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
         
        $this->mostrar($tpl);
        die();
    }

    protected function LogIn()
    {
    	$bLoged = $this->session->LogIn();
    	$usr = $this->_usuario;
    	
    	$this->smarty->assign('usuario',$this->getUsuario());
    	
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

	        if( !$this->ajaxCheckPermisos() || !$this->_checkPermisoAccion($req['accion']) )
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
    protected function getTilePathForDisplayType($displayType) {return "";}

    /**
     * Agrega un archivo (nombre de archivo) js para inluirlos de manera dinamica
     * @param string $jsFile
     * @param string $sortKey
     */
    protected function addJsFile($jsFile,$sortKey=null) {
    	$this->jsFilesList[$sortKey] = $jsFile;
    }

    /**
     * Genera y asigna en smarty una variable a partir del listado de los jsFileList
     */
    private function assignHeadJs()
    {
    	$jsInlcudes = "";
    	//TODO: aca se puede hacer optimización de los archivos listados concatenandolos y
    	// poniendolos minified
    	if(!empty($this->jsFilesList) && is_array($this->jsFilesList))
    	{
    		foreach($this->jsFilesList as $jsFileName)
    		{
    			$jsInlcudes .= "\n	<script type=\"text/javascript\" src=\"{$jsFileName}\"></script>";
    		}
    		//TODO: agarrar de configuración el tpl que esté como head, corroborar que tenga jsIncludes
    		// si, no meterle a la fuerza la variable {$jsIncludes}
    	}
    	$this->smarty->assign('jsIncludes',$jsIncludes);
    }

    /**
     * Agrega un archivo (nombre de archivo) css para inluirlos de manera dinamica
     * @param string $cssFile
     * @param string $sortKey
     */
    protected function addCssFile($cssFile,$sortKey=null) {
    	
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
    	$this->smarty->assign('cssIncludes',$cssIncludes);
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
            $this->smarty->assign('errores',$this->errors);

        $this->assignHeadJs();
        $this->assignHeadCss();

        $this->smarty->assign('menuMod',$this->_menuModTplPath);
        $this->smarty->assign('pantalla',$tpl);
        if($this->xajax!=null)
        	$this->smarty->assign('ajax',$this->xajax->getJavascript('js/'));

        if(!isset($type) || $type=='full')
        {
        	$disp = $this->_tilePath;
        }
		else
			$disp = $this->getTilePathForDisplayType($type);

		if (isset($this->jsModulo))
			$this->smarty->assign("jsModulo",$this->jsModulo);

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
            else{
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

        $this->smarty->assign('sort',$this->_orderListado);
        $this->smarty->assign('sortSentido',$this->_sentidoOrderListado);

        $order = '';
        $multi = split(',',$this->_orderListado);
        foreach($multi as $orden)
        {
        	if(!empty($order)) $order .= ',';
            $order .= "{$orden} {$this->_sentidoOrderListado}";
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

        header("Location: {$_SERVER['PHP_SELF']}?mod={$req['mod']}");
        exit();
    }

    /**
     * Redirecciona a la home del sistema
     */
    protected function redirectHomeSistema()
    {
        setcookie('mod',null);
        header("Location: {$_SERVER['PHP_SELF']}");
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
		
        
        if($this->REST->esUriRecurso())
        {
        	$rec = $this->REST->ejecutar($req);
        }
        else
        {
        	$this->checkPermisos($req);
        	$this->setMiembros($req);
        	$this->smarty->assign('accion',$accion);

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
        $this->smarty->assign($nombreVarSmarty,$rf);
        return $rf;
    }

    /**
     * Devuelve el usuario actual que usa el sitio
     * @return Ambigous <NULL, Usuario>
     */
    protected function getUsuario()
    {
    	if(!isset($this->_usuario))
    	{
    		$entidadUsuario = Configuracion::getEntidadUsuarioClass();
	    	$claseDaoUsuario = 'Dao'.$entidadUsuario;
	    	$daoU = new $claseDaoUsuario();
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
		header('Content-type: application/json');
		$json = json_encode($jsonable);
		if($finishExecution)
			die($json);
		else
			print $json;
	}

	static public function resaltar($str,$filtro,$parameter = array('background-color' => '#FFFFBF'))
	{
		$str = htmlentities($str);
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
		$this->smarty->assign("status",$status);
		$this->smarty->assign("msg",$mensaje);
		$this->smarty->assign("otros",$otros);

		if(isset($this->smarty->_version))
			$sv = $this->smarty->_version;
		else
		{
			$tmp = explode(" ",Smarty::SMARTY_VERSION);
			$sv = $tmp[1];
		}
		if($sv[0]>2)
			$this->smarty->display('string:<status msg="{$msg}" status="{$status}" {foreach from=$otros key=k item=valor} {$k}={$valor} {/foreach}></status>');
		else
		{
			$out = "<status msg=\"{$mensaje}\" status=\"{$status}\"";
			foreach($otros as $k => $valor)
				$out .= "{$k}={$valor} ";
			$out .= "></status>";
			print $out;
		}
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
}
