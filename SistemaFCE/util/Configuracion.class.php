<?php

class Configuracion {
    
    function Configuracion() {
        
    }
    
    public static function initSistema($rutaSysRoot,$pathsIncludePath=null)
    {
        
        if(Configuracion::getSystemRootDir()==null)
            Configuracion::setSystemRootDir($rutaSysRoot);
        
        Configuracion::setIncludePath($pathsIncludePath);

        //{{{ Incluir modulos
        Configuracion::incluirModulos();    
        ///}}}
        
        if(strpos(strtoupper($_SERVER['SERVER_SIGNATURE']),"WIN")!==FALSE) /*servidor Windows*/
            setlocale (LC_TIME, "spanish"); 
        elseif(strpos(strtoupper($_SERVER['SERVER_SIGNATURE']),"UNIX")!==FALSE)/*sevidor unix*/
            setlocale (LC_TIME, "es_AR");
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
    
    public static function getAppName()
    {
    	$config = Configuracion::getConfigXML();
        return $config['nombre'];
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
    
    public static function getDefaultDataSource()
    {
        $config = Configuracion::getConfigXML();

        //busco si exite un archivo exclusivo para datasources
        $dataSources = @simplexml_load_file(Configuracion::getSystemRootDir().'/conf/data-sources.xml');

        if(!$dataSources)
            $dataSources = $config->{"data-sources"};
        
        return (string)$dataSources['default'];
    }
    
    private static function getDBAttribute($attribName,$nombreDataSource)
    {
        $config = Configuracion::getConfigXML();

        //busco si exite un archivo exclusivo para datasources
        $dataSources = @simplexml_load_file(Configuracion::getSystemRootDir().'/conf/data-sources.xml');

        if(!$dataSources)
            $dataSources = $config->{"data-sources"};
            
        if(!isset($nombreDataSource))
            $nombreDataSource = Configuracion::getDefaultDataSource();
        foreach($dataSources->{"data-source"} as $ds)
        {
            if($ds['name'] == $nombreDataSource)
                return $ds[$attribName];
        }
        
        return "";
    }
    
    public static function getDBMS($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("dbms",$nombreDataSource); 
    }
    
    public static function getDbHost($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("host",$nombreDataSource); 
    }
    
    public static function getDbName($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("db-name",$nombreDataSource); 
    }
    
    public static function getDbUser($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("username",$nombreDataSource); 
    }
    
    public static function getDbPassword($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("password",$nombreDataSource); 
    }
    
    public static function getDbPort($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("port",$nombreDataSource); 
    }
    
    public static function getVersion()
    {
    	$config = Configuracion::getConfigXML();
        return $config['version'];
    }
    
    public static function setSystemRootDir($rootDir)
    {
    	$GLOBALS['ROOT_DIR'] = $rootDir;
    }
    
    public static function getSystemRootDir()
    {   
        return $GLOBALS['ROOT_DIR'];
    }
    
    public static function setIncludePath($otrosPaths=null)
    {
        if(strpos(strtoupper(PHP_OS),'WIN')!==FALSE)
            $pathSep = ';';
        else
            $pathSep = ':';
        
        $sysRoot = Configuracion::getSystemRootDir();
        
        $inc_path = ini_get("include_path");
        
        //siempre agrego para todos los sistemas la carpeta de clases 
        $inc_path .= $pathSep.$sysRoot.'/clases';
        
        if(isset($otrosPaths) && is_array($otrosPaths))
        {
        	foreach($otrosPaths as $path)
                $inc_path .= $pathSep.$sysRoot."/".$path;
        }

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
            if(!empty($mod->archivos->coreDir['ruta']))
                $inc .= "{$mod->archivos->coreDir['ruta']}/";
            $inc .= $mod->archivos->coreDir->archivoPrincipal['nombre'];
            
            require_once($inc);   	
        }
    }
    
    public static function getConfigXML()
    {
    	$config = simplexml_load_file(Configuracion::getSystemRootDir().'/conf/config.xml');
        return $config;
    }
    
    private static function findTplPath($tConf,$sysName = 'Default')
    {
        //recorro los archivos
        if(isset($tConf->archivo))
        {
            $archivos = $tConf->archivo;
            foreach($archivos as $arch)
            {
                $sn = $arch['sys-name'];
                if(isset($sn) && $sn == $sysName)
                {   
                    return (string)$arch['nombre'];
                }
            }
        }
        //recorro los dir si los tiene
        if(!empty($tConf->{'dir'}))
        {
            foreach($tConf->{'dir'} as $dir)
            {
                $path = Configuracion::findTplPath($dir,$sysName);
                if(!empty($path))
                {   
                    return "{$dir['ruta']}/$path";
                }
            }
        } 
    }
    
    public static function getDefaultTplPath($templateDir="")
    {
    	//$tplPath = "";
        $tConf = Configuracion::getTemplateConfigByDir($templateDir);
        return Configuracion::findTplPath($tConf,'Default');
    }
    
    public static function getBaseTplPath($templateDir="")
    {
        //$tplPath = "";
        $tConf = Configuracion::getTemplateConfigByDir($templateDir);
        return Configuracion::findTplPath($tConf,'Base');
    }
    
    
    public static function agregarModulo()
    {
    	//TODO: a partir de un XML de modulo importarlo al XML de sistema 
    }
    
    public static function quitarModulo($nombre)
    {
        //TODO: que a partir del $nombre lo borre del XML de sistema
        // tambien deber�a borrarl los archivos 
    }
    
    public static function agregarTemplate()
    {
        //TODO: a partir de un XML de template importarla al XML de sistema 
    }
    
    public static function quitarTemplate($nombreDir)
    {
        //TODO: que a partir del $nombreDir borre la template del XML de sistema
        // tambien deber�a borrarl los archivos 
    }
    
    //TODO: discutir si habr�a que hacer las funciones de ABM de data-sources
    
    public static function getModulosConfig()
    {
        $config = Configuracion::getConfigXML();
        return $config->modulos;
    }
}