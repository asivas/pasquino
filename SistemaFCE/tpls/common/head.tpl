{*smarty*}
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">	
	<title>{$appName} - {$titulo}</title>	
	
	{* archivos css base *}
	<link rel="stylesheet" href="css/main.css" type="text/css">
		
	{* css de template *}
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="skins/{$skin}/css/ie.css">
	<![endif]-->
	<link rel="stylesheet" href="templates/{$skin}/css/style.css" type="text/css">
	{* css de template *}
	
	{include file="common/head.jquery.tpl"}	
	
	<script type="text/javascript" src="/js/sistemafce/admin.js"></script>
	
	{$jsIncludes}
	{$cssIncludes}