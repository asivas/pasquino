var _indice = 100;
var _idContenedor;

function agregarDialogo(nombreDialogo,idContenedor,loaderImgSrc)//id del dialogo
{
	if(idContenedor == null) 
		idContenedor = 'todo';
	if(loaderImgSrc == null)
		loaderImgSrc = '/js/sistemafce/ajax-loader.gif';
	
	contenedor = document.getElementById(idContenedor);
	
	_idContenedor = idContenedor;

	fondo = document.createElement('div');
	fondo.id = 'fondo_'+_indice;
	fondo.className = 'fondo_diags'; //clase del fondo
	fondo.style.zIndex = _indice + 1;
	fondo.onclick = cerrarDialogoDeFondo;
	contenedor.appendChild(fondo);

	dialogo = document.createElement('div');
	dialogo.id = nombreDialogo;
	dialogo.className = nombreDialogo;
	dialogo.style.zIndex = _indice + 2;
	dialogo.innerHTML = "<img style='margin-top:30px;margin-bottom:30px;' src='"+loaderImgSrc+"'>";
	fondo.appendChild(dialogo);
	
	_indice = _indice + 10;
}

function cerrarDialogoDeFondo(e)
{	
	if(e.target.id=='fondo_'+(_indice-10))
	{
		if(confirm('Se cerrará el dialgo actual ¿está seguro?'))
			cerrarDialogo(e);
	}	
}

function cerrarDialogo(e)
{
	_indice = _indice - 10;
	contenedor = document.getElementById(_idContenedor);
	contenedor.removeChild(document.getElementById('fondo_'+_indice));
}