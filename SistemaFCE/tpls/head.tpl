{*smarty*}
		<title>{$appName} - {$titulo}</title>
	
	<!-- JS -->
	<script type="text/javascript" src="/js/jquery/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="/js/jquery/jquery-ui-1.9.2.custom.min.js"></script>
	
	{if isset($jsModulo) && !$noLoadJsMod}
		<script type="text/javascript" src="js/{$jsModulo}.js"></script>
	{/if}
	{$jsIncludes}
	
	<!-- CSS -->
	
	{if isset($defaultCssFile)}
		<link rel="stylesheet" href="{$defaultCssFile}" type="text/css">	
	{/if}
	
	{if isset($gridCssFile)}
		<link rel="stylesheet" href="{$gridCssFile}" type="text/css">	
	{/if}
	
	{if isset($jQueryCss)}
		<link rel="stylesheet" href="{$jQueryCss}" type="text/css">
	{/if}

	{if isset($cssModulo)}
		<link rel="stylesheet" href="css/{$cssModulo}.css" type="text/css">
	{/if}	
	
	
	{$cssIncludes}
	