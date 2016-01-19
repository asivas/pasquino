{*smarty*}
		<title>{$appName}{if isset($titulo)} - {$titulo}{/if}</title>
	
	{if isset($pQnFavicon)}
	<link rel="shortcut icon" href="{$pQnFavicon}"/>
	{/if}
	<!-- JS -->
	{*	Todos los js por defecto (pQn) están incluidos dentro de $jssIncludes en BaseMod::assignHeadJs
		$pQnJQueryJs
		$pQnBrowserUpdateJs
		$pQnJQueryUiJs
		$pQnBootstrapJs
		js/{$jsModulo}.js
	 *}
	{$jsIncludes}
	
	<!-- CSS -->
	{*	Todos los css por defecto (pQn) están incluidos dentro de $cssIncludes en BaseMod::assignHeadCss 
		$pQnBootstrapCss
		$pQnDefaultCss
		$pQnThemeCss
		$pQnGridCss
		$pQnJQueryCss
		$cssModulo
	*}
	{$cssIncludes}
	