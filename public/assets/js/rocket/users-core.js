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
		"dataType":"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(deb){

		if(deb.debug=='1'){
			msg('Error ' +xhr.status +' '+  thrownError +' '+ xhr.responseText,'debug');
		}

		else
			alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
	});
}

bootbox.setDefaults("locale",locale); //traslate bootbox

    // treload = $('#users-table').DataTable({
    //     destroy: true,
    //     dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
    //     processing: true,
    //     serverSide: true,
    //     pageLength: '10',
    //     responsive: true,
    //     // deferRender: true,
    //     language: {
    //         "url": Lang.app.datatable
    //     },
    //
    //     ajax: {
    //         "url": "users/list",
    //         "type": "POST",
    //     },
    //
    //     "order": [[ 0, "asc" ]],
    //
    //     columns: [
    //         { data: 'name', name: 'name'},
    //         { data: 'email', name: 'email'},
    //         { data: 'phone', name: 'phone'},
    //         { data: 'username', name: 'username'},
    //         { data: 'created_at', name: 'created_at'},
    //         { data: 'status', name: 'status'},
    //         { data: 'action', name: 'action', searchable:false, sortable:false}
    //     ]
    // });

// fin de tabla clientes

//limpiar o copiar formulario
$('.peref').click(function(){
	// verificamos is esta copiando el formulario
	if(!$('#copy').is(':checked')){
		//reseteamos el formulario
		$('#formadduser')[0].reset();
	}
});
//fin de limpiar o copiar formulario

//agregar usuario
$(document).on('click','#addbtnuser',function(){
	var data = $('#formadduser').serialize();
    $.easyAjax({
        type: 'POST',
        url: "users/create",
        data: data,
        container: "#formadduser",
        success: function(data) {
            if(data.msg=='success'){
                $('#add').modal('toggle');
                window.LaravelDataTables["user-table"].draw();
                msg('El usuario fue añidido correctamente.', 'success');

            }
        }
    });

});
//fin de agregar cliente
// recargar tabla
$(document).on("click",".recargar", function (event) {
	window.LaravelDataTables["user-table"].draw();
});

//bloquear usuario
$(document).on("click",".ban-client",function(event){
	var idc = $(this).attr ("id");
	bootbox.confirm(Lang.app.areYouSureToActivateAccount, function(result) {
		if(result) {
			$.ajax ({
				type: "POST",
				url: "users/ban",
				data: { "id" : idc },
				dataType: "json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){

				if(data.msg=='error')
					msg('No se pudo bloquear al usuario.', 'error');
				if(data.msg=='success'){
					msg('El usuario fue bloqueado.', 'info');
					window.LaravelDataTables["user-table"].draw();
				}
			});
		}
	});
});
//fin de bloquear cliente
//eliminar usuario
$(document).on("click", '.del', function (event) {
	var idp = $(this).attr ("id");
	bootbox.confirm(Lang.messages.areYouSureToPermanentlyDeleteTheUser, function(result) {
		if(result) {
			$.ajax ({
				type: "POST",
				url: "users/delete",
				data: { "id" : idp },
				dataType: "json",
				'error': function (xhr, ajaxOptions, thrownError) {
					debug(xhr,thrownError);
				}
			}).done(function(data){
				window.LaravelDataTables["user-table"].draw();

				if(data.msg=='notfound')
					msg('No se encontro al usuario en la BD.', 'error');
				if(data.msg=='success')
					msg('El usuario fue eliminado.', 'success');
			});
		}
	});
});

//guardar editar cliente
$(document).on("click","#editbtnuser",function(event){
    $.easyAjax({
        type: 'POST',
        url: "users/update",
        data: $('#UserformEdit').serialize(),
        container: "#UserformEdit",
        success: function(data) {
            if(data.msg=='success'){
                msg('Datos actualizados correctamente.', 'info');
                $('#edit').modal('toggle');
                window.LaravelDataTables["user-table"].draw();
            }
        }
    });
});
//fin guardar editar usuario

$(document).on("click", '.editar', function (event) {
	$('#winedit').waiting({ fixed: true});
	$('[name=user]').val($(this).attr ('id'));
	var fdata = $('#val').serialize();

    // Hide previous errors
    $('#UserformEdit').find(".has-error").each(function () {
        $(this).find(".help-block").text("");
        $(this).removeClass("has-error");
    });

	$('#load').show();
	$.ajax({
		type:"POST",
		url:"user/getuser/data",
		data: fdata,
		dataType:"json",
		'error': function (xhr, ajaxOptions, thrownError) {
			debug(xhr,thrownError);
		}
	}).done(function(data){
		if (data.success) {
			$('#UserformEdit input[name="user_id"]').val(data.id);
			$('#UserformEdit input[name="edit_name"]').val(data.name);
			$('#UserformEdit input[name="edit_phone"]').val(data.phone);
			$('#UserformEdit input[name="edit_email"]').val(data.email);
			$('#UserformEdit input[name="edit_username"]').val(data.username);
			$('#UserformEdit input[name="password"]').val(null);
			$('#UserformEdit input[name="password_confirmation"]').val(null);

			if(data.status)
				$('#edit_status').prop('checked', 'true');
			else
				$('#edit_status').removeAttr('checked');

			console.log(data.level, "Hello from level");
			if(data.level == 'cs')

				$('#edit_cashdesk').prop('checked', 'true');
			else
				$('#edit_cashdesk').removeAttr('checked');

			if(data.acc_clients==1)
				$('#edit_acc_cli').prop('checked', 'true');
			else
				$('#edit_acc_cli'==1).removeAttr('checked');

			if(data.acc_access_clients_editar==1)
				$('#edit_cliente_editar').prop('checked', 'true');
			else
				$('#edit_cliente_editar'==1).removeAttr('checked');

			if(data.acc_access_clients_eliminar==1)
				$('#edit_cliente_eliminar').prop('checked', 'true');
			else
				$('#edit_cliente_eliminar'==1).removeAttr('checked');

			if(data.acc_access_clients_activate==1)
				$('#edit_cliente_activar').prop('checked', 'true');
			else
				$('#edit_cliente_activar'==1).removeAttr('checked');

			if(data.servicio_info==1)
				$('#edit_servicio_info').prop('checked', 'true');
			else
				$('#edit_servicio_info'==1).removeAttr('checked');

			if(data.servicio_edit==1)
				$('#edit_servicio_edit').prop('checked', 'true');
			else
				$('#edit_servicio_edit'==1).removeAttr('checked');

			if(data.servicio_delete==1)
				$('#edit_servicio_delete').prop('checked', 'true');
			else
				$('#edit_servicio_delete'==1).removeAttr('checked');

			if(data.servicio_activate_desactivar==1)
				$('#edit_servicio_activate_desactivar').prop('checked', 'true');
			else
				$('#edit_servicio_activate_desactivar'==1).removeAttr('checked');

			if(data.servicio_new==1)
				$('#edit_servicio_new').prop('checked', 'true');
			else
				$('#edit_servicio_new'==1).removeAttr('checked');

			if(data.acc_plans==1)
				$('#edit_acc_pla').prop('checked', 'true');
			else
				$('#edit_acc_pla').removeAttr('checked');

			if(data.acc_routers==1)
				$('#edit_acc_rou').prop('checked', 'true');
			else
				$('#edit_acc_rou').removeAttr('checked');

			if(data.acc_users==1)
				$('#edit_acc_use').prop('checked', 'true');
			else
				$('#edit_acc_use').removeAttr('checked');

			if(data.acc_system==1)
				$('#edit_acc_sys').prop('checked', 'true');
			else
				$('#edit_acc_sys').removeAttr('checked');

			if(data.acc_pays==1)
				$('#edit_acc_pay').prop('checked', 'true');
			else
				$('#edit_acc_pay').removeAttr('checked');

			if(data.acc_tools==1)
				$('#edit_acc_too').prop('checked', 'true');
			else
				$('#edit_acc_too').removeAttr('checked');

			if(data.acc_box)
				$('#edit_acc_tem').prop('checked', 'true');
			else
				$('#edit_acc_tem').removeAttr('checked');

			if(data.acc_rep==1)
				$('#edit_acc_reports').prop('checked', 'true');
			else
				$('#edit_acc_reports').removeAttr('checked');

			if(data.acc_tic==1)
				$('#edit_acc_ticket').prop('checked', 'true');
			else
				$('#edit_acc_ticket').removeAttr('checked');

			if(data.acc_sms==1)
				$('#edit_acc_sms').prop('checked', 'true');
			else
				$('#edit_acc_sms').removeAttr('checked');

			//editar_new code
			if(data.facturacion==1)
				$('#edit_facturacion').prop('checked', 'true');
			else
				$('#edit_facturacion').removeAttr('checked');

			if(data.tran_facturacion_editar==1)
				$('#edit_tran_facturacion_editar').prop('checked', 'true');
			else
				$('#edit_tran_facturacion_editar').removeAttr('checked');

			if(data.tran_facturacion_eliminar==1)
				$('#edit_tran_facturacion_eliminar').prop('checked', 'true');
			else
				$('#edit_tran_facturacion_eliminar').removeAttr('checked');

			if(data.factura_pagar==1)
				$('#edit_factura_pagar').prop('checked', 'true');
			else
				$('#edit_factura_pagar').removeAttr('checked');

			if(data.factura_editar==1)
				$('#edit_factura_editar').prop('checked', 'true');
			else
				$('#edit_factura_editar').removeAttr('checked');

			if(data.edit_client_balance==1)
				$('#edit_client_balance').prop('checked', 'true');
			else
				$('#edit_client_balance').removeAttr('checked');

			if(data.factura_eliminar==1)
				$('#edit_factura_eliminar').prop('checked', 'true');
			else
				$('#edit_factura_eliminar').removeAttr('checked');

			if(data.pagos_nuevo==1)
				$('#edit_pagos_nuevo').prop('checked', 'true');
			else
				$('#edit_pagos_nuevo').removeAttr('checked');

			if(data.pagos_editar==1)
				$('#edit_pagos_editar').prop('checked', 'true');
			else
				$('#edit_pagos_editar').removeAttr('checked');

			if(data.pagos_eliminar==1)
				$('#edit_pagos_eliminar').prop('checked', 'true');
			else
				$('#edit_pagos_eliminar').removeAttr('checked');

			if(data.finanzas==1)
				$('#edit_finanzas').prop('checked', 'true');
			else
				$('#edit_finanzas').removeAttr('checked');

			if(data.tran_finanzas_editar==1)
				$('#edit_tran_finanzas_editar').prop('checked', 'true');
			else
				$('#edit_tran_finanzas_editar').removeAttr('checked');

			if(data.tran_finanzas_eliminar==1)
				$('#edit_tran_finanzas_eliminar').prop('checked', 'true');
			else
				$('#edit_tran_finanzas_eliminar').removeAttr('checked');

			if(data.estado_financier==1)
				$('#edit_estado_financier').prop('checked', 'true');
			else
				$('#edit_estado_financier').removeAttr('checked');

			if(data.factura_finanzas_pagar==1)
				$('#edit_factura_finanzas_pagar').prop('checked', 'true');
			else
				$('#edit_factura_finanzas_pagar').removeAttr('checked');

			if(data.factura_finanzas_editar==1)
				$('#edit_factura_finanzas_editar').prop('checked', 'true');
			else
				$('#edit_factura_finanzas_editar').removeAttr('checked');

			if(data.factura_finanzas_eliminar==1)
				$('#edit_factura_finanzas_eliminar').prop('checked', 'true');
			else
				$('#edit_factura_finanzas_eliminar').removeAttr('checked');

			if(data.pagos_finanzas_editar==1)
				$('#edit_pagos_finanzas_editar').prop('checked', 'true');
			else
				$('#edit_pagos_finanzas_editar').removeAttr('checked');

			if(data.pagos_finanzas_eliminar==1)
				$('#edit_pagos_finanzas_eliminar').prop('checked', 'true');
			else
				$('#edit_pagos_finanzas_eliminar').removeAttr('checked');

			if(data.access_system==1)
				$('#edit_access_system').prop('checked', 'true');
			else
				$('#edit_access_system').removeAttr('checked');

			if(data.locations_access==1)
				$('#edit_locations_access').prop('checked', 'true');
			else
				$('#edit_locations_access').removeAttr('checked');

			if(data.maps_client_access==1)
				$('#edit_maps_client_access').prop('checked', 'true');
			else
				$('#edit_maps_client_access').removeAttr('checked');

			if(data.billing_setting_update==1)
				$('#billing_setting_update').prop('checked', 'true');
			else
				$('#billing_setting_update').removeAttr('checked');

			if(data.splitter==1)
				$('#edit_splitter').prop('checked', 'true');
			else
				$('#edit_splitter').removeAttr('checked');

			if(data.onu_cpe==1)
				$('#edit_onu_cpe').prop('checked', 'true');
			else
				$('#edit_onu_cpe').removeAttr('checked');

			$('#winedit').waiting('done');
			$('#load').hide();
		}else{
			$('#load').hide();
			msg('No se pudo cargar la información de la base de datos','error');
		}
	});
});

//accesibilidad
$('#add').on('shown.bs.modal', function () {
	$('#name').focus()
})
//fin de accesibilidad

//fin del ready
});
