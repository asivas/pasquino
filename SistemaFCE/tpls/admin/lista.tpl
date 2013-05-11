{*smarty*}
{*
	@vars
	$listaColumnas
	$laLista
	$modName
	$claseEntidad
	
	$pQnFormFiltroTpl
	$pQnBotonAltaTpl
	$pQnGridTpl
	
*}
{include file="$pQnFormFiltroTpl"}
{include file="$pQnBotonAltaTpl" modName="$modName" entidad="$claseEntidad"}
<div style='clear:both'></div>
{include file="$pQnGridTpl" columnsList=$listaColumnas objectsList=$laLista}
