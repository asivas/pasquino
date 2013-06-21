$(document).ready(function(){
	$.fn.button.noConflict();
	
	$('input[name=submit]').button();
	$('input[name=username]').focus();
})