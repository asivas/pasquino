{*smarty*}

{$id = $item->getId()}
{if is_array($id)}
	{$id = implode('-',$id)}
{/if}

<article type='{get_class($item)}' itemId='{$id}'>
	<ul>
	{foreach from=$columnsList key=columnName item=property}
		<li class='{$property}'>
			<div class="data"> 
				{assign var=data value=$facade->getPropiedadMod($property,$item)}
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