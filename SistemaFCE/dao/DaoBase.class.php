<?php

require_once('datos/criterio/Criterio.class.php');
require_once('datos/adodb/adodb.inc.php');

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
        $this->_db = &ADONewConnection(Configuracion::getDBMS()); # eg 'mysql' or 'postgres'
        $this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->_db->Connect(Configuracion::getDbHost(), Configuracion::getDbUser(), Configuracion::getDbPassword(), Configuracion::getDbName());
        
        $this->loadMapping();
        
        $this->tableName = $this->_xmlMapping['tabla'];
        $this->defaultOrder = $this->_xmlMapping['orden'];
        
        require_once($this->_pathEntidad);
    }
    
    private function _getMapperConfig()
    {
        $config = Configuracion::getConfigXML();
        return $config->mappings;
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
                
                $mappings = $this->_getMapperConfig();
                foreach($mappings->mapping as $map)
                {
                    if(strtoupper($map['clase'])==strtoupper($nombreEntidad))
                    {
                        $archivoMappings = "{$mappings['path']}/{$map['archivo']}";
                        break;
                    }
                }
                
                //el archivo obtenido est� puesto relativo a la raiz del proyecto
                $this->_xmlMappingFile = dirname(__FILE__)."/../../{$archivoMappings}";
            }
            
            $map = simplexml_load_file($this->_xmlMappingFile);
        			
           	$this->_xmlMapping = $map->clase;
                      
            $path = $map['path'];
            
            $this->_pathEntidad = "{$path}/{$this->_xmlMapping['nombre']}.class.php";
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
        $buf[(string)$id['columna']] = $elem->$get();
        
        foreach($this->_xmlMapping->propiedad as $prop)
        {
            $get = "get".ucfirst($prop['nombre']);
            $p = $elem->$get();
            $col = (string)$prop['columna'];
            if(isset($prop['tipo'])) //si es con tipo actualizo el id
                $buf[$col] = $p->getId();
            else
                $buf[$col] = $p;
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
        $elem_name = "".$this->_xmlMapping['nombre'];// el "". es para forzar string
        $elem = new $elem_name();
 
        $id = $this->_xmlMapping->id;
        $set = "set".ucfirst("".$id['nombre']);
		
        $elem->$set($row["".$id['columna']]);
        
        //cargo las propiedades
        $tmp = $this->_xmlMapping->propiedad;
   
        foreach($tmp as $prop)
        {
            $set = "set".ucfirst("".$prop['nombre']);
            if(isset($prop['tipo'])) //si es con tipo actualizo el id
            {
                $nombreDao = "Dao".$prop['tipo'];
                
                if(file_exists("daos/{$nombreDao}.class.php"))
                    require_once("daos/{$nombreDao}.class.php");
                if(file_exists("daos/docente/{$nombreDao}.class.php"))
                    require_once("daos/docente/{$nombreDao}.class.php");
                
                $dao = new $nombreDao();
                $elemRelac = $dao->findById($row["".$prop['columna']]);
                $elem->$set($elemRelac);
            }
            else
            {
                 $elem->$set($row["".$prop['columna']]);
             }
        }
 
        //cargo las listas
        $tmp = $this->_xmlMapping->uno-a-muchos;
        if($tmp != null) 
        foreach($tmp as $prop)
        {
            $set = "set".ucfirst("".$prop['nombre']);
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
    	return $elem;
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
        
        if($filtro != null) 
        	$sql .= " WHERE " . $filtro->getCondicion();
        
        if(!isset($order)) 
        	$order = $this->defaultOrder;
        
        if(isset($order))    
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
     * Genera la el criterio de condici�n de id usada para la actualizaci�n y eliminaci�n
     * @return object instancia de Criterio para filtrar por id
     * @param mixed $id
     */
    protected function getCriterioId($idElemento)
    {	
        $id = $this->_xmlMapping->id;
        
        $nombreColId = $id['columna'];
                
        $c = new Criterio();        
        $c->add("{$nombreColId} = '{$idElemento}'");
        
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
