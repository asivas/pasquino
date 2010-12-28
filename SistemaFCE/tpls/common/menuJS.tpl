{* smarty *}
{*
myCoolMenu.makeMenu(name, parent_name, text, link, target, width, height, regImage, overImage, regClass, overClass , align, rows, nolink, onclick, onmouseover, onmouseout) 
*}
{if $nomPMenu eq ""}
{assign var="urlFlecha" value="$dir_images/menu_arrow_down.gif"}
{else}
{assign var="urlFlecha" value="$dir_images/menu_arrow_left.gif"}
{/if}
{foreach from=$mItems item=mItem key=nomMenu}
	{if $nomMenu != '_'}
		{if $mItem._|is_array}
oCMenu.makeMenu('{$nomMenu}','{$nomPMenu}','{$mItem._.tag}&nbsp;&nbsp;<img src="{$urlFlecha}">','{$mItem._.url}')
{include file="common/menuJS.tpl" mItems=$mItem nomPMenu=$nomMenu}
		{else}
	oCMenu.makeMenu('{$nomMenu}','{$nomPMenu}','{$mItem.tag}','{$mItem.url}')
		{/if}
	{/if}
{/foreach}