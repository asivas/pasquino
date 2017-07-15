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
* Tiene funciones de traducci�n entre formato e internaci�n de fechas y tiemo
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
    
    static function segundosAStrTiempo($segundos)
    {
        $horas = $segundos>0?floor($segundos/3600):ceil($segundos/3600);
        $minutos = abs(ceil($segundos/60) - $horas*60);
        $horasFmt = "%02d";
        if($horas<0) $horasFmt = "%03d";
        return sprintf("{$horasFmt}:%02d",$horas,$minutos);
    }


    
    /**
     * Convierte una fecha en el formato
     */
    static function fechaArgtotime($fecha)
    {
        if(preg_match ("/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4}) ([0-9]{1,2}):([0-9]{1,2})/", $fecha, $dmYHM)) {
            return strtotime("{$dmYHM[3]}-{$dmYHM[2]}-{$dmYHM[1]} {$dmYHM[4]}:{$dmYHM[5]}");
        }
        if(preg_match ("/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/", $fecha, $dmY)) {
            return strtotime("{$dmY[3]}-{$dmY[2]}-{$dmY[1]}");
        }
        //Fecha y hora con formato de
        if(preg_match ("/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2}) ([0-9]{1,2}):([0-9]{1,2})/", $fecha, $dmYHM)) {
            if($dmYHM[3]>date('y')+5 && $dmYHM[3]<70)
                $dmYHM[3] = "19{$dmYHM[3]}";
            return strtotime("{$dmYHM[3]}-{$dmYHM[2]}-{$dmYHM[1]} {$dmYHM[4]}:{$dmYHM[5]}");
        }
        if(preg_match ("/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2})/", $fecha, $dmY)) {
            if($dmY[3]>date('y')+5 && $dmY[3]<70)
                $dmY[3] = "19{$dmY[3]}";
            return strtotime("{$dmY[3]}-{$dmY[2]}-{$dmY[1]}");
        }

        if(!empty($fecha))
          return strtotime($fecha);
        
        return time();
    }
    
    /**
     * Calcula la diferencia de dias entre 2 fechas
     */
    static function diasEntreFechas($timestamp_inicio,$timestamp_fin)
    {
    	$inicio = strtotime(date("Y-m-d",$timestamp_inicio));
    	$fin = strtotime(date("Y-m-d",$timestamp_fin));

		if($inicio <= $fin)
		{
			return ($fin - $inicio)/(3600*24);	
		}
		else return 0;
    }
    
    /**
     * Devuelve edad a hoy dado un timestamp
     * EDIT: se puede pasar en que fecha tenia esa edad con un segundo timestamp
     */
    static function edad($personTS,$whenTS = null) {
    	$fecha = date("Y-m-d",$personTS);
    	if($whenTS == null)
    		$whenTS = strtotime('now');
    	list($Y,$m,$d) = explode("-",$fecha);
    	return( date("md",$whenTS) < $m.$d ? date("Y",$whenTS)-$Y-1 : date("Y",$whenTS)-$Y );
    }
}