{*smarty*}

<body id="body-login">
	<header>
		<!-- BEGIN LOGO -->
		<a id="logo" href="./">
			<img src="sistemafce/img/logo.png" alt="{$appName}">
		</a>
		<!-- END LOGO -->
	</header>
	<section id="container">
		<div id="form-login" class="lista">
			<header><h6>Acceso</h6></header>
			<div style="clear:both"></div>
			<form class="base-form" action="{$smarty.server.PHP_SELF}" method="POST">
				
			<div>
				<div class="form_element input-prepend">
					<span class="add-on"><i class="icon-user"></i></span>
					<input type="text" name="username" />
				</div>
				<div class="form_element input-prepend">
					<span class="add-on"><i class="icon-lock"></i></span>
					<input name="password" type="password"/>
				</div>
			</div>
			<div style="clear:both"></div>
			<div class="botonera">
				<input name="submit" value="Login" type="submit" />
			</div>
				
			</form>
		</div>
	</section>
</body>