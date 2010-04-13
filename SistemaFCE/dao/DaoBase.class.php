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
     * @var string Ultimo mensaje de error que sucedió en un save  
     */
    private $_lastError;
    
    /**
     * Constructor de DaoBase
     */
    function DaoBase() {
        $this->loadMapping();
        
        $dataSource = null;
        if(isset($this->_xmlMapping['data-source']))
            $dataSource = (string)$this->_xmlMapping['data-source'];
        
        $this->_db = $this->getConexion($dataSource);
        
        $this->tableName = $this->_xmlMapping['tabla'];
        $this->defaultOrder = $this->_xmlMapping['orden'];
        
        require_once($this->_pathEntidad);
    }
    
    function getConexion($dataSource=null)
    {	
        if(!isset($dataSource))
            $dataSource = Configuracion::getDefaultDataSource();
        
        $db = &ADONewConnection(Configuracion::getDBMS($dataSource)); # eg 'mysql' or 'postgres'
        $db->SetFetchMode(ADODB_FETCH_ASSOC);
        $db->NConnect(Configuracion::getDbHost($dataSource), Configuracion::getDbUser($dataSource), Configuracion::getDbPassword($dataSource), Configuracion::getDbName($dataSource));
        return $db;
    }
    
    private function _getMapperConfig()
    {
        $config = Configuracion::getConfigXML();
        return $config->mappings;
    }
    
    private function _getMappingConfig($clase)
    {
        $mappings = $this->_getMapperConfig();
        foreach($mappings->mapping as $map)
        {
            if(strtoupper($map['clase'])==strtoupper($clase))
            {
                return $map;
            }
        }
        return null;
    }
    
    /**
     * Carga el mapping desde el archivo XML de mappings
     * @return object Objeto SimpleXML con el mapping
     */
    protected function loadMapping()
    {
        $map = Configuracion::getMappingClase(str_replace("Dao","",get_class($this)),$this->_xmlMappingFile);
        
        $this->_xmlMapping = $map->clase;
        
        $path = (string)$map['path'];
        
        if(!empty($path))  $path.="/";
        
        if(!empty($map->clase['path']))
        {  
            $path .= "{$map->clase['path']}/";
        }
        
        $this->_pathEntidad = "{$path}{$this->_xmlMapping['nombre']}.class.php";
        
        return $this->_xmlMapping;
    } 
    
    /**
     * Crea el arreglo buffer para guardar
     * @return array arreglo con datos con nombre de la columna de la base (nombreCol => valor)
     */
    protected function getBuffer($elem)
    {
        $buf = array();
      
        foreach($this->_xmlMapping->id as $id)
        {   
            $get = "get".ucfirst($id['nombre']);
            $buf[(string)$id['columna']] = $elem->$get();
        }

        foreach($this->_xmlMapping->propiedad as $prop)
        {
            if(!isset($prop->{'data-source'})) // si es de otra base no hay que ponerlo en el bufer
            {
                $get = "get".ucfirst($prop['nombre']);
                if($get!="get")
                {
                	$p = $elem->$get();
                	$col = (string)$prop['columna'];
                	if(isset($prop['tipo']) && $p != null) //si es con tipo actualizo el id
                   		$buf[$col] = $p->getId();
                	else
                    	$buf[$col] = $p;
                }
            }
        }
        return $buf;
    }
    
    private function _newDaoClase($clase)
    {
    	$nombreDao = $nombreDaoFile = "Dao".$clase;
        
        $map = Configuracion::getMappingConfigClase($clase);
        
        if(isset($map) && isset($map['dir']))
           $nombreDaoFile = "{$map['dir']}/{$nombreDaoFile}";
        
        //Siempre se espera la misma estructura para los mappings que para los daos 
        require_once("daos/{$nombreDaoFile}.class.php");        
        
        return new $nombreDao();
    }
    
    /**
     * Crea el objeto de la entidad a partir de un arreglo que tiene los mismos nombres de elementos
     * que los nombres de las propiedades.
     * 
     * Este no tiene en cuenta las relaciones tipadas a no ser que la entidad tenga la variable id y 
     * obtenga el objeto relacionado direcatemente desde el get, sin tenerlo como propiedad
     * 
     * @return object el objeto con los datos a partir de $row
     * @param array $arreeglo arreglo con los datos que cada clave es identica que un nombre de propiedad nombrePropiedad => valor
     */
    function crearDesdeArreglo($arreglo) 
    {
        $elem_name = (string)$this->_xmlMapping['nombre'];
        $elem = new $elem_name();
        
        if(!is_array($arreglo))
            return $elem;   
        
        foreach($arreglo as $nombreProp => $valor)
        {
        	$set = "set".ucfirst($nombreProp);
            if(method_exists($elem,$set))
                $elem->$set($valor);
        }
        
        return $elem;
    }
    
    /**
     * Crea el objeto de la entidad a la cual logra el acceso el DAO
     * @return object el objeto con los datos a partir de $row
     * @param array $row arreglo con los datos obtenidos de la base en forma nombreCol => valor
     */
    protected function crearObjetoEntidad($row) 
    {
        $elem_name = (string)$this->_xmlMapping['nombre'];
        $elem = new $elem_name();
 
        foreach($this->_xmlMapping->id as $id)
        {
	        $set = "set".ucfirst((string)$id['nombre']);			
	        $elem->$set($row[(string)$id['columna']]);
        }
        
        //cargo las propiedades
        $propiedades = $this->_xmlMapping->propiedad;
        
        $antes = time();
   
        foreach($propiedades as $prop)
        {
            $nombreProp = (string)$prop['nombre'];
            if(!empty($nombreProp))
            {
                $set = "set".ucfirst($nombreProp);
                $valor = $row[(string)$prop['columna']];
                
                if(isset($prop->{"data-source"})) //busco el valor para la propiedad del datasource especificado
                {
                    $ds = $prop->{"data-source"};
                    $db = $this->getConexion((string)$ds['nombre']);
                    if($rs = $db->Execute("SELECT {$prop['columna']} FROM {$ds['tabla']} WHERE {$ds['clave']} = {$elem->getId()}"))
                    {
                        if($r = $rs->FetchRow())
                            $valor = $r[(string)$prop['columna']];
                    }
                }
                if(method_exists($elem,$set))
                {
                    if(isset($prop['tipo'])) //si es con tipo actualizo el id
                    {
                        $dao = $this->_newDaoClase($prop['tipo']);
                        $elemRelac = $dao->findById($valor);
                        $elem->$set($elemRelac);
                    }
                    else 
                    //si está definida otra data source no espero que esté en el row
                    {
                         $elem->$set($valor);
                    }
                }
            }
        }
 
        //cargo las listas
        $propiedades = $this->_xmlMapping->uno-a-muchos;
        if($propiedades != null) 
        foreach($propiedades as $prop)
        {
            $set = "set".ucfirst((string)$prop['nombre']);
            if(isset($prop['tipo'])) //todos deben tener tipo
            {
                $dao = $this->_newDaoClase($prop['tipo']);
                
                $colName = "{$prop['columna']}";
                if(is_a($this->_db,'ADODB_mysql') || is_a($this->_db,'ADODB_mysqli')) //acá me aseguro por tablas con espacios en mysql
                    $colName = "`{$colName}`";
                
                $elemsRelac = $dao->findBy(new Criterio("{$colName} = ".$elem->getId().""));
                $elem->$set($elemsRelac);
            }
        }
        
        //print "Tiempo $elem_name: ".(time()-$antes). "<br>";
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
    function findBy($filtro = null,$order=null,$count=null,$limit=null){
        
        if(!empty($this->baseFindBySQL))
            $sql = $this->baseFindBySQL;
        else
        {
            $tabla = $this->tableName;
            
            if(is_a($this->_db,'ADODB_mysql') || is_a($this->_db,'ADODB_mysqli')) //acá me aseguro por tablas con espacios en mysql
                $tabla = "`{$tabla}`";
            
            $sql = "SELECT * FROM {$tabla}";
        }
        
        if($filtro != null && $filtro->getCondicion()!='') 
        	$sql .= " WHERE " . $filtro->getCondicion();
        
        if(!isset($order)) 
        	$order = $this->defaultOrder;
        
        if(isset($order))    
            $sql .= " ORDER BY {$order}";
            
         if(isset($count))             
         {
            $sql .= " LIMIT {$count}";
            if(isset($limit))
            	$sql .= " OFFSET {$limit}";
         }
                
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
     * Genera una lista de todos los objetos de los cuales accede a datos esta clase
     */
    function findAll()
    {
    	return $this->findBy();
    }
    
    /**
     * Guarda creando si no existe o actualizando si existe a partir de una instancia de la entidad
     * @param object $elem
     */
    function save(&$elem) {
        
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
        
        $ret = $this->_db->AutoExecute((string)$this->tableName,$buf,$mode,$where);
		
		if(!$ret)
        {
            $this->_lastError =  $this->_db->ErrorMsg();
        }
        
        if($mode == 'INSERT')
        {
            $id = $this->_xmlMapping->id;
            $set = "set".ucfirst($id['nombre']);
            $elem->$set($this->_db->Insert_ID());
        }
        
        return $ret;
    }
    
    function getLastInsertId()
    {
    	return $this->_db->Insert_ID();
    }
    
    /**
     * Genera la el criterio de condición de id usada para la actualización y eliminación
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
    
    public function getLastError()
    {
    	print $this->_lastError;
    }
    
    /**
     * Elimina de la base de datos el elemento con el id dado
     * @param integer $id 
     */
    function deletePorId($id)
    {
    	$tn = "{$this->tableName}";
        if(is_a($this->_db,'ADODB_mysql') || is_a($this->_db,'ADODB_mysqli')) //acá me aseguro por tablas con espacios en mysql
           $tn = "`{$tn}`";
        $sql = "DELETE FROM {$tn} WHERE ".$this->getCriterioId($id)->getCondicion();
        return $this->_db->Execute($sql) or $this->_lastError = $this->_db->ErrorMsg();
    }
}
