<?php
require_once "Auth/Auth.php";

abstract class Mdb2IdAuth extends Auth{
    
    var $id_label;
    var $username_label;  
    protected $dbHost;
    protected $dbName;
    protected $dbUser;
    protected $dbPassword;
    
    function Mdb2IdAuth($loginFunction = "", $showLogin = false) {
        
        $params = array(
            "dsn" => "{$this->dbms}://{$this->dbUser}:{$this->dbPassword}@{$this->dbHost}/{$this->dbName}",
            "table" => "usuarios",
            "usernamecol" => $this->username_label,
            "passwordcol" => "password"
            );
        return parent::Auth("MDB2", $params, $loginFunction,$showLogin); 
    }
    
    function getUserId()
    {
        $sql = "SELECT {$this->id_label} FROM usuarios WHERE {$this->username_label} = '{$this->session['username']}'";
        
        if(!is_object($this->storage)) // si no se hizo ninguna acción no se efectuó el _loadStorage 
            $this->listUsers();        // hago el query de usuarios que llama a _loadStorage y no cambia nada 
        
        if($this->storage->db)
        {
            if($res = $this->storage->db->query($sql))
            {	
                if($u = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
                {
                    return $u[$this->id_label];
                }
            }
        }
        
        return null;
    }
}
