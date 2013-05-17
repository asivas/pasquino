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
		<li>
		<a href="{$mItem._.url}">{$mItem._.tag}</a>
		{if count($mItem) > 1}
			{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu}
		{/if}
		</li>
	{/if}

{/foreach}
</ul>