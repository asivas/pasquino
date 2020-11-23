<?php
namespace pQn\datos\criterio\Restricciones;

use pQn\datos\criterio\Restriccion;

class AllEq extends Restriccion {
    
    private $_nombresValores;
    
    function __construct($nombresValoresPropiedades) {
       $this->_nombresValores = $nombresValoresPropiedades;
       $this->operador = "=";
       $this->operadorH = " es igual a";
    }
    
    function toSqlString($clase=null)
    {
    	$sql = "";
        foreach($this->_nombresValores as $nombre => $valor)
        {
        	if($sql!='') $sql .= " AND ";
            
            $columna = $this->buscarNombreColumna($clase,$nombre);
            
            $sql .= " {$columna} {$this->operador} '{$valor}'";
        }
        return $sql;
    }
    
    function toString()
    {
        $str = "";
        foreach($this->_nombresValores as $nombre => $valor)
        {
            if($str!='') $str .= " y ";
            
            $str .= " {$nombre} {$this->operadorH} '{$valor}'";
        }
        return $str;
    }
    
    function toArray(){
        $a =  array("AND"=>array());
        foreach($this->_nombresValores as $nombre => $valor)
        {
        	$a['AND'][] = array($this->operador=>array($nombre,$valor));
        }
        return;
    }
}
