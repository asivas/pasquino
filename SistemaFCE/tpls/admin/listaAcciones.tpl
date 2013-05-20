{*smarty*}
{* requiere que se le asignen:
	 $modName string el nombre de modulo
	 $entidad Entidad objeto de la entidad a la cual se le efectuarían las acciones 
*}
<div class="lista-acciones">
	<div class="button">
		<a href="?mod={$modName}&accion=modif&id={$entidad->getId()}"><span class="ui-icon ui-icon-pencil"></span></a>
	</div> 				
	<div class="button">
		<span class="ui-icon ui-icon-trash" onclick="eliminar({$entidad->getId()},'{$modName}','{$entidad->toString()}');"></span>
	</div>
</div>