// users Core - Funciones principales JQuery para usuarios
var flag = true;
jQuery(function($) {
///// General Messages for system ///////
//Mesages for confirmatios success
function msg(msg,type)
{
	if(type=='success'){
		var clase = 'gritter-success';
		var tit = Lang.app.registered;
		var img = 'assets/img/ok.png';
		var stincky = false;

	}
	if(type=='error'){
		var clase = 'gritter-error';
		var tit = Lang.app.error;
		var img = 'assets/img/error.png';
		var stincky = false;

	}
	if(type=='debug'){
		var clase = 'gritter-error gritter-center';
		var tit = Lang.app.errorInternoDebugMode;
		var img = '';
		var stincky = false;

	}
	if(type=='info'){
		var clase = 'gritter-info';
		var tit = Lang.app.information;
		var img = 'assets/img/info.png';
		var stincky = false;

	}

	$.gritter.add({
	// (string | mandatory) the heading of the notification
	title: tit,
	// (string | mandatory) the text inside the notification
	text: msg,
	image: img, //in Ace demo dist will be replaced by correct assets path
	sticky: stincky,
	class_name: clase
	});
}
////// end messages for this page //////

//code mirror editor





//end code mirror
///// funcion de depuracion
function debug(xhr,thrownError){
	$.ajax({
		"url":"config/getconfig/debug",
		"type":"GET",
		"data":{},
		"dataType":"json"
	}).done(function(deb){

		if(deb.debug=='1'){
			msg(Lang.app.error +' ' +xhr.status +' '+  thrownError +' '+ xhr.responseText,'debug');
		}

		else
			alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
	});
}
//// fin de la funcion de depuracion


$('#Stem').hide();
$('#typeTemplate').hide();
//cargamos todos los templates

function loadtem(){
$.ajax({
		"url":"templates/list",
		"type":"POST",
		"data":{},
		"dataType":"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){
		$('#type_temp').empty();

		 $('#type_temp').append($('<option>').text(Lang.app.selectTemplate).attr('value', 'none').prop('selected', true));
		 $('#type_temp').append($('<option>').text(Lang.app.newTemplate+' *').attr('value', 'new'));
		 $.each(data, function(i, val) {
			if(val['type']=='screen'){
				 $('#type_temp').append($('<option>').text(val['name']).attr('value', val.name));
		     }
			 else{
				 $('#type_temp').append($('<option>').text(val['name']).attr('value', val.name));
			 }
		 });

});
}
//fin de cargar
	if(flag) {
        loadtem();
        flag = false;
	}


//guardar template

$(document).on('click','#addtemplate',function(e){
    e.stopImmediatePropagation();
	var $btn = $(this).button('loading');
	var data = editor.getValue();
	var tem = $('#type_temp').val();
	var name = $('#nameTeme').val();
	var tp = $('#tp').val();

	$.ajax({
		"type":"POST",
		"url":"templates/html",
		"data":{"data": data,"tem": tem, "name": name, "tp":tp},
		"dataType":"json",
		'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
		 }
	}).done(function(data){

		if(data.msg=='success'){
			if(tem=='new'){
					$.ajax({
					"url":"templates/listhtml",
					"type":"POST",
					"data":{},
					"dataType":"json",
					'error': function (xhr, ajaxOptions, thrownError) {
						debug(xhr,thrownError);
					}
					}).done(function(data){

						 $('#type_temp').empty();
						 $('#type_temp').append($('<option>').text(Lang.app.selectTemplate).attr('value', 'none'));
						 $('#type_temp').append($('<option>').text(Lang.app.newTemplate+' *').attr('value', 'new'));
						 $ro = $.each(data, function(i, val) {
							$('#type_temp').append($('<option>').text(val['name']).attr('value', val.name));
						 });
						$.when($ro).done(function() {
							var myText = name;
							$("#type_temp").children().filter(function() {
							return $(this).val() == myText;
							}).prop('selected', true);

							$('#nameTeme').val('');
							$('#Stem').hide();
							msg(Lang.messages.theTemplateWasSaved, 'success');
						});
				  });
			}



		}
		//máximo numero de caracteres
		if (data.msg=='maxcharacters')
			msg(Lang.messages.characterLimitExceededIsAllowedAMaximumOf160ForSMS, 'error');
		//Mesajes personalizados
		if(data.msg=='error')
			msg(Lang.messages.iDonotPutTheNameToTheTemplate, 'error');
		if(data.msg=='none')
			msg(Lang.messages.iDoNotSelectTheTemplate, 'error');
		if(data.msg=='updated'){
			msg(Lang.messages.theTemplateWasUpdated, 'info');
		}
		//fin de mensajes personalizados
		$btn.button('reset');
	});
});


//fin de guardar template


//Mostrar campo nombre si se selecciona nueva plantilla
$(document).on("change","#type_temp",function(event){
    event.stopImmediatePropagation();
    // event.preventDefault();
	var op = $('#type_temp').val();
	if(op=='new'){

		 editor.setValue('');

		$('#Stem').show('fast');
		$('#typeTemplate').show('fast');
		$('#nameTeme').focus();
	}
	else if(op=='none'){
		$('#Stem').hide('fast');
		$('#typeTemplate').hide('fast');
	}
	else{
		$('#Stem').hide('fast');

		//recupermos el template elegido
		$.ajax({
		type: "POST",
		url: "templates/seteme",
		data: { "name" : op },
		dataType: "html",
			'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
    	}
		}).done(function(data){


			$.ajax({
			 type: "POST",
			 url:"templates/setype",
			 data:{"name":op},
			 dataType: "json"
			}).done(function(datos){
					//buscamos el tipo de template seleccionado
					var myText = datos.type;
					$("#tp").children().filter(function(){
					return $(this).val() == myText;
					}).prop('selected', true);
					$('#typeTemplate').show('fast');
					editor.setValue(data);


			});

		});

		//fin de recuperar
	}

});


//fin del ready
});
