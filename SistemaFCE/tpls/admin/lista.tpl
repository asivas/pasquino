{*smarty*}
{*
	requiere $columnList y $theList
*}
{include file="$pQnGridTpl"}
{include file="$pQnGridTpl" modName="$modName" entidad="$claseEntidad"}
<div style='clear:both'></div>
{include file="$pQnGridTpl" columnsList=$columnList objectsList=$theList}
