<?php

class DaoBase {
    
    protected $_db;
    
    function DaoBase() {
        $this->db = &ADONewConnection(Configuracion::dbms); # eg 'mysql' or 'postgres'
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
    }
    
    function getDb()
    {
    	return $this->_db;
    }
    
    
}
