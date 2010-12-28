{*smarty*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		{include file="common/head.tpl"}
	</head>
	<body>
<div id="todo">
<table id="main" align="center" cellpadding=0 cellspacing=0>
<!--	<tr>
        <td colspan="3" id="superior" >
			{include file="decorators/header.tpl"}
		</td>
	</tr> -->
	{**}
	<tr>
		<td colspan="3" class="style_bienvenido">
			{include file="common/menu.tpl"}
			{if $errores neq ''}
				{foreach item=errorMsg from=$errores}
				<p class="errorMsg">{$errorMsg}</p>
				{/foreach}
			{/if}
		</td>
	</tr>
	{**}
	{if $menuMod neq ''}
	<tr class="centro">
        <td colspan="3" >
			{include file=$menuMod}
		</td>
	</tr>
	{/if}
	<tr class="centro">
		<td colspan="3" height="100%">
		{if $formulario != null}
		<form {$formulario.attributes}>
		{/if}
		<div style="text-align:center">
			<div id="contenido">
				<div id="logo"></div>
				<div id="cabeceraUsuario">
					<h3>Bienvenido {$usuario->getApellido()}, {$usuario->getNombre()}</h3>
					{if $formulario != null}
					<table>
						<tr>
							<th>Apellido</th><td>{$formulario.apellido.html}</td>
							<th>Nombre</th><td>{$formulario.nombre.html}</td>
							<th></th><td></td>
						</tr>
						<tr>
							<th>{$formulario.dni.label}</th><td>{$formulario.dni.html}</td>
							<th>{$formulario.cargo.label}</th><td>{$formulario.cargo.html}</td>
							<th>{$formulario.periodo.label}</th><td>{$formulario.periodo.html}</td>
						</tr>
						<tr>
							<th>{$formulario.legajo.label}</th><td>{$formulario.legajo.html}</td>
							<th>{$formulario.departamento.label}</th><td>{$formulario.departamento.html}</td>
							<th>{$formulario.director.label}</th><td>{$formulario.director.html}</td>
						</tr>
					</table>
					{/if}
				</div>
				{include file=$pantalla}
			</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan="3" id="inferior">
			{include file="common/footer.tpl"} 
		</td>
	</tr>
</table>
</div>
	</body>
</html>		