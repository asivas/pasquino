$(document).ready(function(){
    if(typeof  $.fn.button.noConflict ==='function')
		$.fn.button.noConflict();
	
	$('input[name=submit]').button();
	if($("#error-login").length>0)
		$('input[name=password]').focus();
	else
		$('input[name=username]').focus();
})