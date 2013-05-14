{*smarty*}
		<title>{$appName} - {$titulo}</title>
	
	<!-- JS -->
	
	{if isset($pQnJQueryJs)}
		<script type="text/javascript" src="{$pQnJQueryJs}"></script>
	{/if}
	
	{if isset($pQnJQueryUiJs)}
		<script type="text/javascript" src="{$pQnJQueryUiJs}"></script>
	{/if}
	
	{if isset($jsModulo) && !$noLoadJsMod}
		<script type="text/javascript" src="js/{$jsModulo}.js"></script>
	{/if}
	
	{$jsIncludes}
	
	<!-- CSS -->
	
	{if isset($pQnDefaultCss)}
		<link rel="stylesheet" href="{$pQnDefaultCss}" type="text/css">	
	{/if}
	
	{if isset($pQnGridCss)}
		<link rel="stylesheet" href="{$pQnGridCss}" type="text/css">	
	{/if}
	
	{if isset($pQnJQueryCss)}
		<link rel="stylesheet" href="{$pQnJQueryCss}" type="text/css">
	{/if}

	{if isset($cssModulo)}
		<link rel="stylesheet" href="css/{$cssModulo}.css" type="text/css">
	{/if}	
	
	{$cssIncludes}
	