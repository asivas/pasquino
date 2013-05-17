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
		
		<div id="container" class="row-fluid">
			
			<nav>
				{include file="$pQnMenuTpl"}
			</nav>
			
			<section>
					
					<div class="page-header">
						{include file=$pQnPageHeader}
					</div>
					
					<div class="page">
						{include file=$pantalla}
					</div>
				
			</section>

		</div>
		<footer>
			{include file="$pQnFooterTpl"} 
		</footer>
		
	</body>	
</html>