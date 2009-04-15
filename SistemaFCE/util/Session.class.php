<?php
require_once("datos/ssHandler/ssHandler.class.php");
require_once('datos/adodb/adodb.inc.php');

class Session extends ssHandler{
    
    function getConexion(){
        if(!isset($this->db))
        {
            $this->db = ADONewConnection('mysql'); # eg 'mysql' or 'postgres'
            
            $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
            //$db->debug = true;
            $this->db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
        }
        return $this->db;
    }
    
    function Session() {
        $this->getConexion();
        $this->logobj = null;
        $this->table = "usuarios";
        $this->usr_id_label = "id";
        $this->usr_label = "DNI";
        $this->pass_label = "password";
        $this->cookie_min= 15;
        $this->refreshAfterLogin = true;
        $this->passCaseSensitive = true;
        $this->sessionName = "ficha_docente";
        
        $this->InitSession();//debe llamarse explicitamente el InitSession
    }
    
    function getIdUsuario()
    {
    	return $this->usr_id;
    }
}