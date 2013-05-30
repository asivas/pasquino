<?php
require_once("datos/ssHandler/ssHandler.class.php");
require_once("auth/econtrolAuth.class.php");

class Session extends ssHandler{
    
    function Session($sessName) {
        $this->sessionName = $sessName;
        parent::__construct(new EcontrolAuth());        
    }
    
    function initMembers()
    {
        parent::initMembers();
        $this->cookie_min= 0;
    }
    
    /**
     * Devuelve el tiempo restante de sesion en segundos o FALSE si la sesión no expira nunca
     */
    function getRemainingTime() {
    	if($this->auth->expire > 0)
    		return time() - ($this->auth->session['timestamp'] + $this->auth->expire);
    	 
    	if($this->auth->idle > 0)
    		return ($this->auth->session['idle'] + $this->auth->idle) - time();
    	
    	return FALSE;
    }
}