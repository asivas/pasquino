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
 	 * 
 	 * Limpia la variable definida por key
 	 * @param mixed $key
 	 */
    function clear($key)
    {
    	if(is_array($key))
    	{
    		$strKey="";
    		while(($sk=current($key))!==FALSE)
    		{
    			if(!is_numeric($sk))
    				$sk="'{$sk}'";
    			$strKey.="[{$sk}]";
    			next($key);
    		}
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
    	if(is_array($key))
    	{
    		$strKey="";
    		while(($sk=current($key))!==FALSE)
    		{
    			if(!is_numeric($sk))
    				$sk="'{$sk}'";
    			$strKey.="[{$sk}]";
    			next($key);
    		}
    		if(!empty($strKey))
    		{	
    			eval('$sessVar =& $_SESSION'.$strKey.';');
    			//$sessvar es una variable por referencia por lo tanto
    			//seteo la variable en $_SESSION[$keys]
    			$sessVar = $val;
    		}
    	}
    	else
    		$_SESSION[$key]=$val;
    }
    
	/**
     * Agrega un valor a una variable en la sesión 
     */
    function get($key)
    {
    	if(is_array($key))
    	{
    		$strKey="";
    		while(($sk=current($key))!==FALSE)
    		{
    			if(!is_numeric($sk))
    				$sk="'{$sk}'";
    			$strKey.="[{$sk}]";
    			next($key);
    		}
    		if(!empty($strKey))
    		{
    			eval('$sessVar = $_SESSION'.$strKey.';');
    			return $sessVar;
    		}
    		return null;
    	}
    	else
    		return $_SESSION[$key];
    }
}