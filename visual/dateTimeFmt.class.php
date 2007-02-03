<?php
/**
* Se define la clase dateTimeFmt 
*
* @author       Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      visual
* @since 1.0 - 02/11/2006
*/

/**
* Tiene funciones de traducción entre formato e internación de fechas y tiemo
*
* @author       Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      visual
* @since 1.0 - 02/11/2006
*/
class dateTimeFmt {

    function dateTimeFmt() {
    }
    
    function segundosAStrTiempo($segundos)
    {
        $horas = $segundos>0?floor($segundos/3600):ceil($segundos/3600);
        $minutos = abs(ceil($segundos/60) - $horas*60);
        
        return sprintf("%02d:%02d",$horas,$minutos);
    }
    
    /**
     * Convierte una fecha en el formato
     */
    static function fechaArgtotime($fecha)
    {
        if(ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2})", $fecha, $dmYHM))
            return strtotime("{$dmYHM[3]}-{$dmYHM[2]}-{$dmYHM[1]} {$dmYHM[4]}:{$dmYHM[5]}");
        elseif(ereg ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $fecha, $dmY))
           return strtotime("{$dmY[3]}-{$dmY[2]}-{$dmY[1]}");
        
        if(!empty($fecha))
          return strtotime($fecha);
        
        return time();
    }
}