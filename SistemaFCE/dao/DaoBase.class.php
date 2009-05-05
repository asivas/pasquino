<?php

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
    
    
    function DaoBase() {
        $this->_db = &ADONewConnection(Configuracion::dbms); # eg 'mysql' or 'postgres'
        $this->_db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->_db->Connect(Configuracion::dbHost, Configuracion::dbUser, Configuracion::dbPassword, Configuracion::dbName);
    }
    
    protected abstract function _buffer($elem){}
    protected abstract function crearObjetoEntidad($row) {}
    
    
    function getDb()
    {
    	return $this->_db;
    }
    
    function findBy($filtros = null,$order=null){
        
        $sql = $this->baseFindBySQL;
        
        $sql .= $this->buildCond($filtros);
        
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
    
    function buildCond($filtros)
    {
    	$cond = "";
        if(isset($filtros) && is_array($filtros))
        {
            foreach($filtros as $field => $val)
            {
                if($field{0}!='_')
                {
                    if(empty($cond))
                        $cond = " WHERE ";
                    else
                        $cond .= " AND ";
                    $cond .= "{$field} = '{$val}'";    
                }
            }
        }
        return $cond;
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
