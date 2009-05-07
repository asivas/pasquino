<?php

require_once('datos/criterio/Criterio.class.php');

abstract class DaoBase {
    /**
     * var object $_db 
     */
    protected $_db;
    /**
     * var string $defaultOrder columna de orden por defecto en el find 
     */
    protected $defaultOrder;
    /**
     * var string $baseFindBySQL
     */
    protected $baseFindBySQL;    
    /**
     * var String $tableName nomrbe de la tabla
     */
    protected $tableName;
    
    /**
     * @var object Objeto tipo SimpleXML para leer lo mappings de clases
     */
    protected $_xmlMapping;
    /**
     * @var string Ruta del archivo de mappings de la clase
     */
    protected $_xmlMappingFile;
    
    /**
     * @var string ruta donde se encuentra el archivo que define la clase de entidad 
     */
    private $_pathEntidad;
    
    /**
     * Constructor de DaoBase
     */
    function DaoBase() {
        $this->_db = &ADONewConnection(Configuracion::dbms); # eg 'mysql' or 'postgres'
        $this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->_db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
        
        $this->loadMapping();
        
        $this->tableName = $this->_xmlMapping['tabla'];
        $this->defaultOrder = $this->_xmlMapping['orden'];
        
        require_once($this->_pathEntidad);
    }
    
    private function _getMapperConfig()
    {
    	$config = simplexml_load_file(dirname(__FILE__).'/../mappings-config.xml');
        return $config;
    }
    
    /**
     * Carga el mapping desde el archivo XML de mappings
     * @return object Objeto SimpleXML con el mapping
     */
    protected function loadMapping()
    {
        if(!isset($this->_xmlMapping))
        {
            if(empty($this->_xmlMappingFile))
            {
            	$archivoMappings = "";
                $nombreEntidad = str_replace("Dao","",get_class($this));
                
                $mapConf = $this->_getMapperConfig();
                
                foreach($mapConf->mapping as $map)
                {
                	if($map['clase']==$nombreEntidad)
                    {
                        $archivoMappings = "{$mapConf['mappings-path']}/{$map['archivo']}";
                        break;
                    }
                }
                
                //el archivo obtenido está puesto relativo a la raiz del proyecto
                $this->_xmlMappingFile = dirname(__FILE__)."/../../{$archivoMappings}";
            }
            			
            $map = simplexml_load_file($this->_xmlMappingFile);
        			
			$hijos = $map->children()->attributes();//asi se leen los hijos
 
           	$this->_xmlMapping = $hijos['clase'];
            print "<pre>";
            var_dump($hijos);
            print "</pre>";
            print "<br><br>";
                     
            $path = $map['path'];
            
 			print $path;           
            print "<br><br>";
            $this->_pathEntidad = "{$path}/{$this->_xmlMapping['nombre']}.class.php";
            print $this->_xmlMapping['nombre'];
            print "<br><br>";
        }
        return $this->_xmlMapping;
    } 
    
    /**
     * Crea el arreglo buffer para guardar
     * @return array arreglo con datos con nombre de la columna de la base (nombreCol => valor)
     */
    protected function getBuffer($elem)
    {
        $buf = array();
        
        $id = $this->_xmlMapping->id;
        $get = "get".ucfirst($id['nombre']);
        $buf[$id['columna']] = $elem->$get();
        
        foreach($this->_xmlMapping->propiedad as $prop)
        {
            $get = "get".ucfirst($prop['nombre']);
            if(!isset($prop['tipo'])) //si es con tipo actualizo el id
                $buf[$prop['columna']] = $elem->$get()->getId();
            else
                $buf[$prop['columna']] = $elem->$get;
        }
        return $buf;
    }
    
    /**
     * Crea el objeto de la entidad a la cual logra el acceso el DAO
     * @return object el objeto con los datos a partir de $row
     * @param array $row arreglo con los datos obtenidos de la base en forma nombreCol => valor
     */
    protected function crearObjetoEntidad($row) 
    {
        $elem = new $this->_xmlMapping['nombre']();
        
        $id = $this->_xmlMapping->id;
        $set = "set".ucfirst($id['nombre']);
        $elem->$set($row[$id['columna']]);
        
        //cargo las propiedades
        foreach($this->_xmlMapping->propiedad as $prop)
        {
            $set = "set".ucfirst($prop['nombre']);
            if(!isset($prop['tipo'])) //si es con tipo actualizo el id
            {
                $nombreDao = "Dao{$prop['tipo']}";
                if(file_exists("daos/{$nombreDao}.class.php"))
                    require_once("daos/{$nombreDao}.class.php");
                if(file_exists("daos/docente/{$nombreDao}.class.php"))
                    require_once("daos/docente/{$nombreDao}.class.php");
                
                $dao = new $nombreDao();
                $elemRelac = $dao->findById($row[$prop['columna']]);
                $elem->$set($elemRelac);
            }
            else
                $elem->$set($row[$prop['columna']]);
        }
        
        //cargo las listas
        foreach($this->_xmlMapping->{"uno-a-muchos"} as $prop)
        {
            $set = "set".ucfirst($prop['nombre']);
            if(isset($prop['tipo'])) //todos deben tener tipo
            {
                $nombreDao = "Dao{$prop['tipo']}";
                if(file_exists("daos/{$nombreDao}.class.php"))
                    require_once("daos/{$nombreDao}.class.php");
                if(file_exists("daos/docente/{$nombreDao}.class.php"))
                    require_once("daos/docente/{$nombreDao}.class.php");
                
                $dao = new $nombreDao();
                
                $elemsRelac = $dao->findBy(new Criterio("`{$prop['columna']}` = ".$elem->getId().""));
                $elem->$set($elemsRelac);
            }
        }
    }
    
    /**
     * Obtiene la referencia a la base de datos
     * @return object Referencia al objeto ADODb
     */
    function getDb()
    {
    	return $this->_db;
    }
    
    /**
     * Obtiene una lista de objetos de la entidad
     * @param object $filtro Objeto de clase Criterio
     * @param string $order Columna o columnas separadas por coma (,) para ordenar la busqueda 
     */
    function findBy($filtro = null,$order=null){
        
        if(!empty($this->baseFindBySQL))
            $sql = $this->baseFindBySQL;
        else
        {
            $tabla = $this->tableName;
            $sql = "SELECT * FROM `{$tabla}`";
        }
        
        $sql .= $filtro->getCondicion();
        
        if(!isset($order)) $order = $this->defaultOrder;
            
        $sql .= " ORDER BY {$order}";
        
        if(!($rs = $this->_db->Execute($sql)))
            die($this->_db->ErrorMsg()." $sql");
            
        $lista = array();
        while($row = $rs->FetchRow())
        {
            $lista[] = $this->crearObjetoEntidad($row);
        }
        return $lista;
    }
    
    /**
     * Obtiene una instancia de la entidad a partir de un id dado
     * @param integer $idElemento
     */
    function findById($idElemento) 
    {
        $c = $this->getCriterioId($idElemento);
        
        $arr = $this->findBy($c);
        
        if(!empty($arr) && is_array($arr))
           return current($arr);
        
        return null;
    }
    
    /**
     * Guarda creando si no existe o actualizando si existe a partir de una instancia de la entidad
     * @param object $elem
     */
    function save($elem) {
        
        $buf = $this->getBuffer($elem);
        
        $mode   = 'INSERT';
        $where  = false;
        /*
         * Busco el elemento por id, si existe debo actualizarlo 
         */
        if($this->findById($elem->getId()))
        {   
            $mode  = 'UPDATE';
            $where = $this->getCriterioId($elem->getId())->getCondicion();
         }
        
        return $this->_db->AutoExecute($this->tableName,$buf,$mode,$where);
    }
    
    /**
     * Genera la el criterio de condición de id usada para la actualización y eliminación
     * @return object instancia de Criterio para filtrar por id
     * @param mixed $id
     */
    protected function getCriterioId($id)
    {	
        $id = $this->_xmlMapping->id;
        
        $nombreColId = $id['columna'];
                
        $c = new Criterio();        
        $c->add("{$nombreColId} = '{$id}'");
        
        return $c;
    }
    
    /**
     * Elimina de la base de datos el elemento con el id dado
     * @param integer $id 
     */
    function deletePorId($id)
    {
    	return $this->_db->Execute("DELETE FROM `{$this->tableName}` WHERE ".$this->getCriterioId($id)->getCondicion());
    }
}
