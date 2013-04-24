<?php
require_once 'SistemaFCE/entidad/Entidad.class.php';

class BaseUsuario extends Entidad {
    
    private $permisos;
    private $nombre;
    private $apellido;
    
    function __construct() {
    }
    
    function getPermisos()
    {
    	return $this->permisos;
    }
    
    function setPermisos($permisos)
    {
    	$this->permisos = $permisos;
    }
    
    function getNombre()
    {
        return $this->nombre;   
    }
    
    function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
    
    function getApellido()
    {
        return $this->apellido;   
    }
    
    function setApellido($apellido)
    {
        $this->apellido = $apellido;
    }
    
    function agregarPermiso($permiso)
    {
    	if(!$this->tienePermiso($permiso))
            $this->permisos .= "\n$permiso";
    }
    
    function quitarPermiso($permiso)
    {
    	$this->permisos = str_replace("\r\n$permiso","",$this->permisos);
        if($this->tienePermiso($permiso))
            $this->permisos = str_replace("$permiso","",$this->permisos); 
    }
    
    function tienePermiso($permiso)
    {
    	return strpos($this->getPermisos(),$permiso)!==FALSE;
    }
}
