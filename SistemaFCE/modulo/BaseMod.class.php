<?php
/**
 * 
 * @author lucas.vidaguren
 * @since 06/10/2008
 */

require_once("utils/Session.class.php"); 
require_once('visual/smarty/libs/Smarty.class.php');
require_once('visual/xajax/xajax_core/xajax.inc.php');
require_once("HTML/QuickForm.php");
require_once("HTML/QuickForm/Renderer/ArraySmarty.php");
require_once('utils/calendar/calendar.class.php'); 

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
    
    var $usuario;
    
    var $_form;
    
    var $_formFiltro;
    
    var $_dateFormat;
    var $_dateTimeFormat;
    var $_timeFormat;
    
    function BaseMod($skinDirName=null) {
        
        $this->session = new Session();
        
        $this->_skinConfig = Configuracion::getTemplateConfigByDir($skinDirName);

		$this->_calendar = new DHTML_Calendar('js/jscalendar/', "es", "../../skins/".$this->_skinConfig['dir']."/css/cal", false);
            
        $this->initSmarty();
        
        $this->xajax = new xajax();
        
        $this->_orderListado = $_SESSION[get_class($this)]['sort'];
        $this->_sentidoOrderListado = $_SESSION[get_class($this)]['sortSentido'];
        
        $this->_dateFormat = Configuracion::getDateFormat();
        $this->_dateTimeFormat = Configuracion::getDateTimeFormat();
        $this->_timeFormat = Configuracion::getTimeFormat();
        
        $this->_tilePath = 'decorators/default.tpl';
		$this->_form = new HTML_QuickForm('form','post',$_SERVER.PHP_SELF);
        
        //metodos de xajax (se debe llamar a processRequest para que esto funcione)
        $this->xajax->register(XAJAX_FUNCTION,array('hideMensaje',&$this,'hideMensaje'));
	}
    
    protected function initSmarty()
    {
    	$systemRoot = dirname(dirname(dirname(__FILE__)));
        
        $this->smarty = new Smarty(); // Handler de smarty
        $this->smarty->template_dir = $systemRoot.'/skins/'.$this->_skinConfig['dir']; // configuro directorio de templates
        $this->smarty->compile_dir = $systemRoot.'/tmp/skins/templates_c'; // configuro directorio de compilacion
        $this->smarty->cache_dir = $systemRoot.'/tmp/skins/cache'; // configuro directorio de cache
        $this->smarty->config_dir = $systemRoot.'/skins/configs'; // configuro directorio de configuraciones
        
        $this->smarty->assign('skin',$this->_skinConfig['dir']);
        $this->smarty->assign('relative_images',"skins/{$this->_skinConfig['dir']}/images");
        $this->smarty->assign('version',configuracion::getVersion());
        $this->smarty->assign('skinPath',$systemRoot.'/skins/'.$this->_skinConfig['dir']);
        $this->smarty->assign('appName','CV Docentes');
		$this->smarty->assign('cal_files',$this->_calendar->get_load_files_code());
    }
    
    /**
     * Genera un arreglo con [url,tag] si el operador tiene permisos
     */
    private function _getMenuItemArray($nombreModulo,$item,$operador)
    {
    	if(!empty($item->permisos))
        {
            $tienePermiso = false;
            foreach($item->permisos->permiso as $perm)
            {
                $strPermFunc = "get".(string)$perm;
                $tienePermiso |= $pperador->$strPermFunc();
            }
            
            if(!$tienePermiso)
                return null;
        }

        $mtag = (string)$item['tag'];
        $murl = "{$_SERVER['PHP_SELF']}?mod={$nombreModulo}accion={$item['accion']}";
        if(!empty($item['url']))
            $murl = (string)$item['url'];
        
        return array('url'=>$murl,'tag'=>$mtag);
    }
    
    /**
     * Genera a partir de una config de menu un arreglo para crear un menu en smarty
     * @return array Arreglo para que smarty pueda generar el menu definido en $menuConf
     * @param String $nombreModulo Nombre del modulo al cual pertenece el menú
     * @param object $menuConf Configuración en SimpleXML de menu
     */
    private function _getMenuModuloArray($nombreModulo,$menuConf)
    {	
        $menuItems = array();
        $menu = $menuConf;
        if(!empty($menu))
        {   
            if(($mItem = $this->_getMenuItemArray($nombreModulo,$menu,$this->usuario))==null)
                return $menuItems;
                    
            $menuItems['_'] = $mItem;
            foreach($menu->menuItem as $item)
            { 
                if(($mItem = $this->_getMenuItemArray($nombreModulo,$item,$this->usuario))==null)
                    continue;

                $name = (string) $item['name'];
                if(isset($item->menuItem))
                    $menuItems[$name] = $this->_getMenuModuloArray($item);                 
                else
                    $menuItems[$name] = $mItem;
                    
           }
        }
        return $menuItems;
    }
    
    /**
     * @return array Arreglo para que smarty pueda generar el Menu Principal
     */
    public function getMenuPrincipal()
    {
    	$modulosConfig = Configuracion::getModulosConfig();
        foreach($modulosConfig->modulo as $mod)
        {
            $n = (string)$mod['nombre'];
            $m = $this->_getMenuModuloArray($n,$mod->menuPrincipal);
            if(!empty($m))
              $menuPpal[$n] = $m;
        }
        return $menuPpal;
    }
        
    function addError($strError)
    {
    	$this->errors[] = $strError;
    }
    
    function ajaxNoPermisos(&$objResponse)
    {
        if(isset($objResponse))
            $this->displayError($objResponse,"No tiene permisos suficientes para esa acción");
    }
    
    function ajaxCheckPermisos(&$objResponse=null)
    {
        if(!$this->session->LogIn())
        {   
            if(isset($objResponse))
            {
                $this->displayError($objResponse,"Debe reingresar al sistema");
                $objResponse->script("setTimeout('location.href=\'index.php\'',3100)");
            }
            return false;
        }

        /*
         * Esto debería hacerse cuando exista el DaoUsuario
        $daoU = new DaoUsuario();
        $this->usuario = $u = $daoU->findById($this->session->getIdUsuario());
        $this->smarty->assign('usuario',$u);

        $this->smarty->assign('nombre_usuario',"{$u->apellido}, {$u->nombre}");
        $this->smarty->assign('id_usuario_actual',$this->session->getIdUsuario());
        */
        
        return true;
    }
    
    function checkPermisos()
    {
    	if(!$this->session->LogIn())
        {   
            $this->_tilePath = 'decorators/base.tpl';
            $this->mostrar('formLogin.tpl');
            exit();
        }
        
        if(!$this->ajaxCheckPermisos())
        {
        	$this->_menuModTplPath = '';
            $this->mostrar('sinPermisos.tpl');
            die();
        }
        
        return true;
    }
    
    function mostrar($tpl)
    {
        if(!empty($this->errors))
            $this->smarty->assign('errores',$this->errors);
        
        $this->smarty->assign('menuMod',$this->_menuModTplPath);
        $this->smarty->assign('pantalla',$tpl);
        $this->smarty->assign('ajax',$this->xajax->getJavascript('js/'));
        $this->_form = new HTML_QuickForm('form','post',$_SERVER.PHP_SELF);
        $this->smarty->Display($this->_tilePath);
    }
    
    /**
     * Retorna un string el criterio y senditdo de ordenamiento en los listados a partir del request
     * @param array $req Arreglo del request
     * @return string Cadena de "criterio sentido" de ordenamiento (tipo SQL)
     */
    function getOrder($req){
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
        
        $_SESSION[get_class($this)]['sort'] = $this->_orderListado;
        $_SESSION[get_class($this)]['sortSentido'] = $this->_sentidoOrderListado; 
        
        return "{$this->_orderListado} {$this->_sentidoOrderListado}";  
    }
    
    /**
     * Genera un objeto Criterio a partir de los filtros pasados por request
     * @param array $req
     */
    function getFiltro($req){}
    
    /**
     * redirecciona a la home del modulo 
     */
    function redirectHomeModulo()
    {
        header("Location: {$_SERVER['PHP_SELF']}?mod={$_GET['mod']}");
        exit();
    }
    
    /**
     * Redirecciona a la home del sistema 
     */
    function redirectHomeSistema()
    {
        setcookie('mod',null);
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
    
    /**
     * Asigna valores a las variables miembro que guardan información recibida de request
     * @param array $req
     */
    function setMiembros($req) { }
    
    /**
     * Ejecuta la acción del modulo, a partir de la variable accion recibida por request
     * @param array $req 
     */
    function ejecutar($req)
    {
    	$accion = $req["accion"];
        if(isset($req['logout']) || $accion == 'logout')
        {
            $this->session->LogOut();
            $this->redirectHomeSistema();
        }
        
        $this->checkPermisos();
        $this->setMiembros($req);
        
        $this->smarty->assign('accion',$accion);
        switch($accion)
        {
            case "alta":
                if(!empty($_POST))
                {
                    $this->alta($_POST);
                    $this->redirectHomeModulo();
                }
                $this->form(/*$req*/);   
                break;
            case "modif":
                if(!empty($_POST))
                {
                    $this->modificacion($req);                    
                    $this->redirectHomeModulo();
                }                
                $this->form($req);
                break;
            case "baja":
                $this->baja($req);
                $this->redirectHomeModulo();
                break; 
            case "info":
                $this->info($req);
                break;
            case "listar":
            default:
                $this->lista();
        }   
    }
    
    function caracteres_html($str)
    {
    	$tr = array('á'=>'&aacute;','é'=>'&eacute;','í'=>'&iacute;','ó'=>'&oacute;','ú'=>'&uacute;',
                    'Á'=>'&Aacute;','É'=>'&eacute;','Í'=>'&iacute;','Ó'=>'&oacute;','Ú'=>'&uacute;',
                    'ñ'=>'&ntilde;','Ñ'=>'&Ntilde;','ü'=>'&uuml;','Ü'=>'&Uuml;');
        return strtr($str,$tr);
    }
    
    /**
     * Obtiene el código html de un template utilizando smarty
     * @param string $tpl nombre de archivo del template
     * @return string código html procesado por smarty  
     */
    function fetch($tpl)
    {
    	return $this->caracteres_html($this->smarty->fetch($tpl));
    }
    
    /**
     * Muestra un mensaje usando xajax
     */
    function displayMensaje(&$xajaxObjResponse,$mensaje,$className='message')
    {
    	
        $xajaxObjResponse->script("clearTimeout(tMsg)");
        $xajaxObjResponse->assign("message","innerHTML", "<div style='float:right; font-size:5px;'><button onclick='xajax_hideMensaje()'>X</button></div>".$this->caracteres_html($mensaje));
        $xajaxObjResponse->assign("message","className", $className);
        $xajaxObjResponse->script("tMsg = setTimeout('xajax_hideMensaje()',3000)");
    }
    
    /**
     * Muestra un mensaje de error usando xajax
     */
    function displayError(&$xajaxObjResponse,$mensaje)
    {
        $this->displayMensaje($xajaxObjResponse,$mensaje,'error');
    }
    
    /**
     * Oculta un mensaje mostrado utilizando xajax 
     */
    function hideMensaje()
    {
    	// Instantiate the xajaxResponse object
        $objResponse = new xajaxResponse();
        
        $objResponse->script("clearTimeout(tMsg)");
        $objResponse->assign("message","className", "");
        $objResponse->assign("message","innerHTML", "");
        
        return $objResponse;
    }

   /**
     * Crea el input con el calendario selector de fecha
     * @return String con el html listo para insertar en el template
     */
    function getCalendarInput($name, $value = "", $format = null)
	{
		if(is_null($format)) $format = $this->_dateFormat;
		ob_start();
    	$this->_calendar->make_input_field(
    	// calendar options go here; see the documentation and/or calendar-setup.js
        array('firstDay'       => 1, // show Monday first
              'showsTime'      => false,
              'singleClick'    => true,
              'showOthers'     => true,
              'ifFormat'       => $format
             ),
        // field attributes go here
        array('name'        => $name,
              'value'       => $value));
		return ob_get_clean();
	}
    
    /* funciones abstractas */
    function alta($req){}
    function baja($idItem){}
    function lista(){}
    function form($idItem){}
    function modificacion(){}
}
