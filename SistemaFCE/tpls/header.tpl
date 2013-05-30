{*smarty*}
<div class="container-fluid">
	<!-- BEGIN LOGO -->
	<a id="logo" href="./">
		<img src="{$logoSrc}" alt="{$appName}">
	</a>
	<!-- END LOGO -->
{if isset($usuario)} 
	<div id="user-tools" class="dropdown pull-right">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">
			{$usuario->getNombre()} {$usuario->getApellido()}			
			<i class="icon-chevron-down icon-white"></i>
		</a>
	  	<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
	    	<li><a href="#"><i class="icon-lock"></i> Cambiar Contraseña</a></li>
	    	<li><a href="#"><i class=" icon-edit"></i> Preferncias</a></li>
	    	<li  class="divider"></li>
	    	<li><a href="?logout"><i class="icon-off"></i> Cerrar Sesion</a></li>
	    </ul>
	</div>
{/if}
</div>						

