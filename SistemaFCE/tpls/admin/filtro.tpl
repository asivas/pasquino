{*smarty*}
{if !isset($nombreCampoFiltro) } {$nombreCampoFiltro="filtroNombre"} {/if}
{if !isset($idFormFiltro) } {$idFormFiltro="formFiltro"} {/if}
<div class='filtro'>
	<form id="{$idFormFiltro}" method="POST">
		<input type="text" id="{$nombreCampoFiltro}" name="{$nombreCampoFiltro}" />
	</form>
</div>