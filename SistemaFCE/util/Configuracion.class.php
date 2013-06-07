<?php

class Configuracion {
    
    function Configuracion() {
        
    }
    
	static public function autoload_entidad($nombre) {
        @include_once("entidades/{$nombre}.class.php");
		@include_once("SistemaFCE/entidad/{$nombre}.class.php");
        @include_once("{$nombre}.class.php");
    }
    
    public static function initSistema($rutaSysRoot,$pathsIncludePath=null)
    {
        
        if(Configuracion::getSystemRootDir()==null)
            Configuracion::setSystemRootDir($rutaSysRoot);
        
        Configuracion::setIncludePath($pathsIncludePath);
		
        spl_autoload_register('Configuracion::autoload_entidad');
        //{{{ Incluir modulos
        Configuracion::incluirModulos();    
        ///}}}
        
        if(strpos(strtoupper($_SERVER['SERVER_SIGNATURE']),"WIN")!==FALSE) /*servidor Windows*/
            setlocale (LC_TIME, "spanish"); 
        else //supongo unix //if(strpos(strtoupper($_SERVER['SERVER_SIGNATURE']),"UNIX")!==FALSE)/*sevidor unix*/
            setlocale (LC_TIME, "es_AR", "es_AR.UTF-8");
    } 
    
    public static function getDefaultMod()
    {
    	$config = Configuracion::getConfigXML();
        return $config->modulos['default'];
    }
    
    public static function getTemplateConfigByDir($dir)
    {
    	return Configuracion::getTemplateConfigByNombre($dir);
    }
    
    public static function getTemplateConfigByNombre($nombre_o_dir)
    {   
    	$config = Configuracion::getConfigXML();
        $templates = $config->templates;
        
        if(empty($nombre_o_dir))
            $nombre_o_dir = $templates['default'];
        
        foreach($templates->template as $template)
        {
        	$tDir = "{$template['nombre']}";
        	if(empty($tDir)) $tDir = "{$template['dir']}";
        	
            if($tDir==$nombre_o_dir)
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
    
    public static function getDbDSN($nombreDataSource = null)
    {
        return Configuracion::getDBAttribute("DSN",$nombreDataSource); 
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
        if(isset($GLOBALS['ROOT_DIR']))
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
        }//TODO: permitir que sea string

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
    
    public static function findTplPath($tConf,$sysName = 'Default')
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
        
        return null;
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
    
    public static function getMappingConfigClase($clase)
    {
        $config = Configuracion::getConfigXML();
        $mappings = $config->mappings;
        foreach($mappings->mapping as $map)
        {
            if(strtoupper($map['clase'])==strtoupper($clase))
            {
                return $map;
            }
        }
        return null;
    }
    
    public static function getMappingClase($nombreClase,$xmlMappingFile = null)
    {    
        if(empty($xmlMappingFile))
        {
            $archivoMappings = "";
            $config = Configuracion::getConfigXML();
            
            $mappings = $config->mappings;
            
            $map = Configuracion::getMappingConfigClase($nombreClase);
            if(isset($map))
            {
                $archivo = $map['archivo'];
                if(isset($map['dir']))
                {
                    $archivo = "{$map['dir']}/{$archivo}";
                }
                $archivoMappings = "{$mappings['path']}/{$archivo}";
            }
            
            //el archivo obtenido est� puesto relativo a la raiz del proyecto
            $xmlMappingFile = Configuracion::getSystemRootDir()."/{$archivoMappings}";
        }
        
        $map = simplexml_load_file($xmlMappingFile);
        return $map;	
    }
    
    
    public static function ejecutarSistema($req=null,$m=null)
    {
        if(!isset($req))
            $req = $_REQUEST;
        /*
         Experimental, para poder usar una URL amigable a buscadores
         
        $ruta = str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['REQUEST_URI']);

        $ruta = str_replace("?{$_SERVER['QUERY_STRING']}",'',$ruta);

        if(!empty($ruta))
        {
            $modAcc = split("/",$ruta);
            $m = $modAcc[1];
            $req['accion'] = $modAcc[2]; 
        }
        
        */
        if(!isset($m))
        {
            $m = $req['mod'];
            if(!empty($_GET['mod'])) $m = $_GET['mod'];
            if(!empty($_POST['mod'])) $m = $_POST['mod'];
        }
        
        $modName = ucfirst($m)."Mod";
       
        if(!class_exists($modName))
            $modName = Configuracion::getDefaultMod().'Mod';
       
        $mod = new $modName();
	        
	    $mod->ejecutar($req);	
        
    }
    
    /**
     * Obtiene la configuraci�n del modulo cuyo nombre es dado
     * 
     * @param string $mod nombre del modulo
     */
    public static function getConfigModulo($nombreMod)
    {
    	if(strpos($nombreMod,'Mod')===FALSE)
          $nombreMod .= 'Mod';
        
        $modulos = Configuracion::getModulosConfig();
        foreach($modulos->modulo as $mod)
        {	
            if("{$mod['nombre']}Mod" == $nombreMod)
                return $mod;
        }
        return null;
    }
    
    /**
     * 
     * Obtiene el nombre de la accion predeterminada del modulo dado
     * @param string $mod nombre del modulo
     * @return string el nombre de la accion predeterminada
     */
    public static function getAccionPerdeterminada($mod)
    {
    	$modConfig = Configuracion::getConfigModulo($mod);
    	$accion = (string)$modConfig->acciones['default'];
    	if(empty($accion)) $accion = 'listar';
    	
    	return $accion;
    }
    
    
    public static function getEntidadUsuarioClass() {
    	$config = Configuracion::getConfigXML();
    	$mappings = $config->mappings;
    	
    	if(isset($mappings['entidadUsuario']))
    		return (string)$mappings['entidadUsuario'];
    	
    	return "Usuario";
    }
    
    /**
     * 
     * Obtiene desde XML de configuración el nombre de clase del objeto log
     */
    //FIXME: this method should return array of loggers configurations
    public static function getLoggerClass(){
    	//TODO: contemplar N loggers
    	$config = Configuracion::getConfigXML();
    	if (isset($config->loggers))
    		foreach ($config->loggers as $logger) {
    			return (String)$logger->logger['class'];
    		}    		
    	return null;
    }
    
}