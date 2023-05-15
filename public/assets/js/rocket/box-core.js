// users Core - Funciones principales JQuery para usuarios
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
    bootbox.setDefaults("locale",locale) //traslate bootbox
//datepicker plugin
$.fn.datepicker.dates['es'] = {
	days: ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado", "Sunday"],
	daysShort: ["Dom", "Lun", "Mar", "Mier", "Jue", "Vier", "Sab", "Dom"],
	daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
	months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Augosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
	monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"]
};

var fecha = $('#date_reg').datepicker({
	language: 'es',
	autoclose: true,
	todayHighlight: true,
	dateFormat: "dd-mm-yy"
});

//start stats counters

function contadores(){

	$.ajax({
		"url": "box/totalcounters",
		"type": "GET",
		"cache":false,
		"data": {},
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){
		if(data.success){
			$('#ing').text(data.total_in+data.simbol);
			$('#egr').text(data.total_out+data.simbol);
			$('#sal').text(data.total+data.simbol);
			if(data.total > 0){
				$('#status_sal').removeClass('infobox-red');
				$('#status_sal').addClass('infobox-blue');
			}
			else{
				$('#status_sal').removeClass('infobox-blue');
				$('#status_sal').addClass('infobox-red');

			}
		}
		else{
			$('#ing').text(0);
			$('#egr').text(0);
			$('#sal').text(0);

		}
	});

}


function getrouters(){

	$.ajax({
		"url":"client/getclient/routers",
		"type":"POST",
		"data":{},
		"dataType":"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){
		if(data.msg=='norouters'){
			$('#add').modal('toggle');
			msg(Lang.messages.theyWereNotFound+' <b>'+Lang.app.routers+'</b>, '+Lang.messages.youMustAddAtLeastOneRouter,'system');
		}
		else{
            $('#slcrouter').html('');
			$('#slcrouter').append($('<option>').text(Lang.app.selectRouter).attr('value', '').prop('selected', true));
			$.each(data, function(i, val) {
				$('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
			});

		}

	});
}

contadores();

// end stat counters

//ocultamos razón social
$('#so,#crouter').hide();



//funcion para recargar el selec de clientes
function all_clients()
{
	//rescatamos todos los clientes
	$.ajax({

		url:"client/getclient/clients",
		type:"POST",
		data:{},
		dataType:"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){
		$('#clients').append($('<option>').text('').attr('value', '').prop('selected', true));
		$.each(data, function(i, val) {
			$('#clients').append($('<option>').text(val['name']).attr('value', val.id));
		});


		$('.chosen-select').chosen({allow_single_deselect:true});
					//resize the chosen on window resize

					$(window)
					.off('resize.chosen')
					.on('resize.chosen', function() {
						$('.chosen-select').each(function() {
							var $this = $(this);
							$this.next().css({'width': $this.parent().width()});
						})
					}).trigger('resize.chosen');
					//resize chosen on sidebar collapse/expand
					$(document).on('settings.ace.chosen', function(e, event_name, event_val) {
						if(event_name != 'sidebar_collapsed') return;
						$('.chosen-select').each(function() {
							var $this = $(this);
							$this.next().css({'width': $this.parent().width()});
						})
					});


					$('#chosen-multiple-style .btn').on('click', function(e){
						var target = $(this).find('input[type=radio]');
						var which = parseInt(target.val());
						if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
						else $('#form-field-select-4').removeClass('tag-input-style');
					});

				});

}

// boton nuevo
$(document).on("click","#nuevo_mod",function(){
    $('#clt').hide();

    $('#clients').empty();
	all_clients();
    $('.chosen-select', this).chosen('destroy').chosen();
    $('#so,#crouter').show('fast');
    getrouters();
    $('#soi').focus();
});
$(document).on("click",".newr",function(){
	$('#numrec').hide();
	if(!$('#copy').is(':checked')){
		//reseteamos el formulario
		$('#formaddreg')[0].reset();
	}
	$('#clients').empty();

	all_clients();
});

// recargar tabla
$(document).on("click",".recargar", function (event) {
	 window.LaravelDataTables["box-table"].draw();
	contadores();
});

$('#add').on('shown.bs.modal', function () {
	$('.chosen-select', this).chosen('destroy').chosen();
	$('#numr').focus();
});


$('#edit').on('shown.bs.modal', function () {
	$('.chosen-select', this).chosen('destroy').chosen();
	$('#edit_numr').focus();
});

//ocultar cliente
$(document).on("change","#type",function(event){

	var op = $('#type').val();
	if(op=='out'){
		$('#clt').hide();

		$('#clients').empty();
		$('.chosen-select', this).chosen('destroy').chosen();
		$('#so,#crouter').show('fast');
		getrouters();
		$('#soi').focus();
	}
	else{
		$('#clients').empty();
		$('#slcrouter').empty();
		$('.chosen-select', this).chosen('destroy').chosen();
		all_clients();
		$('#clt').show();
		$('#so,#crouter').hide('fast').val('');
		$('#numr').focus();
	}

});

//agregar registro
$(document).on('click','#addbtreg',function(){

	var data = $('#formaddreg').serialize();
	var $btn = $(this).button('loading');

	$.ajax({
		"type":"POST",
		"url":"box/create",
		"data":data,
		"dataType":"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){

		if(data.msg=='success'){
			$('#add').modal('toggle');
			 window.LaravelDataTables["box-table"].draw();
			 window.LaravelDataTables["box-table"].draw();
			contadores();
			msg(Lang.messages.recordAddedCorrectly, 'success');
		}
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
		$btn.button('reset');
	});
});


//eliminar registro
$(document).on("click", '.del', function (event) {
	var idr = $(this).attr ("id");
	bootbox.confirm(Lang.messages.areYouSureToPermanentlyDeleteTheUser, function(result) {
		if(result) {
			$.ajax ({
				type: "POST",
				url: "box/delete",
				data: { "id" : idr },
				dataType: "json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){

				if(data.msg=='notfound')
					msg(Lang.messages.theRecordWasNotFoundInTheBD, 'error');
				if(data.msg=='success'){
					msg(Lang.messages.theRecordWasDeleted, 'success');
					 window.LaravelDataTables["box-table"].draw();
					contadores();
				}
			});
		}
	});
});


// other plugins

//fin del ready
});
