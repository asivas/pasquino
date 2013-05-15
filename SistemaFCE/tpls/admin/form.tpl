{*smarty*}
	<form class="base-form" {$formulario.attributes} >
		<div class="elementos">
			{foreach from=$formulario key=nc item=campo}
			{if $nc != 'frozen' && $nc != 'cancelar' && $nc != 'guardar' && $nc != 'frozen'
			 && $nc != 'javascript' && $nc != 'attributes' && $nc != 'requirednote'
			 && $nc != 'errors' && $nc != 'hidden'}
			
			<div class='form_element {$nc}'>
				<label for='{$nc}'>{$campo.label}</label>
				{$campo.html}
			</div>
			{/if}
			{/foreach}
		</div>
		<div class="clr"></div>
		<div class='botonera'>
			{$formulario.guardar.html}
			{$formulario.cancelar.html}
		</div>		
		{$formulario.hidden}
	</form>