// Profile Core - Funciones principales JQuery para perfil
jQuery(function($) {
///// General Messages for system ///////
//Mesages for confirmatios success
function msg(msg,type)
{
	if(type=='success'){
		var clase = 'gritter-success';
		var tit = Lang.app.registered;
		var img = '../assets/img/ok.png';
		var stincky = false;

	}
	if(type=='error'){
		var clase = 'gritter-error';
		var tit = Lang.app.error;
		var img = '../assets/img/error.png';
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
		var img = '../assets/img/info.png';
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

			$('#file').ace_file_input({
					no_file:'Seleccione Archivo ...',
					btn_choose:Lang.app.select,
					btn_change:Lang.app.change,
					droppable:false,
					onchange:null,
					thumbnail:false, //| true | large
					whitelist:'gif|png|jpg|jpeg',
					blacklist:'exe|php'
					//onchange:''
					//
				});

		$("#uploadphoto").on('submit',(function(e) {
			e.preventDefault();

			$.ajax({
				"url":"myprofile/update",
				"type":"POST",
				"contentType":false,
				"cache": false,
				"processData":false,
				"data":new FormData(this),
				"dataType":"json"

			}).done(function(data){
				//Mesajes personalizados
				if(data.msg=='error'){
					var arr = data.errors;
					$.each(arr, function(index, value)
                        {
                            if (value.length != 0)
                            {
                                msg(value,'error');
                            }
                        });
				}
				//fin de mensajes personalizados
				if(data.msg=='success'){
					msg('El perfil fue actualizado.', 'success');
					setInterval(function() {
   						document.location = "myprofile";
					}, 1200); //1 seconds

				}
			});
		}));
});
