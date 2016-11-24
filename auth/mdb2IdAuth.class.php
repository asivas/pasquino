<?php
require_once "Auth/Auth.php";

abstract class Mdb2IdAuth extends Auth{
    
    var $id_label;
    var $username_label;
    var $password_label;  
    var $userTable;
    protected $dbHost;
    protected $dbName;
    protected $dbUser;
    protected $dbPassword;
    
    function Mdb2IdAuth($loginFunction = "", $showLogin = false, $options=null) {
        
        if(empty($this->userTable))
            $this->userTable = "usuarios";
        if(empty($this->password_label))
            $this->password_label = "password";

        //TODO: taer las cosas de conexion de configuración
        /*
		$this->dbms           = Configuracion::getDBMS();
        $this->dbHost         = Configuracion::getDbHost();
        $this->dbName         = Configuracion::getDbName();
        $this->dbUser         = Configuracion::getDbUser();
        $this->dbPassword     = Configuracion::getDbPassword();
        */
        
        $params = array(
            "dsn" => "{$this->dbms}://{$this->dbUser}:{$this->dbPassword}@{$this->dbHost}/{$this->dbName}",
            "table" => $this->userTable,
            "usernamecol" => $this->username_label,
            "passwordcol" => $this->password_label
            );
        if(isset($this->dbPort))
        	$params['dsn'] = "{$this->dbms}://{$this->dbUser}:{$this->dbPassword}@{$this->dbHost}:{$this->dbPort}/{$this->dbName}";
        if(is_array($options))
        {
        	foreach($options as $clave => $valor)
        	{
        		$params[$clave] = $valor;
        	}
        }
        return parent::Auth("MDB2", $params, $loginFunction,$showLogin); 
    }
    
    function getUserId()
    {
        $sql = "SELECT {$this->id_label} FROM {$this->userTable} WHERE {$this->username_label} = '{$this->session['username']}'";
        
        if(!is_object($this->storage)) // si no se hizo ninguna acci�n no se efectu� el _loadStorage 
            $this->listUsers();        // hago el query de usuarios que llama a _loadStorage y no cambia nada 
        
        if(isset($this->storage->db) && !PEAR::isError($this->storage->db))
        {
            if($res = $this->storage->db->query($sql))
            {	
                if(!PEAR::isError($res))
                {
	            	if($u = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
	                {
	                    if(empty($u[$this->id_label]))
	                        return $u[strtolower($this->id_label)];
	                    else
	                        return $u[$this->id_label];
	                }
                }
                else 
                 print $res->getMessage();
            }
        }
        
        return null;
    }
}
