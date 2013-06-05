{*smarty*}

{assign var=itemId value=$itemId}

{if isset($smarty.get.itemId)}
	{$cItemId = $smarty.get.itemId}
{else}
	{$cItemId = 1}
{/if}


<ul>
{foreach from=$menuItems item=mItem key=nomMenu}
	{$itemId = $itemId + 1}
	
	{$active = ''}
	{if $itemId == $cItemId} {$active = 'active'} {/if}

	{if !is_array($mItem._) }
		{if  $nomMenu!='_'} {* evito este xq ya se hizo en la llamada anterior*}
		<li class="{$active}">
			<a href="{$mItem.url}&itemId={$itemId}">{$mItem.tag}</a>
		</li>
		{/if}
	{else}
		{if count($mItem) > 1}
			{if ($cItemId >= $itemId) && ($cItemId <= ($itemId + count($mItem)))} {$active = 'active'} {/if}
			<li class="has-sub {$active}">
				<a href="{$mItem._.url}&itemId={$itemId}">
					<i class="icon-th-list icon-white"></i>
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu itemId=$itemId}
				{$itemId = $itemId + count($mItem)}
			</li>
		{else}
			<li class="{$active}">
				<a href="{$mItem._.url}&itemId={$itemId}">
					<i class="icon-th-list icon-white"></i>
					{$mItem._.tag}
				</a>
			</li>
		{/if}
			
	{/if}

{/foreach}
</ul>