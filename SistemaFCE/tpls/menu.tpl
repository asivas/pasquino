{*smarty*}

{if isset($smarty.get.mIt)}
	{$mItemName = $smarty.get.mIt}
{else}
	{$nMod = $smarty.get.mod|lower}
	{$nAccion = $smarty.get.accion|lower}
	{if ( empty($nMod) || empty($nAccion)) && !isset($nomMenu)} {* el !isset($nomMenu) es que no viene de rellamado*}
		{$active = 'active'}
	{/if}
{/if}

<ul>
{foreach from=$menuItems item=mItem key=nomMenu}


	{if is_array($mItem._)}
		{$cItem = $mItem._}
	{else}
		{$cItem = $mItem}
		{if  $nomMenu=='_'} {* evito este xq ya se hizo en la llamada anterior*}
			{continue}
		{/if}
	{/if}

	{*if !$markActive*}
		{$id = $cItem.id}
		{if isset($mItemName)}
			{if "$mItemName" == $cItem.name}{$active = 'active'}{$markActive = true}{/if}
		{else}
			{if !empty($nMod) && !empty($nAccion)}
				{if stripos($cItem.url,"mod=$nMod") !== false && stripos($cItem.url,"accion=$nAccion") !== false}
					{$active = 'active'}
					{$markActive = true}
				{/if}
			{/if}
		{/if}
	{*/if*}

	{if !is_array($mItem._) || count($mItem) <= 1}
			<li class="{$active}">
				<a href="{$cItem.url}">
					{* Icono de menu *}
					{if $cItem.icon != null}
						<i class="{$cItem.icon}"></i>
					{/if}
					{$cItem.tag}
				</a>
			</li>
	{else}
		{* if count($mItem) > 1 *}
			<li class="has-sub {$active}">
				<a href="{$cItem.url}">

					{* Icono de menu *}
					{if $mItem._.icon != null}
						<i class="{$cItem.icon}"></i>
					{/if}
					{$mItem._.tag}
					<span class="arrow"></span>
				</a>
				{include file="$pQnMenuTpl" menuItems=$mItem nomPMenu=$sNomMenu active=''}

			</li>
		{* /if *}
	{/if}
	{$active = ''}

{/foreach}
</ul>