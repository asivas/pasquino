<?php

class Configuracion {
    
    function Configuracion() {
        
    }
    
    public static function getDefaultMod()
    {
    	$config = Configuracion::getConfigXML();
        return $config->modulos['default'];
    }
    
    public static function getTemplateConfigByDir($dir)
    {
        $config = Configuracion::getConfigXML();
        $templates = $config->templates;
        
        if(empty($dir))
            $dir = $templates['default'];
            
        foreach($templates->template as $template)
        {
        	$tDir = "{$template['dir']}"; 
            if($tDir==$dir)
            {   
                return $template;
            }
        }
        return null;
    }
    
    public static function getDateFormat()
    {
        $config = Configuracion::getConfigXML();
		return "{$config->{'date-formats'}->{'date-format'}}";

    }

    public static function getDateTimeFormat()
    {
        $config = Configuracion::getConfigXML();
		return "{$config->{'date-formats'}->{'datetime-format'}}";

    }

    public static function getTimeFormat()
    {
        $config = Configuracion::getConfigXML();
		return "{$config->{'date-formats'}->{'time-format'}}";

    }
    
    public static function getDefaultTemplateConfig()
    {
        return Configuracion::getTemplateConfigByDir("");
    }
    
    private static function getDBAttribute($attribName,$nombreDataSource)
    {
        $config = Configuracion::getConfigXML();

        //busco si exite un archivo exclusivo para datasources
        $dataSources = @simplexml_load_file(dirname(__FILE__).'/../../conf/data-sources.xml');

        if(!$dataSources)
            $dataSources = $config->{"data-sources"};
        foreach($dataSources->{"data-source"} as $ds)
        {
            if($ds['name'] == $nombreDataSource)
                return $ds[$attribName];
        }
        
        return "";
    }
    
    public static function getDBMS($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("dbms",$nombreDataSource); 
    }
    
    public static function getDbHost($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("host",$nombreDataSource); 
    }
    
    public static function getDbName($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("db-name",$nombreDataSource); 
    }
    
    public static function getDbUser($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("username",$nombreDataSource); 
    }
    
    public static function getDbPassword($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("password",$nombreDataSource); 
    }
    
    public static function getDbPort($nombreDataSource = "CVDocentes")
    {
        return Configuracion::getDBAttribute("port",$nombreDataSource); 
    }
    
    public static function getVersion()
    {
    	$config = Configuracion::getConfigXML();
        return $config['version'];
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
        //require_once('modulos/CurriculumMod.class.php');
        $config = Configuracion::getConfigXML();
        $modulos = $config->modulos;
        $pathModulos = $modulos['path'];
        foreach($modulos->modulo as $mod)
        {
            $inc = "{$pathModulos}/";
            if(!empty($mod['dir']))
             $inc .= "{$mod['dir']}/";
            $inc .= $mod->archivos->coreDir->archivoPrincipal['nombre'];
            
            require_once($inc);   	
        }
    }
    
    public static function getConfigXML()
    {
    	$config = simplexml_load_file(dirname(__FILE__).'/../../conf/config.xml');
        return $config;
    }
    
    
    public static function agregarModulo()
    {
    	//TODO: a partir de un XML de modulo importarlo al XML de sistema 
    }
    
    public static function quitarModulo($nombre)
    {
        //TODO: que a partir del $nombre lo borre del XML de sistema
        // tambien debería borrarl los archivos 
    }
    
    public static function agregarTemplate()
    {
        //TODO: a partir de un XML de template importarla al XML de sistema 
    }
    
    public static function quitarTemplate($nombreDir)
    {
        //TODO: que a partir del $nombreDir borre la template del XML de sistema
        // tambien debería borrarl los archivos 
    }
    
    //TODO: discutir si habría que hacer las funciones de ABM de data-sources
    
    public static function getModulosConfig()
    {
        $config = Configuracion::getConfigXML();
        return $config->modulos;
    }
}