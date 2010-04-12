var funcionesInputs = new Array();
function llamarFuncionAjax(nombreFuncion)
{
	if(funcionesInputs[nombreFuncion]!= null)
	{ 
		var inputQueCambia = funcionesInputs[nombreFuncion]['input'];
		if(funcionesInputs[nombreFuncion]['valor']==inputQueCambia.value && funcionesInputs[nombreFuncion]['valorAnterior']!=inputQueCambia.value)
		{
			eval(funcionesInputs[nombreFuncion]['funcion']);
			funcionesInputs[nombreFuncion]['valorAnterior'] = inputQueCambia.value;
		}
	}
}

function funcionAjaxCondicional(funcion,inputQueCambia,msEspera)
{
	var nombreFuncion = funcion.substring(0,funcion.indexOf('('));	
	if(funcionesInputs[nombreFuncion]==null)
		funcionesInputs[nombreFuncion] = new Array();
	funcionesInputs[nombreFuncion]['funcion'] = funcion;
	funcionesInputs[nombreFuncion]['input'] = document.getElementById(inputQueCambia);
	funcionesInputs[nombreFuncion]['valor'] = document.getElementById(inputQueCambia).value;
	
	if(msEspera == null) msEspera = 500;
	
	setTimeout("llamarFuncionAjax('"+nombreFuncion+"')",msEspera);
	
}