<?php
require_once 'SistemaFCE/util/Configuracion.class.php';
require_once 'datos/criterio/Criterio.class.php';
/**
 * 
 * Modulo que permite que todo sistema FCE senga RESTfull
 * usando /entidad/
 * @author lucas.vidaguren
 *
 */
class RESTMod {
	
	private $nombreRecurso;
	
	function __construct()
	{
		
	}

	/**
	 * Devuelve el nombre del recurso desde la URI
	 * return string nombre del recurso, null si nen el sistema no hay un recurso con el nombre requerido
	 */
	public function getNombreRecurso()
	{
		if(!isset($this->nombreRecurso))
		{
			//por si no se está usando mod_rewrite cambio /index.php/recurso[/id] por /recurso[/id]
			$uri = preg_replace('/\/index.php/','',$_SERVER['REQUEST_URI']);
					
			if (preg_match('/([^\/]+)\//i', $uri, $rec)) {
				$this->nombreRecurso = $rec[1];
			}
		}
		return $this->nombreRecurso; 
	}
	
	/**
	 * Determina si la URI es un recurso REST conocido por el sistema
	 * todas las entidades mapeadas pueden accederse por este restfullmod
	 * @return boolean true si el sistema conoce el 
	 */
	function esUriRecurso()
	{
		$rec = $this->getNombreRecurso();
		//por si no se está usando mod_rewrite cambio /index.php/recurso[/id] por /recurso[/id]
		if($rec!=null) {
	        $config = Configuracion::getConfigXML();
	        $mappings = $config->mappings;
	        foreach($mappings->mapping as $map)
	        {
	        	if(stricmp($rec,$map['clase'])==0)
	        		return true;
	        }			
	    }
		return false;
	}
	
	/**
	 * Genera un Criterio a partir de variables de filtro en el request
	 * @param array $req
	 */
	function getFiltro($req)
	{
		$crit = new Criterio();
	}
	
	/**
	 * genera la string de orden para el findby a partir de lo pasado en el request
	 * @param array $req
	 */
	function getOrden($req)
	{
		
	}
	
	/**
	 * 
	 * Genera una lista 
	 * @param string $datos los datos enviados en el request (json)
	 * @param array $req el arreglo de request (obtenido de la query string)
	 * @return string la cadena json que representa la lista buscada
	 */
	function lista($datos,$req)
	{	
		$nombreDao = "Dao".ucfirst($this->getNombreRecurso());
		$dao = new $nombreDao();
		
		$crit = $this->getFiltro($req);
		$orden = $this->getOrden($req);
		
		$lista = $dao->findBy($crit,$orden);
		
		$json = '[';
		foreach($lista as $recurso)
		{
			$json .= "{";
			$json .= json_encode($recurso);
			$json .= "}";
		}
		$json .= ']';
	}
	
	
	/**
	 * Ejecuta la llamada de un
	 * Enter description here ...
	 */
	function ejecutar()
	{
		/*
		// find the function/method to call
    	$callback = NULL;
		if (preg_match('/rest\/([^\/]+)/', $_SERVER['REQUEST_URI'], $m)) {
	        if (isset($GLOBALS['RESTmap'][$_SERVER['REQUEST_METHOD']][$m[1]])) {
	            $callback = $GLOBALS['RESTmap'][$_SERVER['REQUEST_METHOD']][$m[1]];
	        }
	    }
	
	    if (is_callable($callback)) {
	        // get the request data
	        $data = NULL;
	        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	            $data = $_GET;
	        } else if ($tmp = file_get_contents('php://input')) {
	            $data = json_decode($tmp);
	        }
	
	        // execute the function/method and return the results
	        header("{$_SERVER['SERVER_PROTOCOL']} 200 OK");
	        header('Content-Type: text/plain');
	        print json_encode(call_user_func($callback, $data));
	    } else {
	        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
	        // print 404 page here
	        exit;
	    }*/
	}
	
}