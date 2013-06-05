{*smarty*}

{assign var=prefixItem value=$prefixItem}

{if isset($smarty.get.itemId)}
	{$cItemId = $smarty.get.itemId}
{else}
	{$cItemId = 1}
{/if}


<ul>
{foreach from=$menuItems item=mItem key=nomMenu}

	

	{if !is_array($mItem._) }
		{if  $nomMenu!='_'} {* evito este xq ya se hizo en la llamada anterior*}
			{$itemId = $mItem.id}
			
			{$active = ''}
			{if "$prefixItem$itemId" == $cItemId} {$active = 'active'} {/if}
			<li class="{$active}">
				<a href="{$mItem.url}&itemId={$prefixItem}{$mItem.id}">
					{* Icono de menu *}
					{if $mItem.icon != null}
						<i class="{$mItem.icon} icon-white"></i>
					{/if}
					{$mItem.tag}
				</a>
			</li>
		{/if}
	{else}
	
		{$itemId = $mItem._.id}
		
		{$active = ''}
		{if "$prefixItem$itemId" == $cItemId} {$active = 'active'} {/if}
		
		{if count($mItem) > 1}
		
			{* Marca padre (revisar tercer nivel)*}
			{$aCItemId = explode('-',$cItemId)}
			{$ini = $aCItemId[0]}
			{if "$prefixItem$itemId" ==  $ini } 
				{$active = 'active open'} 
			{/if}
			
			<li class="has-sub {$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$itemId}">
					
					{* Icono de menu *}
					{if $mItem._.icon != null}
						<i class="{$mItem._.icon} icon-white"></i>
					{/if}
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu prefixItem="$itemId-"}

			</li>
		{else}
			<li class="{$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$itemId}">
					{* Icono de menu *}
					{if $mItem._.icon != null}
						<i class="{$mItem._.icon} icon-white"></i>
					{/if}
					{$mItem._.tag}
				</a>
			</li>
		{/if}
			
	{/if}

{/foreach}
</ul>