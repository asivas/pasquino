<?php
    /**
    * Se define la clase provee acceso via usuario y contrase�a
    * 
    * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
    *
    * @package      Auth
    * @version      0.1
    */		

    // en caso de querer usar log por sql el que extiende debería incluir esto: 
    // require_once("datos/logger/sqlLogger.class.php");
    /**
    * Esta clase prov�e acceso via usuario y contrase�a
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
		* @var bool Determina si la contrase�a debe ser case-sensitive
		*/
		var $passCaseSensitive;
		
		/**
		 * @var object maneja los logs
		 */
		var $logobj;
		
        /**
         * @var object Objeto de clase Auth o derivado de auth que controla
         * la autenticaci�n por diferntes metodos
         */
        var $auth;
		
		/**
		* constructor de la clase
		* inicia las variables de clase
		* @param object $auth referencia a un objeto auth que define como 
        * se usar� la autenticaci�n
		*/
		function ssHandler($auth) 
		{
            $this->auth = $auth;
            if (function_exists("session_register_shutdown"))
            	session_register_shutdown();
            $this->initMembers();
            $this->initSessionData();
		}
        
        /**
         * Inicializa las variables miembro a definir en la construcci�n de la clase 
         */
        function initMembers()
        {
        	$this->logobj = null;
        	$this->cookie_min= 15;
        	$this->setIdle(0);
        	$this->refreshAfterLogin = true;
            $this->passCaseSensitive = true;
        }
        
        protected function isStarted () {
        	return !function_exists('session_status') && !session_id() || function_exists('session_status') && session_status() == PHP_SESSION_ACTIVE;
        } 
		
		/**
		* Setea el tiempo de la coockie e inicia la sesion
		*/
		function initSessionData()
		{   
            if(!$this->isStarted())
           		@session_start();
            
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
        
        protected function loggingIn()
        {
        	return isset($_POST[$this->auth->getPostUsernameField()],$_POST[$this->auth->getPostPasswordField()]);
        }
		
		/**
		* Registra las variables de sesion de usuario si el usuario y la contrase�a son correctos
		* @param string $username
		* @param string $password
		* @return bool
		*/
		function LogIn($username = "",$password = "")
		{   
			if(!$this->isStarted())
				$this->initSessionData();
			
			$this->auth->start();
            if($this->IsLoged())
            {   
                if($this->loggingIn() && $this->refreshAfterLogin)
                {
                    if(isset($_SERVER['SERVER_PORT']))
                        $port = ":{$_SERVER['SERVER_PORT']}";
                    $server = $_SERVER['SERVER_NAME'];
                    
                    if(isset($_SERVER["HTTP_X_FORWARDED_HOST"]))
    					$server = $_SERVER["HTTP_X_FORWARDED_HOST"];

                    $path = Configuracion::getGessedAppRelpath();

                    $loc = "http://{$server}{$port}{$path}";
                    if(!empty($_SERVER['QUERY_STRING'])) $loc .= '?'.$_SERVER['QUERY_STRING'];
                    header("Location: $loc");
                    exit();
                }
                return true;
            }								
			return false;			
		}	
        
		/**
		* Verifica si hay un usuario logueado en la maquina cliente que est� logueado
		* @return bool
		*/
		function IsLoged()
		{
			//me aseguro que si la session fue cerrada por alguna causa
			//volver a abrirla para
			if(!$this->isStarted())
				$this->initSessionData();
				
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
			    $this->logobj->log(date("Y-m-d H:i")." - {$_SESSION[$this->usr_label]}: Cerr� sessi�n",CH_LOG_NOTICE);
			
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
        
        private function getStrKey($key)
        {
        	if(!is_array($key))
        		return $key;
        	$strKey="";
    		while(($sk=current($key))!==FALSE)
    		{
    			if(!is_numeric($sk))
    				$sk="'{$sk}'";
    			$strKey.="[{$sk}]";
    			next($key);
    		}
    		return $strKey;
        }
        
        /**
         * setea la cantidad de minutos de tolerancia de inactividad
         */
        function setIdle($minutes,$add = false)
        {
        	$this->auth->setIdle($minutes*60,$add);	
        }
        
        function getIdle()
        {
        	return $this->auth->idle;
        }
        
        
		/**
	 	 * 
	 	 * Limpia la variable definida por key
	 	 * @param mixed $key
	 	 */
	    function clear($key)
	    {
	    	if(!$this->isStarted())
	    		@session_start();
	    	
	    	if(is_array($key))
	    	{
	    		$strKey = $this->getStrKey($key);
	    		if(!empty($strKey))
	    			eval('unset($_SESSION'.$strKey.');');
	    	}
	    	else
	    		unset($_SESSION[$key]);
	    }
	    
	    /**
	     * Agrega un valor a una variable en la sesión
	     * @key mixed clave puede ser un arreglo ordenado de claves o una clave alfanumerica
	     * @val mixed valor a asignar
	     */
	    function set($key,$val)
	    {
	    	if(!$this->isStarted())
	    		@session_start();
	    	
	    	if(is_array($key))
	    	{
	    		$strKey = $this->getStrKey($key);
	    		if(!empty($strKey))
	    			eval('$sessVar =& $_SESSION'.$strKey.';');
	    	}
	    	else
	    		$sessVar =& $_SESSION[$key];
	    		
	    	$sessVar = $val;
	    }
	    
		/**
	     * Agrega un valor a una variable en la sesión 
	     */
	    function get($key)
	    {
	    	if(!$this->isStarted())
	    		@session_start();
	    	
	    	if(is_array($key))
	    	{
	    		$strKey = $this->getStrKey($key);
	    		if(!empty($strKey))
	    			eval('$sessVar = $_SESSION'.$strKey.';');
	    	}
	    	else
	    		$sessVar = $_SESSION[$key];
	    		
	    	return $sessVar;
	    }
	    
	    /**
	     * Cierra la sessión
	     */
	    function close() {
	    	//TODO: Revisar de hacer un cleanup de las variables miembro de esta clase que se
	    	// conlleven con las de $_SESSION
	    	session_write_close();
	    }
	}
