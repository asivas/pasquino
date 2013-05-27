{*smarty*}
<!DOCTYPE html>

<html>
	
	<head>
		{include file="$pQnHeadTpl"}
		{include file="$pQnHeadAdminTpl"}
	</head>
	
	<body>
	
		<!-- HEADER -->
		<header>
			{include file="$pQnHeaderTpl"}
		</header>
		<!-- fin HEADER -->
		
		<div class="clr"></div>
	
		<!-- CONTAINER -->
		<div id="container" class="container-fluid">
		
			<div class="row-fluid">
	
				<!-- SIDEBAR (.span3) -->
				<nav id="sidebar" class="span3">
					{include file="$pQnMenuTpl"}
				</nav>
				<!-- fin SIDEBAR -->
				
				<!-- MAIN SECTION (.span9) -->
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
				<!-- fin MAIN SECTION -->
				
			</div>
			
		</div>
		<!-- fin CONTAINER -->
		
		<!-- FOOTER -->
		<footer>
			{include file="$pQnFooterTpl"}
		</footer>
		<!-- fin FOOTER -->

	</body>
	
</html>


		