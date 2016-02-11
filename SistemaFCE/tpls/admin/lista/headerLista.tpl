{*smarty*}
<header>
	<h6>{$tituloLista}</h6>
	<ul class="tools">
		<li class="button">{include file="$pQnBotonAltaTpl" modName="$modName" entidad="$claseEntidad"}</li>
		<li>{include file="$pQnFormFiltroTpl"}</li>
	</ul>
</header>