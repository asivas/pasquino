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
			<li class='{$columnName}'>{$columnName}</li>		
		{/foreach}
		</ul>
	</header>
	{if empty($objectsList) }
		<article type="{$entidad}" id="$entidad" >
		<ul>
			<li>No hay objetos de tipo {$entidad} con el filtro actual</li>
		</ul>
		</article>
	{/if}
	<div>
	{foreach from=$objectsList item=object}
	<article type='{get_class($object)}' itemId='{$object->getId()}'>
		<ul>
		{foreach from=$columnsList key=columnName item=property}
			<li class='{$columnName}'>
				{assign var=data value=$facade->getPropiedadMod($property,$object)}
				{if  ! strpos($data,'>') }
					{$data|resaltar:$filtroNombre}
				{else}
					{$data}
				{/if}
			</li>
		{/foreach}
		<ul>
	</article>
	{/foreach}
	</div>
</div>