<?php
/**
 * Se define la clase abstracta logger que permite extenderse para 
 * hacer entradas de registro para una aplicaci�n
 * 
 * @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 * @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
 *
 * @package      datos
 * @subpackage   logger
 * @version      0.2
 */

/**
 * Se utiliza cuando se registra simplemente un aviso
 */
if(!defined('LOG_NOTICE'))
	define('LOG_NOTICE',1);
/**
 * Se utiliza cuando se registra una adveretencia
 */
if(!defined('LOG_WARNING'))
	define('LOG_WARNING',2);
/**
 * Se utiliza cuando se registra un error
 */
if(!defined('LOG_ERROR'))
	define('LOG_ERROR',3);

/**
 * Define los metodos y se plantean los que deben definirse cuando se extiende esta clase
 */
class logger {
    var $_modulo; 
    
    /**
     * asigna las variables de instancia
     * @param string $modulo determina para que modulo o apliclaci�n se registraran entradas
     */
    function logger($modulo='core')
    {
        $this->_modulo = $modulo;
    }
    
    /**
     * modifica el modulo o aplicaci�n para la cual se registran eventos
     * @param string $modulo nuevo nombre de archivo
     */
    function setModulo($modulo) { $this->_modulo = $modulo;  }
    
    /**
     * recupera el modulo o aplicacion para la cual se est�n registrando eventos
     * @return string nombre de modulo acutal
     */
    function getModulo() { return $this->_modulo; }
    
    /**
     * Cuando se estiende debe generar el registro de un mensaje
     * @param string $msg el mensaje registrado
     * @param integer $type el tipo de mensaje
     */
    function log($msg,$type)
    {        
        //Este metodo debe ser definido
    }
}