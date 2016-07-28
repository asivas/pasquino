<?php
namespace pQn\auth;

class EcontrolAuth extends Mdb2IdAuth{
    
    function EcontrolAuth($loginFunction = "",$showLogin = false) {
        
        $this->id_label       = "id";
        $this->username_label = "DNI";
        $this->password_label = "password";
        
        $this->userTable      = "usuarios";
        
        
        $this->dbms           = "mysql";
        $this->dbHost         = "intranet.econ.unicen.edu.ar";
        $this->dbName         = "econtrol";
        $this->dbUser         = "econtrol";
        $this->dbPassword     = "enccmae";
        
        return parent::__construct($loginFunction,$showLogin); 
    }
}
