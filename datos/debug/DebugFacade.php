<?php
namespace pQn\datos\debug;

class DebugFacade {

    function DebugFacade() {
    }
    
    static function inicio()
    {
    	$GLOBALS['debug_inicio'] = time();
    	
    	//print "{$GLOBALS['debug_inicio']}<br>";
    }
    
    static function fin($print=true)
    {
    	$fin = time();
    	$tiempo = ($fin - $GLOBALS['debug_inicio']);
    	if($print)
    		print "Inicio: {$GLOBALS['debug_inicio']}<br>Fin: $fin<br>Tiempo Transcurrido: $tiempo<br>";
    	return $tiempo;
    }
    
    static function dump($var,$die = false)
    {
    	print "<pre>";
    	var_dump($var);
    	print "</pre>";
    	if($die) die;
    }
}