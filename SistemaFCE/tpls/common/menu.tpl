{*smarty*}
<script language="JavaScript" src="skins/{$skin}/js/initMainMenu.js"></script>
{if $menuItems != ''}
<script>
{include file="common/menuJS.tpl" mItems=$menuItems}
oCMenu.construct()
</script>
{/if}
<div id="btnCerrar" onclick="location.href = './?logout'"></div>