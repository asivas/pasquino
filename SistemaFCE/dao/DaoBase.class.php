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
     * var array $filtroId
     */     
    protected $filtroId;
    /**
     * var String $tableName nomrbe de la tabla
     */
    protected $tableNale;
    
    /**
     * Constructor de DaoBase
     */
    function DaoBase() {
        $this->_db = &ADONewConnection(Configuracion::dbms); # eg 'mysql' or 'postgres'
        $this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->_db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
    }
    
    /**
     * Crea el arreglo buffer para guardar
     * @return array arreglo con datos con nombre de la columna de la base (nombreCol => valor)
     */
    protected abstract function _buffer($elem){}
    
    /**
     * Crea el objeto de la entidad a la cual logra el acceso el DAO
     * @return object el objeto con los datos a partir de $row
     * @param array $row arreglo con los datos obtenidos de la base en forma nombreCol => valor
     */
    protected abstract function crearObjetoEntidad($row) {}
    
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
    
    function save($elem) {
        
        $buf = $this->_buffer($elem);
        
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
