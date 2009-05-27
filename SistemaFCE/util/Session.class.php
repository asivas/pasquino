<?php
require_once("datos/ssHandler/ssHandler.class.php");
require_once("auth/econtrolAuth.class.php");

class Session extends ssHandler{
    
    function Session($sessName) {
        parent::__construct(new EcontrolAuth());
        $this->sessionName = $sessName;
    }
    
    function initMembers()
    {
        parent::initMembers();

        $this->cookie_min= 0;
                
    }
}