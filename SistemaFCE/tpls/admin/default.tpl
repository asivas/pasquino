{*smarty*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		{include file="$pQnHead"}
		{include file="$pQnHeadAdmin"}
	</head>
	<body>
		<header>
			{include file="$pQnHeader"}
		</header>
		<nav>
			{include file="$pQnMenu"}
		</nav>
		<section>
			{include file=$pantalla}
		</section>
		<footer>
			{include file="$pQnFooter"} 
		</footer>
	</body>	
</html>