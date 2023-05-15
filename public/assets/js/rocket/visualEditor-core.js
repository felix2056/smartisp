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

//cargamos todos los templates
function loadtem(){
$.ajax({
		"url":baseUrl+"/templates/listvisual",
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

				 $('#type_temp').append($('<option>').text(val['name']).attr('value', val.name));


		 });

});
}
//fin de cargar
	if(flag) {
        loadtem();
        flag = false;
	}

//funcion uploader

$(document).on("click","#uploadImg",function(event) {
    $('#addimg').modal('toggle');
});

$("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "../assets/imgeditor/upload.php",

        // Maximum file size
        max_file_size : '2mb',

        chunk_size: '1mb',

        // Resize images on clientside if we can
        resize : {
            width : 800,
            height : 600,
            quality : 90,
            crop: true // crop to exact dimensions
        },

        // Specify what files to browse for
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"},
            {title : "Zip files", extensions : "zip,avi"}
        ],

        // Rename files by clicking on their titles
        rename: true,

        // Sort files
        sortable: true,

        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,

        // Views to activate
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },

        // Flash settings
        flash_swf_url : '/Moxie.swf',

        // Silverlight settings
        silverlight_xap_url : '/Moxie.xap'
    });

//fin del uploader

//guardar template

$(document).on('click','#addtemplate',function(event){
	event.stopImmediatePropagation();
	var data = tinyMCE.activeEditor.getContent({format : 'html'});
	var tem = $('#type_temp').val();
	var name = $('#nameTeme').val();
	var tp = 'screen';

		$.ajax({
			"type":"POST",
			"url":"templates/create",
			"data":{"data": data,"tem": tem, "name": name, "tp":tp},
			"dataType":"json",
			'error': function (xhr, ajaxOptions, thrownError) {
						debug(xhr,thrownError);
			 }
		}).done(function(data){

			if(data.msg=='success'){
				if(tem=='new'){
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
			//Mesajes personalizados
			if(data.msg=='error')
				msg(Lang.messages.iDonotPutTheNameToTheTemplate, 'error');
			if(data.msg=='none')
				msg(Lang.messages.iDoNotSelectTheTemplate, 'error');
			if(data.msg=='updated'){
				msg(Lang.messages.theTemplateWasUpdated, 'info');
			}
			//fin de mensajes personalizados

		}); //end ajax

});

//fin de guardar template


//Mostrar campo nombre si se selecciona nueva plantilla
$(document).on("change","#type_temp",function(event){
	event.preventDefault();
	event.stopImmediatePropagation();
	var op = $('#type_temp').val();
	if(op=='new'){
		tinyMCE.activeEditor.setContent('');
		$('#Stem').show('fast');
		$('#nameTeme').focus();
	}
	else if(op=='none'){
		$('#Stem').hide('fast');
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
				console.log(data, "Hello from data");
				tinymce.activeEditor.setContent(data);

			});

		});

		//fin de recuperar
	}

});

//fin del ready
});
