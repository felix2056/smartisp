// Clients Core - Funciones principales JQuery para tickets cliente
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
///// funcion de depuracion
function debug(xhr,thrownError){
	$.ajax({
		"url":"config/getconfig/debug",
		"type":"GET",
		"data":{},
		"dataType":"json"
	}).done(function(deb){

		if(deb.debug=='1'){
			msg('Error ' +xhr.status +' '+  thrownError +' '+ xhr.responseText,'debug');
		}

		else
			alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
	});
}





// recargar tablas
$(document).on("click", '.recargar', function (event) {
     window.LaravelDataTables["invoice-table"].draw();
});

//fin del ready
});
