<?php
namespace pQn\SistemaFCE\modulo;


use pQn\SistemaFCE\dao\DaoUsuario;
use pQn\SistemaFCE\util\Configuracion;

class BaseUsuarioMod extends BaseMod{
    
    protected $_daoU;
    
    function __construct($skinDirName=null)
    {
    	$this->_daoU = DaoUsuario::getInstance();
        parent::__construct($skinDirName);
        $tConf = Configuracion::getTemplateConfigByDir($templateDir);
    	$this->_tilePath = Configuracion::findTplPath($tConf,'Admin');
        
    }
    
    function lista()
    {
        $usuarios = $this->_daoU->findBy();
        
        $this->smarty->assign('permisos',$this->_getPermisosVigentes());
        $this->smarty->assign('usuarios',$usuarios);
        
        $this->mostrar('usuario/listar.tpl');
    }  
    
    function accionImportar($req)
    {
        $usr = new Usuario();
        $usr->setId($req['id_usuario']);
        $usr->setPermisos("");
        $this->_daoU->save($usr);
        
        //una vez creado lo vuelvo a leer
        $this->form($req);
    }  
    
    function formImportar()
    {
        $usuarios = $this->_daoU->findPosiblesUsuarios();
        $this->smarty->assign('usuarios',$usuarios); 
        $this->mostrar('usuario/importar.tpl');
    }
    
    function form($req=null)
    {
    	if(!isset($req)) //form de alta
        {
        	$this->formImportar();
            return;
        }
        
        $usuario = $this->_daoU->findById($req['id_usuario']);
        
        $this->smarty->assign('permisos',$this->_getPermisosVigentes());
        $this->smarty->assign('usuario',$usuario);
        
        $this->mostrar('usuario/formulario.tpl');
    }
    
    function baja($req)
    {
    	$this->_daoU->deletePorId($req['id_usuario']);
    }
    
    function modificacion($req)
    {
        $usuario = $this->_daoU->findById($req['id_usuario']);
        
        $permisosPosibles = $this->_getPermisosVigentes();
        
        foreach($permisosPosibles as $perm)
        {
        	if(isset($req[$perm]))
                $usuario->agregarPermiso($perm);
            else
                $usuario->quitarPermiso($perm);
        }
        
        $this->_daoU->save($usuario);
    }
    
    protected function _getArrPermisos(&$permisos,$permisosXml)
    {
    	if(empty($permisosXml)) return;
        
        if(empty($permisosXml->permiso)) return;
        
        foreach($permisosXml->permiso as $perm)
        {
            //lo guardo en el key y en el valor para que sea unico
            $permisos[(string)$perm] = (string)$perm;   
        }
        
    }
    
    protected function _getPermisosVigentes()
    {
    	$permisos = array();
        $modsConf = Configuracion::getModulosConfig();
        //recorro los modulos
        foreach($modsConf->modulo as $mod)
        {   
            $n = (string)$mod['nombre'];
            //busco los permisos de las acciones
            $accionesMod = $mod->acciones;
            if(isset($accionesMod->accion))
            {       
                foreach($accionesMod->accion as $acc)
                {   
                    $this->_getArrPermisos($permisos,$acc->permisos);
                }
            }
            
            //busco los permisos de los menues
            $menuMod = $mod->menuPrincipal;
            if(!empty($menuMod->menuItem))
            {
                foreach($menuMod->menuItem as $item)
                {
                    $this->_getArrPermisos($permisos,$item->permisos);
                }
            }
            
            //y los del men� principal
            $this->_getArrPermisos($permisos,$menuMod->permisos);
        }
        return $permisos;

    }
}
