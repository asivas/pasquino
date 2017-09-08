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

<div class="lista span12" {if isset($sort)}sorted="{$sort}" sortdir="{$sortSentido}"{/if}>
	
	{include file="$pQnHeaderListaTpl"}
	
	<div style='clear:both'></div>
	
	{include file="$pQnGridTpl" columnsList=$listaColumnas objectsList=$laLista}
	
	
	{include file="$pQnFooterListaTpl"}
	
	
</div>