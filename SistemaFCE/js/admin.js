
/**
 * Genera una query string para llamar a una accion de un modulo
 * @param modulo nombre del modulo
 * @param accion nombre de la acción
 * @param mostrar
 * @returns {String}
 */
function getAccionUrl(modulo,accion,mostrar) {
	if(mostrar==null) mostrar='full';
	var qs = "?";
	if(modulo!=null)
		qs+="mod="+modulo;
	if(qs!="?")
		qs+="&";
	
	qs += "accion="+accion;
	
	qs+="&";
	qs += "display="+mostrar;
	
	return qs;
}

function getUrlBaja(mod,id){
	return getAccionUrl(mod,'baja','plain')+'&id='+id;
}

function getUrlModif(mod,id){
	return getAccionUrl(mod,'modif','plain')+'&id='+id;
}

function getUrlAlta(mod){
	return getAccionUrl(mod,'alta','plain');
}

/**
 * efectúa los binds de los botones de alta, modificación y filtro
 */
function setupButtons(selectorBotonera,selectorBotones){
	
	if(selectorBotones==null)
		selectorBotones='.acciones';
	if(selectorBotonera==null)
		selectorBotonera='li.acciones';
	
	$(selectorBotonera).find('div').addClass('ui-state-default ui-corner-all').css('float','left').css('margin-left','2px');
	
	$(selectorBotones).delegate('a>.ui-icon-pencil','click',function(e){
		e.preventDefault();
		if(typeof $fnBindModifBtn == 'function') {
			$fnBindModifBtn($(this).parent("a").attr("href"));			
		}		
	});
	if(typeof $fnBindAltaBtn == 'function')	$fnBindAltaBtn();
	if(typeof $fnBindFiltro == 'function')	$fnBindFiltro();
	
}

var htmlCargando = "cargando...";
jQuery.fn.exists = function(){return this.length>0;};
jQuery.fn.crearDiv = function(idDiv){
	if(!$("#"+idDiv).exists())
		return this.append("<div id='"+idDiv+"'></div>");
	return $("#"+idDiv);
};


var ultimoKeyup=null;
var filtroAnterior='';

/**
 * Metodo para realiar el filtro automatico, enviando el submit del form
 * @param tiempo
 * @param aInputName
 * @param aFormID Id del form
 */
jQuery['doFilter']=function(tiempo,aInputName){	
	if(tiempo==ultimoKeyup){
		var aInput=$("input[name="+aInputName+"]");
		var aInputVal=aInput.val();
		if(filtroAnterior!=aInputVal){	
			filtroAnterior=aInputVal;
			aInput.parent("form").submit();
		}
	}
};

/**
 * Metodo agregado a los objetos jQuery para realizar bind de input de filtro, 
 * con una espera para realizar filtro automatico 
 * @param aSourceID Objeto desde el cual se carga TargetID
 * @param aMod Modulo a ejecutar
 * @param aAction Metodo a Ejecutar
 * @param aOptions objeto con opciones (success).
 */
jQuery.fn.keyUpFilter = function(aSourceID,aMod,aAction,aOptions){
	var form = $(this).parent("form");
	form.unbind('submit');
	form.submit(function(e) {		
		$("#"+aSourceID).parent().load(getAccionUrl(aMod,aAction,"plain")+ "&" +$(this).serialize() + " #"+aSourceID,
				function(response, status, xhr) {
			  		if (status == "error") {
			  			alert("Ocurrio un Error: " + xhr.status + " " + xhr.statusText);
			  		}else{
			  			if(aOptions!=null &&
			  			   aOptions.success!=null && 
			  			   (typeof aOptions.success == 'function')) 
			  				aOptions.success();
			  		}
		});
		e.preventDefault();
	});
	$(this).unbind('keyup');
	$(this).keyup(function(event){
		setTimeout("$.doFilter("+event.timeStamp+",'"+ $(this).attr('name') +"')",800);
		ultimoKeyup = event.timeStamp;
	});
};


/**
 * Metodo para eliminar una entidad usando ajax procesando la respuesta <status>
 * @param idMetodoPago
 * @returns {Boolean}
 */
function eliminar(id,mod){
	var ret=false;
	$.ajax({
		url: getAccionUrl(mod,'baja','plain')+"&id="+id,
		type: 'POST',
		data: '' , 
		async:false,
		success: function(data,  textStatus, jqXHR) {
			$("body").crearDiv('tmp');
			$("#tmp").hide().html(data);
			if($("#tmp status").attr('status')!='OK'){
				alert($("#tmp status").attr('msg'));
				ret=false;
			}else{
				alert($("#tmp status").attr('msg'));
				if($('#idFiltro').exists())
					$('#idFiltro').submit();
				ret=true;
			}
		}
	});
	return ret;
}

jQuery['dialogoGuardar'] = function(idDialogo,url,titulo,idForm,opciones){
	var nombreBotonGuardar,valueBotonGuardar,anchoDialogo,callback,nombreBotonCancelar;
	if(opciones!=null) {
		nombreBotonGuardar = opciones.nombreBotonGuardar;
		valueBotonGuardar = opciones.valueBotonGuardar;
		anchoDialogo = opciones.anchoDialogo;
		nombreBotonCancelar = opciones.nombreBotonCancelar;
	}
	else
		opciones={};

	if(nombreBotonGuardar==null)	nombreBotonGuardar = 'guardar';
	if(valueBotonGuardar==null)		valueBotonGuardar = 'Guardar';
	if(anchoDialogo==null)			anchoDialogo = 'auto';
	if(nombreBotonCancelar==null)	nombreBotonCancelar = 'cancelar';
		
	if(opciones.modal==null) opciones.modal=true;
	if(opciones.top==null) opciones.top=69;
	if(opciones.width==null) opciones.width=anchoDialogo;
	if(opciones.autoOpen==null) opciones.autoOpen=false;
	
	var dlgOpts = opciones;
	dlgOpts.title = titulo;
	
	$("body").crearDiv(idDialogo);
	var dlg = $("#"+idDialogo);
	dlg.html(htmlCargando).load(url,function(){
		if( opciones.onLoad )
			opciones.onLoad();
		var btn = $("form#"+idForm+" input[name='"+nombreBotonGuardar+"']");
		var $procesarForm = function(e){
			$.post('./',$("form#"+idForm).serialize(),
					function(data) {
					$("body").crearDiv("tmp");
					
					$("#tmp").hide().html("").html(data);
					var status = $("#tmp status").attr('status'); 
					dlg.attr('status',status);
					if(status=='OK')
					{	
						if( opciones.success )
							opciones.success();
						dlg.dialog('close').remove();
					}
					else if(status=='ERR')
						alert($("#tmp status").attr('msg'));
				}
			);
			e.preventDefault();
		};
		
		if(btn.exists())
			btn.button($procesarForm).click($procesarForm).attr('value',valueBotonGuardar);
		else
			$("form#"+idForm).submit($procesarForm);
		
		var btnCancelar = $("form#"+idForm+" input[name='"+nombreBotonCancelar+"']");
		if(btnCancelar.exists())
			btnCancelar.button().click(function(e){dlg.html("").dialog('close');});
	dlg.dialog('open');	
		
	}).dialog(dlgOpts);
};

function initModulo(idDialogo,nombreEntidadPrincipal,idForm,nombreCampoFiltro,idBotonAlta,idLista,aMod) {
	var $filtroFormSubmit = function(){$("input[name='"+nombreCampoFiltro+"']").parent("form").submit();};
	$fnBindAltaBtn = function(){ 
		$("#"+idBotonAlta).botonAlta(idDialogo,"Nuevo "+nombreEntidadPrincipal ,idForm,{success:$filtroFormSubmit});
	};
	$fnBindModifBtn = function(href){ 
		$.dialogoGuardar(idDialogo,href+"&display=plain","Modificar "+nombreEntidadPrincipal,idForm,{success:$filtroFormSubmit});
	};
	$fnBindFiltro = function() {
		$("input[name='"+nombreCampoFiltro+"']").keyUpFilter(idLista,aMod,"listar",{success:function(){setupButtons();}});
	};
	setupButtons();
}

/**
 * Crea una funcion de jQuery que le asigna la funcionalidad de boton de alta al elemento que la llama
 * suponiendo que es un contenedor html (div,td,span...) que tiene un a href con la URL para el alta
 */
jQuery.fn.botonAlta = function(idDialogo,tituloDialogo,idFormAlta,opciones) {
	var linkAlta = $(this).addClass("ui-state-default ui-corner-all").css('float','right').css('display','block').find('a');
	if(!linkAlta.find("span.ui-icon-plus").exists())
		linkAlta.append('<span class="ui-icon ui-icon-plus"></span>');
	$(this).delegate("a","click",function(e){
		e.preventDefault();
		$.dialogoGuardar(idDialogo,$(this).attr('href')+"&display=plain",tituloDialogo,idFormAlta,opciones);
	});
};
