{*smarty*}
		<title>{$appName}{if isset($titulo)} - {$titulo}{/if}</title>
	
	{if isset($pQnFavicon)}
	<link rel="shortcut icon" href="{$pQnFavicon}"/>
	{/if}
	<!-- JS -->
	
	{if isset($pQnJQueryJs)}
		<script type="text/javascript" src="{$pQnJQueryJs}"></script>
	{/if}
	
	
	{* FIXME *}	
	<script type="text/javascript" src="/sistemafce/js/browser-update.js"></script>
	
	
	{if isset($pQnJQueryUiJs)}
		<script type="text/javascript" src="{$pQnJQueryUiJs}"></script>
	{/if}
	
	{if isset($jsModulo) && !$noLoadJsMod}
		<script type="text/javascript" src="js/{$jsModulo}.js"></script>
	{/if}
	
	{$jsIncludes}
	
		
	{* FIXME *}	
	<script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script>
	
	
	<!-- CSS -->
	{*	Todos los css por defecto (pQn) están incluidos dentro de $cssIncludes en BaseMod::assignHeadCss *}
	{$cssIncludes}
	