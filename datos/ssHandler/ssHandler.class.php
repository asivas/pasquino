<?
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
		
		//var $user;
		//var $pass:
		/**
		* @var string tiene la tabla que usa para chequear el usuario y la contraseña
		*/
		var $table;
		/**
		* @var string tiene el titulo del campo de usuario
		*/
		var $usr_label;
		/**
		* @var string tiene el titulo del campo de password
		*/
		var $pass_label;
		/**
		* @var string tiene el nombre de campo de id de usuario		
		*/
		var $usr_id_label;
		/**
		* @var integer el id del usuario logueado
		*/
		var $usr_id;
		
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
		 * @var object se comunica con la base de datos
		 */
		var $db;
		
		/**
		* constructor de la clase
		* inicia las variables de clase
		* @param object $base referencia a la conexión (ADODB)
		*/
		function ssHandler($base) 
		{
		    if(isset($base)) $this->db = $base;
		    $this->logobj = new sqlLogger($this->db);
			//$this->InitSession();//debe llamarse explicitamente el InitSession
			$this->table = "admin";
			$this->usr_id_label = "id";
			$this->usr_label = "usr";
			$this->pass_label = "pass";
			$this->cookie_min= 15;
            $this->refreshAfterLogin = true;
			$this->passCaseSensitive = true;
		}
		
		/**
		* Setea el tiempo de la coockie e inicia la sesion
		*/
		function initSession()
		{
		    session_name($this->sessionName);
			session_set_cookie_params($this->cookie_min*60);
			session_start();
			
			// Fecha en el pasado
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            
            // siempre modificado
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            
            // HTTP/1.1
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            
            // HTTP/1.0
            header("Pragma: no-cache");
            
            $this->session_data = $_SESSION;
		}	
		
		/**
		* Registra las variables de sesion de usuario si el usuario y la contraseña son correctos
		* @param string $username
		* @param string $password
		* @return bool
		*/
		function LogIn($username = "",$password = "")
		{
			if($this->IsLoged())
            {
                $this->usr_id = $_SESSION[$this->usr_id_label];
                return true;
            }

			if($username != "" && $password!= "")
			{
				return $this->CheckUser($username,$password,true);
			}
			//print_r($_POST);
			if(isset($_POST[$this->usr_label],$_POST[$this->pass_label]))
			{ 
				if($this->CheckUser($_POST[$this->usr_label],$_POST[$this->pass_label],true))
				{
					if(isset($this->logobj))
					    $this->logobj->log(date("Y-m-d H:i")." - {$_SESSION[$this->usr_label]}: Inicio sessión $_SERVER[REMOTE_ADDR]",CH_LOG_NOTICE);
					
					if($this->refreshAfterLogin)
                    {
                        //como hizo el login para quee si actualiza no vuelva a enviar los datos
						//cargo de nuevo la página actual sin las cosas de $_POST
						$loc = "http://$_SERVER[SERVER_NAME]$_SERVER[PHP_SELF]";
						if(!empty($_SERVER['QUERY_STRING'])) $loc .= '?'.$_SERVER['QUERY_STRING'];
						//if(!headers_sent()) no uso esto porque uso buffer en el PHP
                        //el true se va a retornar en el refresh luego de $this->IsLogeg
						header("Location: $loc");
                    }
                    else
                    {
                    	return true;
                    }
				}
			}					
			return false;			
		}	
        
		/**
		* Verifica si hay un usuario logueado en la maquina cliente que esté logueado
		* @return bool
		*/
		function IsLoged()
		{
			$ret = isset($_SESSION[$this->usr_label],$_SESSION[$this->pass_label]);
			if($ret)
	    	{
	    	    $this->usr_id = $_SESSION[$this->usr_id_label];
			    $this->usr_name = $_SESSION[$this->usr_label];
			    $this->password = $_SESSION[$this->pass_label];
			}
			return  $ret;
		}
		/**
		* Cierra la sesion del usuario
		*/
		function LogOut()
		{
			//unset($_SESSION[$this->usr_label],$_SESSION[$this->pass_label]);
			if($this->IsLoged() && isset($this->logobj))
			    $this->logobj->log(date("Y-m-d H:i")." - {$_SESSION[$this->usr_label]}: Cerró sessión",CH_LOG_NOTICE);
			$_SESSION = array();
			
			if (isset($_COOKIE[session_name()])) {
               setcookie(session_name(), '', time()-42000, '/');
            }
			session_destroy();
		}
		/**
		* Verifica que el usuario exista y que su contraseña sea valida
		* @param string $usr
		* @param string $pass
        * @param boolean $rec_id graba el id en la variable $this->usr_id
		* @return bool
		*/
		function CheckUser($usr,$pass,$rec_id = false)
		{
			global $lang;
			$db = $this->db;
			if($this->passCaseSensitive)
			    $sql = "SELECT `$this->usr_label`,`$this->pass_label`,`$this->usr_id_label` FROM `$this->table` WHERE `$this->usr_label` = '$usr' AND `$this->pass_label` = MD5('$pass')";
			else
			    $sql = "SELECT `$this->usr_label`,`$this->pass_label`,`$this->usr_id_label` FROM `$this->table` WHERE `$this->usr_label` = '$usr' AND `$this->pass_label` = '$pass'";
			$res = $db->Execute($sql);
			//print $sql;
			if(!$res) trigger_error($db->ErrorMsg(),E_USER_ERROR);
			$numrows = $res->RowCount();
			if($numrows != "0")
			{
				if($rec_id) $this->usr_id = $res->fields[$this->usr_id_label];
                $_SESSION[$this->usr_label] = $_POST[$this->usr_label];
                $_SESSION[$this->pass_label] = md5($_POST[$this->pass_label]);
                $_SESSION[$this->usr_id_label] = $this->usr_id;                
				return true;
			}
			if(!empty($usr) || !empty($pass))
			{
			    $this->error = $lang['loginout_usrpwd_error'];
			    //print $this->error;
			}
			return false;
		}
	}
?>