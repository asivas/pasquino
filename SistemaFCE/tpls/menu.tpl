{*smarty*}
{if isset($smarty.get.itemId)}
	{$cItemId = $smarty.get.itemId}
{else}
	{$nMod = $smarty.get.mod|lower}
	{$nAccion = $smarty.get.accion|lower}
	{if empty($nMod) || empty($nAccion)}
		{$cItemId = 1}
	{/if}
{/if}

<ul>
{foreach from=$menuItems item=mItem key=nomMenu}

	
	{if !is_array($mItem._)}
		{$cItem = $mItem}
	{else}
		{$cItem = $mItem._}
	{/if}

	{$active = ''}
	{*if !$markActive*}
		{$id = $cItem.id}
		{if isset($cItemId)}
			{if "$prefixItem$id" == $cItemId}{$active = 'active'}{$markActive = true}{/if}
		{else}
			{if !empty($nMod) && !empty($nAccion)}
				{if stripos($cItem.url,"mod=$nMod") !== false && stripos($cItem.url,"accion=$nAccion") !== false}
					{$active = 'active'} 
					{$markActive = true}
				{/if}
			{/if}
		{/if}
	{*/if*}

	{if !is_array($mItem._) }
		{if  $nomMenu!='_'} {* evito este xq ya se hizo en la llamada anterior*}

			<li class="{$active}">
				<a href="{$mItem.url}&itemId={$prefixItem}{$cItem.id}">
					{* Icono de menu *}
					{if $mItem.icon != null}
						<i class="{$mItem.icon} icon-white"></i>
					{/if}
					{$mItem.tag}
				</a>
			</li>
		{/if}
	{else}
	

		{if count($mItem) > 1}
			<li class="has-sub {$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$cItem.id}">
					
					{* Icono de menu *}
					{if $mItem._.icon != null}
						<i class="{$mItem._.icon} icon-white"></i>
					{/if}
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu prefixItem="$id-" markActive=$markActive}

			</li>
			
		{else}
			<li class="{$active}">
				<a href="{$mItem._.url}&itemId={$prefixItem}{$cItem.id}">
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