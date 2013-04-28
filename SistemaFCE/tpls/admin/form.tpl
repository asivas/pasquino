{*smarty*}
	<form {$formulario.attributes} >
		{foreach from=$formulario key=nc item=campo}
		{if $nc != 'frozen' && $nc != 'cancelar' && $nc != 'guardar' && $nc != 'frozen'
		 && $nc != 'javascript' && $nc != 'attriutes' && $nc != 'requirednote'
		 && $nc != 'errors' && $nc != 'hidden'}
		<div class='form_element'>
			<label for='{$nc}'>{$campo.label}</label>
			{$campo.html}
		</div>
		{/if}
		{/foreach}
		
		<div class='botonera'>
			{$formulario.guardar.html}
			{$formulario.cancelar.html}
		</div>		
		{$formulario.hidden}
	</form>