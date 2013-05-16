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

<div class="lista">
	<header>
		<h6>Titulo</h6>
		<ul class="tools">
			<li>{include file="$pQnBotonAltaTpl" modName="$modName" entidad="$claseEntidad"}</li>
			<li>{include file="$pQnFormFiltroTpl"}</li>
		</ul>
		
	</header>
	<div style='clear:both'></div>
	{include file="$pQnGridTpl" columnsList=$listaColumnas objectsList=$laLista}
</div>