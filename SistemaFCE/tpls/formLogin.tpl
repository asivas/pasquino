{*smarty*}
<form action="{$smarty.server.PHP_SELF}" method="POST">
	<table>
		<tr>
			<td>Nombre ususario:</td><td> <input name="username"></td>
		</tr>
		<tr>
			<td>Contraseña:</td> <td><input name="password" type="password"></td>
		</tr>
		<tr>
			<td colspan="2"><input value="ingresar" type="submit"></td>
		</tr>
		
	</table>
</form>