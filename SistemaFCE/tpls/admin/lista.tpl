{*smarty*}
{*
	requiere $columnList y $theList
*}
{include file='common/admin/filtro.tpl' nombreCampoFiltro='filtroNada'}
{include file='common/admin/botonAlta.tpl' modName='cobertura' entidad='Cobertura'}
<div style='clear:both'></div>
{include file="$pQnGridTpl" columnsList=$columnList objectsList=$theList}
