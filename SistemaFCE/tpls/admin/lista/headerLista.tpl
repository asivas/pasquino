{*smarty*}
<header>
	<h6>{$tituloLista}</h6>
	<ul class="tools">
		<li>{include file="$pQnBotonAltaTpl" modName="$modName" entidad="$claseEntidad"}</li>
		<li>{include file="$pQnFormFiltroTpl"}</li>
	</ul>
</header>