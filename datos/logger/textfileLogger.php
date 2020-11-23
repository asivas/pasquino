<?php
namespace pQn\datos\logger;
/**
 * Se define la clase textFilelogger que permite hacer entradas de registro para una aplicaci�n
 * en un archivo de texto
 *
 * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 *
 * @package      datos
 * @subpackage   logger
 * @version      0.2
 */

/**
 * Genera un log en  un archivo de texto
 * esta clase requiere PEAR::File
 */
class textfileLogger extends logger {
    /**
     * Nombre de archivo en el que se escribiran las entradas
     * @var string
     */
    var $_filename;
    
    /**
     * asigna las variables de instancia segun el valor del parametro $mode
     * @param string $fileName el nombre del archivo donde se guarda el registro
     */
    function textfileLogger($fileName,$modulo=NULL)
    {
        parent::logger($modulo);
        $this->_filename = $fileName;
    }
    
    /**
     * modifica el nombre de archivo de registro
     * @param string $fn nuevo nombre de archivo
     */
    function setFilename($fn) { $this->_filename = $fn;  }
    
    /**
     * recupera el nombre de archivo de registro actual
     * @return string nombre del archivo actual
     */
    function getFilename() { return $this->_filename; }
    
    /**
     * Genera el registro de un mensaje $msg en el archivo
     * @param string $msg el mensaje registrado
     * @param integer $type el tipo de mensaje
     */
    function log($msg,$type)
    {
        \File::writeLine($this->_filename,"({$type}) [{$this->_modulo}] {$msg}");
    }
}