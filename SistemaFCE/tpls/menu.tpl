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
				<a href="{$mItem.url}&itemId={$prefixItem}{$mItem.id}">{$mItem.tag}</a>
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
				{$active = 'active'} 
			{/if}
			
			<li class="has-sub {$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$itemId}">
					<i class="icon-th-list icon-white"></i>
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu prefixItem="$itemId-"}

			</li>
		{else}
			<li class="{$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$itemId}">
					<i class="icon-th-list icon-white"></i>
					{$mItem._.tag}
				</a>
			</li>
		{/if}
			
	{/if}

{/foreach}
</ul>