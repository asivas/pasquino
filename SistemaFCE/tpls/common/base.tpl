{*smarty*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		{include file="common/head.tpl"}
	</head>
	<body>
		<div id="todo">
			<table id="main" align="center" cellpadding=0 cellspacing=0>
				<tr>
					<td colspan="3" class="style_bienvenido">
					<span class="tip">Para ingresar necesita identificarse</span>
					</td>
				</tr>
				<tr class="centro">
					<td width="1000px" height="100%">
					<div style="text-align:center">
						<div id="contenido">
							<div id="logo"></div>
							<div id="cabeceraUsuario">							
							{include file=$pantalla}
							</div>
						</div>
					</div>
						
					</td>
					<td></td>
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