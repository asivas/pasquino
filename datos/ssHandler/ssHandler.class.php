<?php
    /**
    * Se define la clase provee acceso via usuario y contraseña
    * 
    * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    *
    * @package      Auth
    * @version      0.1
    */		

    require_once("datos/logger/sqlLogger.class.php");
    /**
    * Esta clase provée acceso via usuario y contraseña
    * 
    * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    *
    * @package      datos
    * @subpackage      Auth
    * @version      0.1
    */		
	class ssHandler {
        
        /**
         * @var boolean determina si hacer refresh si hace login via post o no 
         */
        var $refreshAfterLogin;
         
		/**
		 * @var string nombre de la sesion de php
		 */
		var $sessionName;
		
		/**
		* @var integer la cantidad de minutos que dura la sesion
		*/
		var $cookie_min;
		
		/**
		* @var string guarda el ultimo mensaje de error
		*/
		var $error;
		
		/**
		* @var bool Determina si la contraseña debe ser case-sensitive
		*/
		var $passCaseSensitive;
		
		/**
		 * @var object maneja los logs
		 */
		var $logobj;
		
        /**
         * @var object Objeto de clase Auth o derivado de auth que controla
         * la autenticación por diferntes metodos
         */
        var $auth;
		
		/**
		* constructor de la clase
		* inicia las variables de clase
		* @param object $auth referencia a un objeto auth que define como 
        * se usará la autenticación
		*/
		function ssHandler($auth) 
		{
            $this->auth = $auth;
            $this->initMembers();
            $this->initSessionData();
		}
        
        /**
         * Inicializa las variables miembro a definir en la construcción de la clase 
         */
        function initMembers()
        {
        	$this->logobj = null;
            $this->cookie_min= 15;
            $this->refreshAfterLogin = true;
            $this->passCaseSensitive = true;
        }
		
		/**
		* Setea el tiempo de la coockie e inicia la sesion
		*/
		function initSessionData()
		{   
            $this->auth->setSessionName($this->sessionName);
            $this->auth->setExpire($this->cookie_min*60);
            
			// Fecha en el pasado
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            
            // siempre modificado
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            
            // HTTP/1.1
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            
            // HTTP/1.0
            header("Pragma: no-cache");
		}	
        
        private function loggingIn()
        {
        	return isset($_POST[$this->auth->getPostUsernameField()],$_POST[$this->auth->getPostPasswordField()]);
        }
		
		/**
		* Registra las variables de sesion de usuario si el usuario y la contraseña son correctos
		* @param string $username
		* @param string $password
		* @return bool
		*/
		function LogIn($username = "",$password = "")
		{   
            $this->auth->start();
            if($this->IsLoged())
            {   
                if($this->loggingIn() && $this->refreshAfterLogin)
                {
                    if(isset($_SERVER['SERVER_PORT']))
                        $port = ":{$_SERVER['SERVER_PORT']}";
                    $loc = "http://$_SERVER[SERVER_NAME]{$port}$_SERVER[PHP_SELF]";
                    if(!empty($_SERVER['QUERY_STRING'])) $loc .= '?'.$_SERVER['QUERY_STRING'];
                    header("Location: $loc");
                    exit();
                }
                return true;
            }								
			return false;			
		}	
        
		/**
		* Verifica si hay un usuario logueado en la maquina cliente que esté logueado
		* @return bool
		*/
		function IsLoged()
		{
            if($this->auth->getAuth())
            {
                return true;
            }
            return false;
		}
        
		/**
		* Cierra la sesion del usuario
		*/
		function LogOut()
		{	
            if($this->IsLoged() && isset($this->logobj))
			    $this->logobj->log(date("Y-m-d H:i")." - {$_SESSION[$this->usr_label]}: Cerró sessión",CH_LOG_NOTICE);
			
            $_SESSION = array();
			
            $this->auth->logout();
            
			if (isset($_COOKIE[session_name()])) {
               setcookie(session_name(), '', time()-42000, '/');
            }
			session_destroy();
		}
        
        /**
         * Si existe el idUsuario en el Auth devuelve el idUsuario del usuario logueado
         */
        function getIdUsuario()
        {
            if(method_exists($this->auth,'getUserId'))
                return $this->auth->getUserId();
        }
	}
