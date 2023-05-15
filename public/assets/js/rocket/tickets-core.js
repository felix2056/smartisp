// Clients Core - Funciones principales JQuery para tickets
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
			msg('Error ' +xhr.status +' '+  thrownError +' '+ xhr.responseText,'debug');
		}

		else
			alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
	});
}
//// fin de la funcion de depuracion
    bootbox.setDefaults("locale",locale) //traslate bootbox
//inicio de tabla planes
/*var styleb = '<div class="hidden-sm hidden-xs action-buttons">';
var stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
var stylee = '<span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a></li></ul></div></div>';*/
/*
var treload = $('#ticket-table').DataTable({
	"oLanguage": {
		"sUrl": Lang.app.datatables
	},
    bAutoWidth: false,
    "processing": true,
    destroy: true,
	 "columnDefs": [ {
	 	"targets": 6,
	 	"render": function ( data, type, full ) {
	 		return styleb+'<a class="blue chok" href="#" id="'+full['id']+'"><i class="ace-icon fa fa-check-square-o bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'+full['id']+'"><i class="ace-icon fa fa-pencil-square-o bigger-130"></i></a><a class="red del" href="#" id="'+full['id']+'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>'+stylem+'<a href="#" class="tooltip-info chok" data-rel="tooltip" id="'+full['id']+'" title="Cerrar ticket"><span class="blue"><i class="ace-icon fa fa-check-square-o bigger-120"></i></span></a></li><li><a href="#" class="tooltip-success editar" data-rel="tooltip" title="Editar" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'+full['id']+'"><span class="green"><i class="ace-icon fa fa-pencil-square-o bigger-120"></i></span></a></li><li><a href="#" class="tooltip-error del" id="'+full['id']+'" data-rel="tooltip" title="Eliminar">'+stylee;
	 	}
	 }
	 ],

	 ajax: {
	 	"url": "tickets/list",
	 	"type": "POST",
	 	"cache":false,
	 	"dataSrc": ""
	 },
	 "createdRow": function ( row, data, index ) {
			//console.log(data['open']);
			if ( data['read_admin']==0 ) {

				$(row).addClass('info');
			}
		},
		columns: [

		{ data: 'id' },
		{ data: 'section' },
		{ data: 'subject'},
		{ "mRender": function ( data, type, full ) {
			if(full['status']=='op'){
				return '<span class="badge badge-success">Abierto</span>';
			}else{
				return '<span class="badge badge-primary">Cerrado</span>';
			}
		}},

		{ data: 'created_at' },
		{ data: 'client' },
		]
	} );*/
// fin de tabla planes

// recargar tablas
$(document).on("click", '.recargar', function (event) {
    event.stopImmediatePropagation();
	window.LaravelDataTables["ticket-table"].draw()
});

$('#lsclient').hide();

//Llenar con clientes segun router seleccionado
$(document).on("click",".newtck",function(event){
    event.stopImmediatePropagation();
		//recupermos el template elegido
		$('#clients').empty();
		$('#ticketform')[0].reset();
		$.ajax({
			type: "POST",
			url: "client/getclient/allclients",
			data: {},
			dataType: "json",
			'error': function (xhr, ajaxOptions, thrownError) {
				debug(xhr,thrownError);
			}
		}).done(function(data){

			if(data.msg=='noclients'){
				msg('No se encontraron clientes en el sistema.','error');
			}
			else{

				$('#clients').append($('<option>').text('').attr('value', '').prop('selected', true));
				$.each(data, function(i, val) {
					$('#clients').append($('<option>').text(val.name).attr('value', val.id));
				});


				$('.chosen-select').chosen({allow_single_deselect:true});
					//resize the chosen on window resize outo responsive

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
				}
			});
		//fin de recuperar
	});

//Cerrar ticket
$(document).on("click", '.chok', function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
	var idt = $(this).attr ("id");
	bootbox.confirm("¿ Esta seguro de cerrar el ticket, no se podran agregar respuestas ?", function(result) {
		if(result) {
			$.ajax ({
				type: "POST",
				url: "tickets/close",
				data: { "id" : idt },
				dataType: "json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){

				if(data.msg=='notfound')
					msg('No se encontro el ticket en la BD.', 'error');
				if(data.msg=='success'){
					msg('El ticket fue cerrado.', 'success');
					window.LaravelDataTables["ticket-table"].draw()
				}
			});
		}
	});
});

//Eliminar ticket
$(document).on("click", '.del', function (event) {
    event.stopImmediatePropagation();
	var idt = $(this).attr ("id");
	bootbox.confirm("¿ Esta seguro de eliminar el ticket: <b>#"+ idt +"</b>, todas las respuestas contenidas se eliminaran ?", function(result) {
		if(result) {
			$.ajax ({
				type: "POST",
				url: "tickets/delete",
				data: { "id" : idt },
				dataType: "json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){

				if(data.msg=='notfound')
					msg('No se encontro el ticket en la BD.', 'error');
				if(data.msg=='success'){
					msg('El ticket fue eliminado.', 'success');
					window.LaravelDataTables["ticket-table"].draw()
				}
			});
		}
	});
});


//desactivar ticket
$("#addbtnticket").click(function(){

	$("addbtnticket").attr("disabled",true);
});

//resetear formulario
$(".newtck").click(function(){

	$("#ticketform")[0].reset();
	$("addbtnticket").attr("disabled",false);
});


//añadir ticket

$("#ticketform").on('submit',(function(e) {
	e.preventDefault();
    e.stopImmediatePropagation();
	$(".loads").show();
	$.ajax({
		"url":"tickets/create",
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
					$('#add').modal('toggle');
					msg('Ticket creado correctamente.', 'success');
					window.LaravelDataTables["ticket-table"].draw()
				}
				$(".loads").hide();
				$("addbtnticket").attr("disabled",false);
			});
}));


//resetear formulario responder ticket
$(document).on('click','.editar',function(e){
	$('#edtbtn').attr("disabled",false);

});

//ocultar icono load
$(".loads").hide();

//guardar respuesta ticket

$("#resticketform").on('submit',(function(event) {
    event.preventDefault();
    event.stopImmediatePropagation();
	$(".loads").show();
	$('#edtbtn').attr("disabled",true);

	$.ajax({
		"url":"tickets/reply",
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
					$('#edit').modal('toggle');
					msg('Respuesta enviada correctamente.', 'success');
					window.LaravelDataTables["ticket-table"].draw()
				}
				$(".loads").hide();
				$('#edtbtn').attr("disabled",false);
			});
}));

$('#accordion2').hide();
//get responder ticket
$(document).on("click", '.editar', function (event) {
    event.stopImmediatePropagation();
	$('#menrep').val('');

	$('[name=ticket]').val($(this).attr ('id'));
	$('#winedit').waiting({ fixed: true});
	var fdata = $('#val').serialize();
	$('#load2').show();


	//verificamos el estado del ticket si esta abierto o cerrado
	$.ajax({
		type: "POST",
		url: "ticket/getticket/status",
		data: fdata,
		dataType: 'json',
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(dt){

		if(dt.st !='resolved'){
			$('#accordion2').show();
		}
		else{
			$('#accordion2').hide();
		}

	});

	$.ajax({
		type: "POST",
		url: "ticket/getticket/show",
		data: fdata,
		dataType: 'json',
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){

		$('#navticket').empty();

		$tk = $.each(data, function(i, val) {
			if(val['file']=='none'){
				var file = '';
			}
			else{
				var file = '<hr><p><a class="btn btn-xs btn-success" href="assets/support_uploads/'+val['file']+'" target="_blanck"><i class="fa fa-cloud-download"></i> Visualizar archivo</a></p>';
			}

			if(val['user']=='administracion' || val['user']=='tecnico'){
				$('#navticket').append($('<span>').html('<div class="panel panel-info"><div class="panel-heading"><i class="fa fa-user"></i> '+ val['user']+' <div class="pull-right">'+val['created_at']+'</div></div><div class="panel-body">'+val['message']+ file +'</div></div>'));
			}else{
				$('#navticket').append($('<span>').html('<div class="panel panel-default"><div class="panel-heading"><i class="fa fa-user"></i> '+ val['user']+' <div class="pull-right">'+val['created_at']+'</div></div><div class="panel-body">'+val['message']+ file +'</div></div>'));
			}

		});

		$.when($tk).done(function() {
			$('#load2').hide();
			$('#winedit').waiting('done');
			// window.LaravelDataTables["ticket-table"].draw()
		});


	});
}); // fin de editar


$('#add,#addEditModal').on('shown.bs.modal', function () {
	$('.chosen-select', this).chosen('destroy').chosen();
	$('select', this).chosen('destroy').chosen();
	$('#subject').focus();
});



//fin de obtener respuestas de tickets
$('#file,#efile').ace_file_input({
	no_file:Lang.app.Selectafileonlyimages,
	btn_choose:Lang.app.select,
	btn_change:Lang.app.change,
	droppable:false,
	onchange:null,
					thumbnail:false, //| true | large
					whitelist:'gif|png|jpg|jpeg|pdf',
					blacklist:'exe|php'
					//onchange:''
					//
				});

//end aditional plugins
//fin del ready
});
