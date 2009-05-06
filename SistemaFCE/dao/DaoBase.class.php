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
    protected $tableNale;
    
    /**
     * @var object Objeto tipo SimpleXML para leer lo mappings de clases
     */
    protected $_xmlMapping;
    /**
     * @var string Ruta del archivo de mappings de la clase
     */
    protected $_xmlMappingFile;
    
    /**
     * Constructor de DaoBase
     */
    function DaoBase() {
        $this->_db = &ADONewConnection(Configuracion::dbms); # eg 'mysql' or 'postgres'
        $this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->_db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
        
        $this->loadMapping();
    }
    
    /**
     * Carga el mapping desde el archivo XML de mappings
     * @return object Objeto SimpleXML con el mapping
     */
    protected function loadMapping()
    {
        if(!isset($this->_xmlMapping))
        {
            $map = simplexml_load_file($this->_xmlMappingFile);
            $this->_xmlMapping = $map->clase[];
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
        
        $id = $this->_xmlMapping->id[0];
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
        
        $id = $this->_xmlMapping->id[0];
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
        foreach($clase->unoAMuchos as $prop)
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
        
        $sql = $this->baseFindBySQL;
        
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
        $sql = $this->baseFindBySQL;
        
        $id = $this->_xmlMapping->id[0];
        $nombreColId = $id['columna'];
        
        $c = new Criterio();
        
        $c->add("{$nombreColId} = '$idElemento'");
        
        $sql .= $c->getCondicion();
        
        if(!($rs = $this->_db->Execute($sql)))
            die($this->_db->ErrorMsg()." $sql");
        else
            return $this->crearObjetoEntidad($rs->FetchRow());
        
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
         * buscar si existe el codigo postal, si existe lo actualizo 
         */
        if($this->findById())
        {   
            $mode  = 'UPDATE';
            $where = $this->buildCond();
         }
        
        return $this->_db->AutoExecute($this->tableName,$buf,$mode,$where);
    }
}
