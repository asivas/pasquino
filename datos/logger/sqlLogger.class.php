<?php
/**
 * Se define la clase sqlLogger que permite hacer entradas de registro para una aplicación
 * en una base de datos
 * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 *
 * @package      datos
 * @subpackage   logger
 * @version      0.2
 */

/**
 * La definicio clase abrtacta que se extenderá
 */
require_once('datos/logger/logger.class.php');


/**
 * para conexion a la base de datos
 */
require_once('datos/adodb/adodb.inc.php');

/**
 * Genera un log en una tabla SQL o en un archivo de texto
 */
class sqlLogger extends logger {
    /**
     * Objeto de conexión a la base de datos (adodb)
     * 
     */
    var $_db;
    
    var $_table;
    
    var $modulo; 
    
    /**
     * asigna las variables de instancia segun el valor del parametro $mode
     * @param string $mode puede ser 'db' o 'file' determina que tipo de log se utilizará
     * @param mixed $db es la referencia al objeto adodb
     */
    function sqlLogger($db,$table=NULL,$modulo=NULL)
    {
        parent::logger($modulo);
        
        $this->_db = $db;
        if(isset($table))
            $this->_table = $table;
        else
            $this->_table = "log";
        
        
        /*
         * Debería corroborar la existencia de la tabla 
         * y si no está y tiene derechos crearla
         */
            
    }
    /**
     * Genera el registro de un mensaje $msg en la base de datos
     * @param string $msg el mensaje registrado
     * @param integer $type el tipo de mensaje
     */
    function log($msg,$type)
    {
       $db = $this->_db;
       $registro['msg'] = $msg;
       $registro['fecha'] = 'NOW()';
       $registro['tipo'] = $type;
       $registro['modulo'] = $this->_modulo;
       $db->AutoExecute($this->_table,$registro,'INSERT');       
       //$db->Execute("INSERT INTO $this->_table (msg,fecha,tipo,modulo) VALUES ('$msg',NOW(),'$type','$this->_modulo')");
    }
}