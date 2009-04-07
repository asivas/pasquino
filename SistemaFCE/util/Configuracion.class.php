<?php

class Configuracion {
    const dbHost        = 'localhost';
    const dbName        = 'docentes';
    const dbUser        = 'dnccmad';
    const dbPassword    = 'dnccmad';    
    const dbms          = 'mysql';
    
    const version       = '0.1 alpha';
    
    const modDefault    = 'Docentes'; 
    
    function Configuracion() {
        
    }
    
    public static function setIncludePath()
    {
        if(strpos(strtoupper(PHP_OS),'WIN')!==FALSE)
            $pathSep = ';';
        else
            $pathSep = ':';
        
        $sysRoot = dirname(dirname(dirname(__FILE__)));
        
        $inc_path = ini_get("include_path");
        $inc_path .= $pathSep.$sysRoot.'/clases';

        $inc_path = ini_set("include_path",$inc_path);    
        
    }
    
    public static function incluirModulos()
    {
        //require_once('modulos/DocentesMod.class.php');
    }
}