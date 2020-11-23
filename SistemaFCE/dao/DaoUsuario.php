<?php
namespace pQn\SistemaFCE\dao;

class DaoUsuario extends DaoBase {
    
    /**
     * Genera una lista de posibles usuarios (arreglo de nombre,apellido,id desde econtrol)
     */
    function findPosiblesUsuarios()
    {	
        $db = $this->getConexion('econtrol');
        $rs = $db->Execute("SELECT id as idUsuario,nombre,apellido FROM usuarios WHERE borrado IS NULL ORDER BY apellido,nombre");
        while($row = $rs->FetchRow())
        {
        	if(!$this->findById($row['idUsuario']))
                $posibles[] = $row;
        }
        return $posibles;
    } 
}
