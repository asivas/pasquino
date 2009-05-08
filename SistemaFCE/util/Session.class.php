<?php
require_once("datos/ssHandler/ssHandler.class.php");
require_once("auth/econtrolAuth.class.php");

class Session extends ssHandler{
    
    function Session() {
        parent::__construct(new EcontrolAuth());
    }
    
    function initMembers()
    {
    	parent::initMembers();

        $this->cookie_min= 15;
        $this->sessionName = "ficha_docente";
    }
}