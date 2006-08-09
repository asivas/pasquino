<?php

class mysqlQueryBuilder{
    var $_sql;
    
    var $_action;
    
    var $tablas;
    var $conds;
    var $orders;
    var $fields;
	var $limitStart;
	var $limitCount;
    
    function mysqlQueryBuilder()
    {
        //pongo select como acción por defecto
        $this->_action = "SELECT";
    }
    
    function setAction($accion)
    {
        $this->_action = $accion;
    }
    
    function addCond($cond,$logic='AND')
    {
        $this->conds[]['logic'] = $logic;
        $this->conds[]['cond'] = $cond;
    }
    
    function addTabla($tabla,$alias='')
    {
        if(!empty($alias))
            $tabla .= " {$alias}";
        
        //print "agregando tabla {$tabla}<br>";
        $this->tablas[] = $tabla;
        
    }

    function addFieldValue($field,$value='')
    {
        //print "agregando campo {$field} {$value}<br>";
        $this->fields[$field] = $value;
    }

    function addOrder($order)
    {
        $this->orders[] = $order;
    }

    function rmvOrder($order)
    {
        if($key = array_search($order,$this->orders))
            unset($this->orders[$key]);
    }

	function setLimitStart($limit)
    {
        $this->limitStart = $limit;
    }

	function setLimitCount($limit)
    {
        $this->limitCount = $limit;
    }

    function buildCond()
    {
        if(!empty($this->conds))
            foreach($this->conds as $cnd)
            {
                if(empty($sqlCond))
                    $sqlCond = $cnd['cond'];
                else
                    $sqlCond .= " {$cnd['logic']} {$cnd['cond']}";
            }            
        return $sqlCond;
    }

    function buildOrder()
    {
        if(!empty($this->orders))
            foreach($this->orders as $ord)
            {
                if(empty($sqlOrder))
                    $sqlOrder = $ord;
                else
                    $sqlOrder .= ",{$cnd}";
            }
        return $sqlOrder;
    }

    function buildTablas()
    {
        foreach($this->tablas as $tabla)
            if(empty($tablas))
               $tablas = $tabla;
            else
                $tablas .= ",{$tabla}";
        return $tablas;
    }

	/**
	 * 
	 */
	function buildLimit()
    {
        if($this->limitCount)
        {
            $limit = $this->limitCount;
            if($this->limitStart)
                $limit = "{$this->limitStart}, $limit";
        }
        return  $limit; 
    }

    /**
     * 
     */
    function buildFieldValues()
    {
        $upperAction = strtoupper($this->_action);
		switch($upperAction)
		{
		    case 'SELECT':
		        foreach($this->fields as $field => $dummy)
		        {
    		        if(empty($strFieldsValues))
    		            $strFieldsValues = $field;
    		        else
    		            $strFieldsValues .= ",$field";
    		    }
			break;
			case 'INSERT':
			    foreach($this->fields as $field => $value)
    		        if(empty($strFields))
    		        {
    		            $strFields = $field;
    		            $strValues = "'{$value}'";
    		        }
    		        else
    		        {
    		            $strFields .= ",$field";
    		            $strValues .= ",'$value'";
    		        }
    		    $strFieldsValues = "({$strFields}) VALUES ({$strValues})";
			break;
			case 'UPDATE':
			    foreach($this->fields as $field => $value)
    		        if(empty($strFields))
    		            $strFieldsValues = "$field='$value'";
    		        else
    		            $strFieldsValues .= ",$field='$value'";
			break;
		}
		
		return $strFieldsValues;
		
    }

    function buildSQL($action=NULL)
    {
        if(isset($action))
            $this->setAction($action);
        
        $cond           = $this->buildCond();
        $order          = $this->buildOrder();
        $tablas         = $this->buildTablas();
        $fieldsValues   = $this->buildFieldValues();
        $limit          = $this->buildLimit();
        
		$upperAction = strtoupper($this->_action);
		switch($upperAction)
		{
			case 'SELECT':
				$sqlQuery = "SELECT {$fieldsValues} FROM {$tablas}";
				if(!empty($cond)) $sqlQuery .= " WHERE {$cond}";
				if(!empty($order)) $sqlQuery .= " ORDER BY {$order}";
				if(!empty($limit)) $sqlQuery .= " LIMIT {$limit}";				
			break;
			case 'INSERT':
			    $sqlQuery = "INSERT INTO {$tablas} {$fieldsValues}";				
			break;
			case 'UPDATE':
			    $sqlQuery = "UPDATE {$tablas} SET {$fieldsValues}";
				if(!empty($cond)) $sqlQuery .= " WHERE {$cond}";
			break;
			case 'DELETE':
			    $sqlQuery = "DELETE FROM {$tablas}";
				if(!empty($cond)) $sqlQuery .= " WHERE {$cond}";
			break;
		}
		
		$this->_sql = $sqlQuery;
    }

    function getSQL()
    {
        $this->buildSQL();
        return $this->_sql;
    }
}