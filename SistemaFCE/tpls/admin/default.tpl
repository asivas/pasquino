{*smarty*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		{include file="$pQnHeadTpl"}
		{include file="$pQnHeadAdminTpl"}
	</head>
	<body>
		<header>
			{include file="$pQnHeaderTpl"}
		</header>
		<nav>
			{include file="$pQnMenuTpl"}
		</nav>
		<section class="container_12">
			{include file=$pantalla}
		</section>
		<footer>
			{include file="$pQnFooterTpl"} 
		</footer>
	</body>	
</html>