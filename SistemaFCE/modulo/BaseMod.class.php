<?php
/**
 * 
 * @author lucas.vidaguren
 * @since 06/10/2008
 */

require_once("clases/utils/Session.class.php"); 
require_once('visual/smarty/libs/Smarty.class.php');
require_once('visual/xajax/xajax_core/xajax.inc.php');

class BaseMod {
	
    var $smarty;
    var $_skinName; 
    var $_orderListado;
    var $_sentidoOrderListado;
    
    var $_menuModTplPath;
    
    var $session;
    
    var $xajax;
    
    var $errors;
    
    var $_tilePath;
    
    var $usuario;
    
    
    function BaseMod($skin='default') {
        
        $this->session = new Session();
        
        $this->_skinName = $skin;
        
        $this->initSmarty();
        
        $this->xajax = new xajax();
        
        $this->_orderListado = $_SESSION[get_class($this)]['sort'];
        $this->_sentidoOrderListado = $_SESSION[get_class($this)]['sortSentido'];
        
        $this->_tilePath = 'decorators/default.tpl';
        
        //metodos de xajax (se debe llamar a processRequest para que esto funcione)
        $this->xajax->register(XAJAX_FUNCTION,array('hideMensaje',&$this,'hideMensaje'));
	}
    
    protected function initSmarty()
    {
    	$systemRoot = dirname(dirname(dirname(__FILE__)));
        
        $this->smarty = new Smarty(); // Handler de smarty
        $this->smarty->template_dir = $systemRoot.'/skins/'.$this->_skinName; // configuro directorio de templates
        $this->smarty->compile_dir = $systemRoot.'/tmp/skins/templates_c'; // configuro directorio de compilacion
        $this->smarty->cache_dir = $systemRoot.'/tmp/skins/cache'; // configuro directorio de cache
        $this->smarty->config_dir = $systemRoot.'/skins/configs'; // configuro directorio de configuraciones
        
        $this->smarty->assign('skin',$this->_skinName);
        $this->smarty->assign('relative_images',"skins/{$skin}/images");
        $this->smarty->assign('version',configuracion::version);
        $this->smarty->assign('skinPath',$systemRoot.'/skins/'.$this->_skinName);
        //$this->smarty->assign('nombre_usuario',$_SESSION['nombreOperador']);
        $this->smarty->assign('appName','CV Docentes');
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
        $this->smarty->assign('id_usuario_actual',$this->session->session_data[$this->session->usr_id_label]);
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
        
        $this->smarty->Display($this->_tilePath);
    }
    
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
    
    function getFiltro($req){}
    
    function redirectHomeModulo()
    {
        header("Location: {$_SERVER['PHP_SELF']}?mod={$_GET['mod']}");
        exit();
    }
    
    function redirectHomeSistema()
    {
        setcookie('mod',null);
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
    
    function setMiembros($req) { }
    
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
                $this->form($req['item']);   
                break;
            case "modif":
                if(!empty($_POST))
                {
                    $this->modificacion($req);                    
                    $this->redirectHomeModulo();
                }                
                $this->form($req['item']);
                break;
            case "baja":
                $this->baja($req['item']);
                $this->redirectHomeModulo();
                break; 
            case "info":
                $this->info($req['item']);
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
    
    function fetch($tpl)
    {
    	return $this->caracteres_html($this->smarty->fetch($tpl));
    }
    
    function displayMensaje(&$xajaxObjResponse,$mensaje,$className='message')
    {
    	
        $xajaxObjResponse->script("clearTimeout(tMsg)");
        $xajaxObjResponse->assign("message","innerHTML", "<div style='float:right; font-size:5px;'><button onclick='xajax_hideMensaje()'>X</button></div>".$this->caracteres_html($mensaje));
        $xajaxObjResponse->assign("message","className", $className);
        $xajaxObjResponse->script("tMsg = setTimeout('xajax_hideMensaje()',3000)");
    }
    
    function displayError(&$xajaxObjResponse,$mensaje)
    {
        $this->displayMensaje($xajaxObjResponse,$mensaje,'error');
    }
    
    function hideMensaje()
    {
    	// Instantiate the xajaxResponse object
        $objResponse = new xajaxResponse();
        
        $objResponse->script("clearTimeout(tMsg)");
        $objResponse->assign("message","className", "");
        $objResponse->assign("message","innerHTML", "");
        
        return $objResponse;
    }
    
    /* funciones abstractas */
    function alta($req){}
    function baja($idItem){}
    function lista(){}
    function form($idItem){}
    function modificacion(){}
}
