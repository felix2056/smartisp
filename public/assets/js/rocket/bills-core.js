// bill Core - Funciones principales JQuery para pagos
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

///// star loading modal //////////////

function startloading(selector,text){

	$(selector).loadingModal({
	  position: 'auto',
	  text: text,
	  color: '#fff',
	  opacity: '0.7',
	  backgroundColor: 'rgb(0,0,0)',
	  animation: 'spinner'
	});
}

//end loading message ////////////////


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
//ocultamos los campos
$('#swpay_date').hide();
$('#swamount').hide();
$('#swnbill').hide();
$('#swplan').hide();
$('#swrouter').hide();
$('#swtotalpay').hide();
$('#swexpi').hide();
$('#lbsearch').text('Nombre cliente');
//fin de ocultar los campos
$('.peref').click(function(){
$('#addbtnpay').hide();
$('#addbtnpritn').hide();
$('#swexpi').removeClass('has-warning');
$('#swexpi').removeClass('has-success');
$('#swexpi').removeClass('has-error');
//reseteamos el formulario
$('#name').val('');
$('#expiring').val('');
$('#pay_date').val('');
$('#amount').val('');
$('#nbill').val('');
$('#plan').val('');
$('#router').val('');
//ocultamos los campos
$('#swexpi').hide();
$('#swpay_date').hide();
$('#swamount').hide();
$('#swnbill').hide();
$('#swplan').hide();
$('#swrouter').hide();
$('#swtotalpay').hide();
$('#swprint').hide();
});

//cambiar texto de busqueda
$(document).on('change', '#filter', function(event) {
	event.preventDefault();

	switch($('#filter').val()){

		case 'name':

		$('#lbsearch').text('Nombre cliente');

		break;

		case 'dni':

		$('#lbsearch').text('Número CI/DNI');

		break;

		case 'phone':

		$('#lbsearch').text('Número teléfono');

		break;

		case 'ip':

		$('#lbsearch').text('Dirección IP');

		break;

		case 'mac':

		$('#lbsearch').text('Dirección MAC');

		break;

		case 'email':

		$('#lbsearch').text('Email cliente');

		break;

	}

	$('#name').focus();
});


$('#numpays').ace_spinner({value:1,min:1,max:12,step:1, btn_up_class:'btn-info' , btn_down_class:'btn-info'});
//autocomplete
	$('#name').typeahead({
		onSelect: function(item) {
        	//console.log(item);
			var id = item.value;
			$('#client_id').val(id);
			$('#swexpi').removeClass('has-warning');
			$('#swexpi').removeClass('has-success');
			$('#swexpi').removeClass('has-error');
			$.ajax({
			 "url":"client/getclient/gcl",
			 "type":"POST",
			 "data":{"id":id},
			 "dataType":"json",
			 'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
			 }
			}).done(function(data){
				if(data.success){
					/*var d = new Date();
					var strDate = d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();*/
					var dateAr = data.expiring.split('-');
					var newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
					if(data.show == 'y'){
						$('#swexpi').addClass('has-warning');
					}
					if(data.show == 'g'){
						$('#swfoot').show();
						$('#swexpi').addClass('has-success');
						//deshabilitamos pagos si no esta el vencimiento
						alert(Lang.messages.Thepaymentorthiscustomerhas);
					}
					if(data.show == 'r'){
						$('#swfoot').show();
						$('#swexpi').addClass('has-error');
					}

					$('#amount').val(data.amount);
					$('#nbill').val(data.nbill);
					$('#plan').val(data.plan);
					$('#router').val(data.router);
					$('#expiring').val(newDate);
					$('#nbill').val(data.nbill);
					//mostramos
					$('#swexpi').show('fast');
					$('#swamount').show('fast');
					$('#swnbill').show('fast');
					$('#swplan').show('fast');
					$('#swrouter').show('fast');
					$('#swtotalpay').show('fast');
					$('#swprint').show('fast');
					$('#addbtnpay').show();
					$('#addbtnpritn').show();
				}
			});
    	},
         ajax: {
		 url:"client/getclient/client",
		 method:"POST",
		 preDispatch: function (query) {
            return {
                search: query,
				filter: $('#filter').val()
            }
        	}
		 },
		 scrollBar:true
});


//inicio de tabla pagos
var styleb = '<div class="hidden-sm hidden-xs action-buttons">';
var stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
var stylee = '<span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a></li></ul></div></div>';

var treload = $('#payments-table').DataTable({
	"oLanguage": {
      			"sUrl": Lang.app.datatables
    },
	bAutoWidth: false,
    dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
    processing: true,
    serverSide: true,
    pageLength: '10',
    responsive: true,
	 destroy: true,
	"columnDefs": [ {
    "targets": 7,
    "render": function ( data, type, full ) {

					return styleb+'</a><a class="green send-client" href="#" id="'+full['id']+'" title="Enviar por email"><i class="ace-icon fa fa-paper-plane-o bigger-130"></i></a><a class="blue print" href="reprint/'+full['id']+'" target="_black" data-toggle="modal" title="Imprimir"><i class="ace-icon fa fa-print bigger-130"></i></a><a class="red del" href="#" id="'+full['id']+'" title="Eliminar"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>'+stylem+'<a href="#" class="tooltip-info send-client" id="'+full['id']+'" data-rel="tooltip"  title="Enviar por email"><span class="green"><i class="ace-icon fa fa-paper-plane-o bigger-120"></i></span></a></li><li><a href="#" class="tooltip-info imprimir" data-rel="tooltip" title="Imprimir" id="'+full['id']+'"><span class="blue"><i class="ace-icon fa fa-print bigger-120"></i></span></a></li><li><a href="#" class="tooltip-error del" id="'+full['id']+'" data-rel="tooltip" title="Eliminar">'+stylee;
    }
	}
	 ],
	ajax: {
            "url": "bill/list",
            "type": "POST",
			"cache":false,
			"dataSrc": ""
        },
    columns: [
		{ data: 'client_name' },

		{ "mRender": function ( data, type, full ) {
			var dateAr = full['expiries_date'].split('-');
			var dateEn = full['after_date'].split('-');
			var newDate = dateAr[2] + '/' + dateAr[1] + '/' + dateAr[0];
			var newDate2 = dateEn[2] + '/' + dateEn[1] + '/' + dateEn[0];
			return newDate+' al '+newDate2;
		 }},
        { data: 'total_amount' },
		{ data: 'num_bill' },
		{ "mRender": function ( data, type, full ) {
			var dateAr = full['pay_date'].split('-');
			var newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
			return newDate;
		 }},
		{ data: 'plan_name' },
		{ data: 'router_name' }
    ]
} );

//enviar factura al correo del cliente
$(document).on('click','.send-client',function(event){

	var idp = $(this).attr ("id");
		bootbox.confirm(Lang.app.proofofpaymenttotheclient, function(result) {

				if(result) {

					startloading('body',Lang.messages.SendingProof+'...');

							$.ajax ({
							type: "POST",
							url: "bill/sendmail",
							data: { "id" : idp },
							dataType: "json",
							'error': function (xhr, ajaxOptions, thrownError) {
								debug(xhr,thrownError);
							}
							}).done(function(data){
									treload.ajax.reload();

									if(data.msg=='error'){
										$('body').loadingModal('destroy');
										msg(Lang.messages.Thepaymentwasnotfound, 'error');
									}
									if(data.msg=='success'){
										$('body').loadingModal('destroy');
										msg(Lang.messages.Proofofpaymentwas, 'success');
									}

							});
						}
	});

});


//agregar pago
$(document).on('click','#addbtnpay',function(event){
event.preventDefault();
	var id = $('#client_id').val();
	var cant = $('#numpays').val();
	var $btn = $(this).button('loading');
			$.ajax({
			"url":"bill/create",
			"type":"POST",
			"data":{"id":id,"cant":cant},
			"dataType":"json",
			'error': function (xhr, ajaxOptions, thrownError) {
				debug(xhr,thrownError);
			}
			}).done(function(data){
				if(data[0].msg=='success'){
					treload.ajax.reload();
					msg(Lang.messages.Paymentwasaddedcorrectly, 'success');
					$('#add').modal('toggle');
				}

				if(data[0].msg=='errorConnect')
					msg(Lang.messages.Itisnotpossibleto,'error');
				if(data[0].msg=='errorConnectLogin')
					msg(Lang.messages.thisnotpossibletolog,'error');
				//mikrotik errors
				if(data[0].msg=='mkerror'){
					$.each(data, function(index, value)
					{
						msg(value.message,'mkerror');
					});
				}

				$btn.button('reset');
		});
});

//agregar pago e imprimir
$(document).on('submit','#formaddpay',function(){

			var id = $('#client_id').val();
			var cant = $('#numpays').val();
			var $btn = $(this).button('loading');
			$.ajax({
				"url":"bill/create",
				"type":"POST",
				"data":{"id":id,"cant":cant},
				"dataType":"json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){
				if(data[0].msg=='success'){
					treload.ajax.reload();
					msg(Lang.messages.Paymentwasaddedcorrectly, 'success');
					$('#add').modal('toggle');
				}

				if(data[0].msg=='errorConnect')
					msg(Lang.messages.itIsNotPossibleToAccess,'error');
				if(data[0].msg=='errorConnectLogin')
					msg(Lang.messages.ItIsNotPossibleToLogInToRouter,'error');

				//mikrotik errors
				if(data[0].msg=='mkerror'){

						$.each(data, function(index, value)
							{
								 msg(value.message,'mkerror');
							});
				}
				$btn.button('reset');
			});
});

$('#formaddpay').bind('keypress', function(e){
       if(e.keyCode == 13) { e.preventDefault(); }
});

//fin de agregar pagos
// recargar tabla
$(document).on("click",".recargar", function (event) {
 treload.ajax.reload();
});
//eliminar pago
$(document).on("click", '.del', function (event) {
	var idp = $(this).attr ("id");
		bootbox.confirm(Lang.messages.permanentlydeletethepayment, function(result) {
				if(result) {
							$.ajax ({
							type: "POST",
							url: "bill/delete",
							data: { "id" : idp },
							dataType: "json",
							'error': function (xhr, ajaxOptions, thrownError) {
								debug(xhr,thrownError);
							}
							}).done(function(data){
									treload.ajax.reload();
									if(data.msg=='error')
										msg(Lang.messages.Thepaymentwasnotfound, 'error');
									if(data.msg=='success')
										msg(Lang.messages.Paymentwasdeleted, 'success');
							});
						}
					});
 });
//fin del ready
//accesibilidad
$('#add').on('shown.bs.modal', function () {
  $('#name').focus()
})




//fin de accesibilidad
});
