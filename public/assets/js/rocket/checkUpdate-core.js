// Clients Core - Funciones principales JQuery para tickets cliente
jQuery(function($) {
///// General Messages for system ///////
//Mesages for confirmatios success
function msg(msg,type)
{
	if(type=='success'){
		var clase = 'gritter-success';
		var tit = Lang.app.registered;
		var img = '';
		var stincky = false;
	}
	if(type=='error'){
		var clase = 'gritter-error';
		var tit = Lang.app.error;
		var img = '';
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


$(document).on("click", '#btn_update', function (event) {
	$("#loadinfo2").show();
	$.ajax({
		"url":"verify_updates_ok",
		"type":"POST",
		"data":{},
		"dataType":"json"

	}).done(function(data){

		if(data.status=='200'){
			$('#texto_update').html(Lang.messages.SmartISPwasupdatedsuccessfully);
			$("#snewver").hide();
			$("#oldver").hide();
			$("#btn_update").hide();
		}else{
			$('#texto_update').html(Lang.messages.Errorupdatingpleasetryagainlater);
			$("#snewver").hide();
			$("#oldver").hide();
			$("#btn_update").hide();
		}
		$("#loadinfo2").hide();
	});
});

// check update
$(document).on("click", '#update_p', function (event) {
	$('#texto_update').html(Lang.app.lookingForUpdate+'.....');
	$("#snewver").hide();
	$("#oldver").hide();
	$("#btn_update").hide();
	$("#loadinfo2").show();
	$.ajax({
		"url":"verifyUpdates",
		"type":"POST",
		"data":{},
		"dataType":"json"

	}).done(function(data){

		if(data.status=='200'){
			$("#oldver").show();
			$("#ver_Actual").html(data.version_actual);
			$('#texto_update').html(data.success);
			if(data.newv=='true'){
				$("#snewver").show();
				$("#btn_update").show();
				$("#newver").html(data.version_nueva);
			}
		}else{
			msg('Error update','error');
		}
		$("#loadinfo2").hide();

	});
});

//fin del ready
});
