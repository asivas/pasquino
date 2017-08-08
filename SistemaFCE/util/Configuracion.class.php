<?php

class Configuracion {
    static private $rootDir;
    static private $confDir='conf';
    static private $confFilename='config.xml';
    static private $publicDir;
    static private $configXml;

    function Configuracion() {

    }

	static public function autoload_entidad($nombre) {
		$entPath = "{$nombre}.class.php";
		$systemEntPath = "entidades/{$entPath}";
		$pasquinoEntPath = "SistemaFCE/entidad/{$entPath}";

		if(stream_resolve_include_path($systemEntPath))
			include_once($systemEntPath);
		if(stream_resolve_include_path($pasquinoEntPath))
			include_once($pasquinoEntPath);
		if(stream_resolve_include_path($entPath))
			include_once($entPath);
    }

    public static function initSistema($rutaSysRoot=null,$pathsIncludePath=null)
    {
		$dbt = debug_backtrace();
        
        if(Configuracion::getSystemRootDir()==null)
        {
        	if(!isset($rutaSysRoot))
        	{
        		$rutaSysRoot = dirname(dirname($dbt[0]['file']));
        	}
        	Configuracion::setSystemRootDir($rutaSysRoot);
        }
        
        self::$publicDir = dirname($dbt[0]['file']);

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

    public static function getGessedAppRelpath() {
        $path = $_SERVER['REQUEST_URI'];
        if( strpos($path,'?')!==false && !empty($_SERVER['QUERY_STRING']) )
            $path = str_replace("?{$_SERVER['QUERY_STRING']}",'',$path);

        return $path;
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
        $dataSources = @simplexml_load_file(Configuracion::getSystemRootDir().DIRECTORY_SEPARATOR.self::$confDir.DIRECTORY_SEPARATOR.'data-sources.xml');

        if(!$dataSources)
            $dataSources = $config->{"data-sources"};

        return (string)$dataSources['default'];
    }

    private static function getDBAttribute($attribName,$nombreDataSource)
    {
        $config = Configuracion::getConfigXML();

        //busco si exite un archivo exclusivo para datasources
        $dataSources = @simplexml_load_file(Configuracion::getSystemRootDir().DIRECTORY_SEPARATOR.self::$confDir.DIRECTORY_SEPARATOR.'data-sources.xml');

        if(!$dataSources)
            $dataSources = $config->{"data-sources"};

        if(!isset($nombreDataSource))
            $nombreDataSource = Configuracion::getDefaultDataSource();
        foreach($dataSources->{"data-source"} as $ds)
        {
            if($ds['name'] == $nombreDataSource)
                return (string)$ds[$attribName];
        }

        return "";
    }

    public static function getDbHostPort($nombreDataSource = null)
    {
    	$host = Configuracion::getDbHost($nombreDataSource);
    	$port = Configuracion::getDbPort($nombreDataSource);
    	if($port != '')	$host .= ":{$port}";
    	return $host;
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
    	//$GLOBALS['ROOT_DIR'] = $rootDir;
    	self::$rootDir = $rootDir;
    }

    public static function getSystemRootDir()
    {
        /*if(isset($GLOBALS['ROOT_DIR'])) return $GLOBALS['ROOT_DIR']; */
    	if(isset(self::$rootDir))
    		return self::$rootDir;
    }

    private static function getStandardSubdirs($dir) {
    	return array($dir.DIRECTORY_SEPARATOR.'entidades'.DIRECTORY_SEPARATOR.'daos',
        		$dir.DIRECTORY_SEPARATOR.'utils',
    			$dir.DIRECTORY_SEPARATOR.'util',
        		$dir.DIRECTORY_SEPARATOR.'auth',
        		$dir.DIRECTORY_SEPARATOR.'rules');
    }

    public static function setIncludePath($otrosPaths=null)
    {
    	$pathSep = PATH_SEPARATOR;

        $sysRoot = Configuracion::getSystemRootDir();

        $inc_path = ini_get("include_path");

        if(!isset($otrosPaths) )
        	$otrosPaths= array();

       	//si es string lo hago array
       	if(is_string($otrosPaths))
       		$otrosPaths[] = $otrosPaths;

        $standardDirs = array('clases', 'src', 'application');

        foreach ($standardDirs as $dirname)
        	if(file_exists($sysRoot.DIRECTORY_SEPARATOR.$dirname) && array_search($dirname, $otrosPaths)===FALSE)
        		$otrosPaths[] = $dirname;

        if(is_array($otrosPaths))
        {
        	foreach($otrosPaths as $dir)
        	{
        		if(file_exists($sysRoot.DIRECTORY_SEPARATOR.$dir))
        		{
        			$inc_path .= $pathSep.$sysRoot.DIRECTORY_SEPARATOR.$dir;
        			$stdSubDirs = Configuracion::getStandardSubdirs($dir);
        			if(is_array($stdSubDirs))
        			{
        				foreach ($stdSubDirs as $sdir)
        				{
        					if(file_exists($sysRoot.DIRECTORY_SEPARATOR.$sdir))
        						$inc_path .= $pathSep.$sysRoot.DIRECTORY_SEPARATOR.$sdir;
        				}
        			}
        		}
        		else if(file_exists($dir))
        			$inc_path .= $pathSep.$dir;
        	}
        }

        $inc_path = ini_set("include_path",$inc_path);
    }

    public static function incluirModulos()
    {
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
    	if(!isset(self::$configXml))
            self::$configXml = simplexml_load_file(Configuracion::getSystemRootDir().DIRECTORY_SEPARATOR.self::$confDir.DIRECTORY_SEPARATOR.self::$confFilename);
        return self::$configXml;
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

		if(file_exists($xmlMappingFile) && $archivoMappings!='')
			$map = simplexml_load_file($xmlMappingFile);
		else {
			$daoClass = "Dao{$nombreClase}";

			if(class_exists($daoClass) && method_exists($daoClass, 'getDefaultMapping'))
			{
				$map = $daoClass::getDefaultMapping();
			}
			else{
				throw new Exception("No se ecuentra el mapping de la clase {$nombreClase}");
			}
		}

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
        if(isset($req['mIt']))
        {
        	if( ($menuItemConf = Configuracion::getMenuItemConfByName($req['mIt'])) != null)
        	{
        		if(isset($menuItemConf['alias']))
        			if( ($aliasItemConf = Configuracion::getMenuItemConfByName($menuItemConf['alias']))!=null)
        				$menuItemConf = $aliasItemConf;

       			$m = (string)$menuItemConf['mod'];
       			$req['accion'] = (string)$menuItemConf['accion'];
        	}
        }

        if(!isset($m))
        {
            $m = $req['mod'];
            if(!empty($_GET['mod'])) $m = $_GET['mod'];
            if(!empty($_POST['mod'])) $m = $_POST['mod'];
        }

        $modName = ucfirst($m)."Mod";

        if(!class_exists($modName))
        {
            $modName = Configuracion::getDefaultMod().'Mod';
            if(!isset($req['accion'])) //solo actualizo si se llama sin mod y sin acción
            	Configuracion::installOrUpdateDatabase();
        }

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

    /**
     * Obtiene la configuración de un item submenú de menu de la configuración a partir del nombre
     * o null si no la encuentra
     * @param $name nombre del menuItem del que se quiere obtener la config
     */
    private static function getSubMenuItemConfByName($name,$menuItemConf)
    {
    	if(!empty($menuItemConf))
    	{
	    	foreach($menuItemConf->menuItem as $subItemConf)
	    	{
	    		$nam = (string)$subItemConf['name'];
	    		if($nam == $name)
	    			return $subItemConf;
	    	}
    	}
    	return null;
    }

    /**
     * Obtiene la configuración de un item del menú a partir del nombre
     * o null si no la encuentra
     * @param $name nombre del menuItem del que se quiere obtener la config
     */
    public static function getMenuItemConfByName($name)
    {
	    $modulosConfig = Configuracion::getModulosConfig();
	    foreach($modulosConfig->modulo as $modConf)
	    {
	    	$nam = (string)$modConf->menuPrincipal['name'];

	    	if($nam == $name)
	    		$menuItemConf = $modConf->menuPrincipal;
	    	else
		    	$menuItemConf = Configuracion::getSubMenuItemConfByName($name, $modConf->menuPrincipal);

	    	if($menuItemConf!=null)
	    	{
		    	if(empty($menuItemConf['mod']))
		    		$menuItemConf['mod'] = (string)$modConf['nombre'];

		    	return $menuItemConf;
	    	}
	    }
	    return null;
    }

    public static function getDBUpdaterClass() {
    	$config = Configuracion::getConfigXML();
    	$className = (string)$config['dbUpdater-class'];
   		return $className;
    }

    public static function getPropertiesManagerClass(){
    	$config = Configuracion::getConfigXML();
    	return (string)$config['properties-manager-class'];
    }

    public static function getVersionDB() {
    	$propsMgrClass = Configuracion::getPropertiesManagerClass();
    	if(!empty($propsMgrClass))
    	{
    		require_once "$propsMgrClass.class.php";
    		//$propsMgr = new $propsMgrClass();
    		return $propsMgrClass::getPropertyValue('versionDB','0');
    	}
    	return "0";
    }

    static private function installOrUpdateDatabase() {
    	$dbUpdClass = Configuracion::getDBUpdaterClass();
    	if(empty($dbUpdClass)) return;
    	$versionActual = Configuracion::getVersionDB();
    	$versionEsperada = $dbUpdClass::$expectedDBVersion;
    	if($versionEsperada<=$versionActual)
    		return;

    	$dbUpd = new $dbUpdClass();
    	$dbUpd->updateDb($versionActual);
    }

    static public function getPasquinoPath()
    {
    	return dirname(dirname(dirname(__FILE__)));
    }

    static public function getAuths() {
    	$config = Configuracion::getConfigXML();
    	return $config->auths;
    }
    
    /**
     * Gets an attribute from config, if the modName is defined and has the attribute defined will return the mod attribute, 
     * if it doesn't it will return the general attribute
     * @param unknown $attribName
     * @param unknown $modName
     */
    static private function getAttributeModOrGeneral($attribName,$modName=null) {
    	$config = self::getConfigXML();
    	if(isset($modName))
    	{
    		$configMod = self::getConfigModulo($modName);
    		if(isset($configMod[$attribName]))
    			return $configMod[$attribName];
    	}
    	
    	return $config[$attribName];
    }
    
    /**
     * Corrobora en el config del sistema si se deben mergear los archivos JS en uno solo (los agregados con addJsFile)
     * @return boolean
     */
    static public function getMergeJsFiles($modName=null) {
    	return self::getAttributeModOrGeneral('merge-js-flies',$modName) == true;
    } 
    
    /**
     * Corrobora en el config del sistema si se deben mergear los archivos css en uno solo (los agregados con addCssFile)
     * 
     * @return boolean
     */
    static public function getMergeCssFiles($modName=null) {
    	return self::getAttributeModOrGeneral('merge-css-flies',$modName) == true;
    }
    
    /**
     * Gets the public dir (where executed index.php is) path
     * @return string
     */
    static public function getPublicDir() {
    	return self::$publicDir;
    }

}

class_alias('Configuracion','SistemaFCE');