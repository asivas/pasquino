<?php 
use pQn\SistemaFCE\modulo\BaseMod;

function smarty_modifier_resaltar($str,$filtro,$parameter = array('background-color' => '#FFFFBF'))
{	
	return BaseMod::resaltar($str, $filtro, $parameter);
}
