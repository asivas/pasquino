{*smarty*}

{* 

@usage
	
	{include file='common/admin/lista/objGrid.tpl' columnsList=$myModColumns objectsList=$myObjects} 

@vars

	$myModColumns =	
		array(
			'colName'=>'objProperty',
			...
		);

	$myObjects = 
		array(
			object1,
			object2,
			...
		);
*}

<div class='grid'>
	<header>
		<ul>
		{foreach from=$columnsList key=columnName item=property}
		<li class='{$property}'>
			<div class="data">
				{$columnName}
			</div>
		</li>		
		{/foreach}
		</ul>
	</header>	
	<div id="articles">
	{if empty($objectsList) }
		<article type="{$entidad}" id="{$entidad}" >
			<div class="info text-warning">No hay objetos de tipo {$entidad} con el filtro actual</div>
		</article>
	{/if}
	{foreach from=$objectsList item=object}
		{$id = $object->getId()}
		{if is_array($id)}
			{$id = implode('-',$id)}
		{/if}
		
		<article type='{get_class($object)}' itemId='{$id}'>
			<ul>
			{foreach from=$columnsList key=columnName item=property}
				<li class='{$property}'>
					<div class="data"> 
						{assign var=data value=$facade->getPropiedadMod($property,$object)}
						{if  ! strpos($data,'>') }
							{$data|resaltar:$filtroNombre}
						{else}
							{$data}
						{/if}
					</div>
				</li>
			{/foreach}
			</ul>
		</article>
	{/foreach}
	</div>
</div>

{* ANCHO COLUMNAS *}
<style>
section .grid ul li {
	width: {100 / $columnsList|count}% ;
}
	
</style>