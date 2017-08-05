{*smarty*}

<body id="body-login">
	<header>
		{if isset($logoSrc)}
		<!-- BEGIN LOGO -->
		<a id="logo" href="./">
			<img src="{$logoSrc}" alt="{$appName}">
		</a>
		<!-- END LOGO -->
		{/if}
	</header>
	<section id="container">
		{if isset($errorLogin)}
			<div id="error-login" class="alert">
			  <strong>Error!</strong> Su usuario o su clave de acceso son incorrectos.
			</div>
		{/if}
		<div id="form-login" class="lista">
			<header><h6>Acceso</h6></header>
			<div style="clear:both"></div>
			<form class="base-form" action="{$action}" method="POST">
				
			<div>
				<div class="form_element input-prepend">
					<span class="add-on"><i class="icon-user"></i></span>
					<input type="text" name="username" value="{$smarty.post.username}" />
				</div>
				<div class="form_element input-prepend">
					<span class="add-on"><i class="icon-lock"></i></span>
					<input name="password" type="password"/>
				</div>
			</div>
			<input name="mod" type="hidden" value="{$smarty.request.mod}"/>
			<div style="clear:both"></div>
			<div class="botonera">
				<input name="submit" value="Login" type="submit" />
			</div>
				
			</form>
		</div>
	</section>
</body>