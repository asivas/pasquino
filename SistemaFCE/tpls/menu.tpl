{*smarty*}
<ul>
{foreach from=$menuItems item=mItem key=nomMenu}
	{if !is_array($mItem._) }
		{if  $nomMenu!='_'} {* evito este xq ya se hizo en la llamada anterior*}
		<li>
			<a href="{$mItem.url}">{$mItem.tag}</a>
		</li>
		{/if}
	{else}
		{if count($mItem) > 1}
			<li class="has-sub">
				<a href="{$mItem._.url}">
					<i class="icon-th-list icon-white"></i>
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu}
			</li>
			{else}
				<li>
					<a href="{$mItem._.url}">
						<i class="icon-th-list icon-white"></i>
						{$mItem._.tag}
					</a>
				</li>
		{/if}
			
	{/if}

{/foreach}
</ul>