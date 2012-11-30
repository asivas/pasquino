<?php 
require_once 'SistemaFCE/modulo/BaseMod.class.php';
function smarty_modifier_resaltar($str,$filtro,$parameter = array('background-color' => '#FFFFBF'))
{	
	return BaseMod::resaltar($str, $filtro, $parameter);
}
