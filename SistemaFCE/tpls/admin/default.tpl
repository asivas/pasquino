{*smarty*}
<!DOCTYPE html>

<html>
	
	<head>
		{include file="$pQnHeadTpl"}
		{include file="$pQnHeadAdminTpl"}
	</head>
	
	<body>
	
		<header>
			{include file="$pQnHeaderTpl"}
		</header>
	
		<div class="clr"></div>
	
		<div id="container" class="container-fluid">
	
			<div class="row-fluid">
	
				<div class="span3">
					<nav id="sidebar">
						{include file="$pQnMenuTpl"}
					</nav>
				</div>			
	
				<section id="main-section" class="span9" >
					
					
						<div class="page-header">
							{include file=$pQnPageHeader}
						</div>
						
						<div class="page">
							<div class="row-fluid">
								{include file=$pantalla}
							</div>
						</div>
					
				</section>
		
	
			</div>
	
			<footer>
				{include file="$pQnFooterTpl"}
			</footer>
	
		</div>
		<!--/.fluid-container-->

	</body>
	
</html>


		