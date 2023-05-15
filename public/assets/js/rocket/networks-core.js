// routers Core - Funciones principales JQuery para networks
$(document).ready(function(e) {
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

	if(type=='system'){
		var clase = 'gritter-light gritter-center';
		var tit = 'Información del sistema';
		var img = '';
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
//// fin de la funcion de depuracion
    bootbox.setDefaults("locale",locale) //traslate bootbox
//receteamos el form
	$(document).on('click','.peref',function(event){
		event.preventDefault();

		if(!$('#copy').is(':checked')){

			$('#formaddnetwork')[0].reset(); //reseamos el dormulario
			$("#textv").hide();
		}
	});


	var styleb = '<div class="action-buttons">';

	//inicio de tabla routers
	var treload = $('#routers-table').DataTable({
		"oLanguage": {
					"sUrl": Lang.app.datatable
		},
		bAutoWidth: false,
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        destroy: true,
		"columnDefs": [ {
			"targets": 5,
			"render": function ( data, type, full ) {
		  		if(full['status']=='of' || full['status']=='er' || full['status']=='nc'){
					return styleb+'<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'+full['id']+'"><i class="ace-icon fa fa-pencil bigger-130"></i></a><a class="red del" href="#" id="'+full['id']+'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
				}
				else{
					return styleb+'<a class="blue infor" href="#" data-toggle="modal" data-target="#info-router" id="'+full['id']+'"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'+full['id']+'"><i class="ace-icon fa fa-pencil bigger-130"></i></a><a class="red del" href="#" id="'+full['id']+'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
				}
			}
		}],
	ajax: {
            "url": "networks/list",
            "type": "POST",
			"cache":false,
			"dataSrc": ""
    },
    columns: [
        { data: 'network' },
        { data: 'hosts'},
        { "mRender": function ( data, type, full ) {
			/*if(full['model']=='none')
				return full['model'];
			else
				return '<a href="http://routerboard.com/'+full['model']+'" target="_blank">'+full['model']+'</a>';*/
			return '<div class="progress progress-striped"><div class="progress-bar progress-bar-warning" aria-valuemin="0" aria-valuemax="100" style="width: '+full['used']+'%;">'+full['used']+'%</div></div>';
		}},
        { data: 'name' },
		{ data: 'type' }

    ]
    });
// fin de tabla routers

// recargar tabla
$(document).on("click", '.recargar', function (event) {
	window.LaravelDataTables["network-table"].draw();
});
// fin de recarga de tabla


setTimeout(function(){ window.LaravelDataTables["network-table"].draw(); }, 3000);


//funcion para habilitar la función dhcp si selecciona el control DHCP Leases
$(document).on("change","#typecontrol", function(event){

	if($(this).val()=='dl'){
		$('#dhcp').prop('checked', 'true');
	}
	else {
		$('#dhcp').removeAttr('checked');
	}

});

//funcion para impedir cambiar dhcp si esta el control con dhcp leases
$(document).on("change","#dhcp",function(event){

	if($('#typecontrol').val()=='dl'){
		$('#dhcp').prop('checked', 'true');
	}
});

//eliminar ip/red
		$(document).on("click", '.del', function (event) {
			var idr = $(this).attr ("id");
				bootbox.confirm("¿ Esta seguro de eliminar la IP/red ?", function(result) {
						if(result) {
									$.ajax ({
									type: "POST",
									url: "networks/delete",
									data: { "id" : idr },
									dataType: "json",
									'error': function (xhr, ajaxOptions, thrownError) {
										debug(xhr,thrownError);
    								}
									}).done(function(data){

											if(data.msg=='notfound')
												msg('No se encontro la IP/red en la BD.', 'error');
											if(data.msg=='inused')
												msg('No es posible eliminar la IP/Red se encuentra en uso.', 'error');
											if(data.msg=='success'){
												msg('La IP/red fue eliminada.', 'success');
												window.LaravelDataTables["network-table"].draw();
											}
									});
						}
				});
		});
		//fin de eliminar router

		//añadir IP/red
		$(document).on("click", "#addbtnnetwork",function(event){
			var routerdata = $('#formaddnetwork').serialize();

			var $btn = $(this).button('loading');
				$.ajax({
					type: "POST",
					url:"networks/create",
					data: routerdata,
					dataType:"json",
					'error': function (xhr, ajaxOptions, thrownError) {
						debug(xhr,thrownError);
    				}
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

					if(data.msg=='duplicate'){
						msg('El Red ya ha sido tomado.', 'error');
					}

					if(data.msg=='success'){
						$('#add').modal('toggle');
						msg('La IP/red fue añadido correctamente, por ultimo deberá editar el router y vincular las IP/redes.', 'success');
						window.LaravelDataTables["network-table"].draw();

						//buscamos datos y abrimos el modal editar router

					}
					//fin de mensajes personalizados
					//restore button
					$btn.button('reset');
				});
		});
		//fin de añadir IP/Red

		//guardar editar IP/red
		$(document).on("click","#editbtnnetwork",function(event){

			var routerdata = $('#formeditnetwork').serialize();

			var $btn = $(this).button('loading');
				$.ajax({
					type:"POST",
					url:"networks/update",
					data:routerdata,
					dataType:"json",
					'error': function (xhr, ajaxOptions, thrownError) {
						debug(xhr,thrownError);
						$btn.button('reset');
    				}
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

					if(data.msg=='not-found')
						msg('No se encuentra la IP/Red en la BD.', 'error');
					if(data.msg=='success'){
						msg('La IP/Red fue actualizada correctamente.', 'info');
						$('#edit').modal('toggle');
						window.LaravelDataTables["network-table"].draw();
					}
					//restore button
					$btn.button('reset');
				});
		});

	//elimiar ip/red
	$(document).on('click','.eliminar', function(event){
		var id = $(this).attr ("id");
		var idro = $('#val').val();
		var faction = "routers/inte";
				if(confirm('¿Esta seguro de eliminar la red?')){
					$.post(faction, { "id" : id, "idro" : idro }, function(json) {
							if(json.msg=='error')
								msg('No se encontro la red en la BD.', 'error');
							if(json.msg=='errorConnect')
								msg('No es posible acceder al router, verifique que este en línea.','error');
							if(json.msg=='success'){
								msg('La red fue eliminada.', 'success');
								window.tred.ajax.reload();
							}
					});
				}
	});
	//fin de eliminar ip/red
//fin de la funcion para editar router

//get editar IP/Red
$(document).on("click", '.editar', function (event) {
		event.preventDefault();

		$('#mytabs').waiting({ fixed: true});
		var id = $(this).attr ('id');
		$('[name=netid]').val(id);
		$('#load').show();
		$.ajax({
				type:"POST",
				url:"network/getnetwork/data",
				data: {id:id},
				dataType:"json",
				'error': function (xhr, ajaxOptions, thrownError) {
						debug(xhr,thrownError);
    			}
			}).done(function(data){
				if (data.success) {

					$('#editname').val(data.name);
					$('#edit_address').html('<b>'+data.network+'</b>');

					var myText = data.type;
					$("#edit_routing").children().filter(function() {
						return $(this).val() == myText;
					}).prop('selected', true);


					$('#load').hide();

				}else{
					$('#load').hide();
					msg('No se pudo cargar la información de la base de datos','error');
				}
		}); //end ajax
}); //fin editar router

	//info ip/red
	$(document).on("click",".infor",function(event){
		var idn = $(this).attr('id');
		//recuperamos la info de la IP/red
		$.ajax({
			  "type":"POST",
			  "url":"network/getinfo/data",
			  "data":{"id":idn},
			  "dataType":"json",
			  'error': function (xhr, ajaxOptions, thrownError) {
				debug(xhr,thrownError);
    		  }
		  }).done(function(data){
			  if(data.success){
			  	  $('#gateway').text(data.gateway);
				  $('#address').text(data.address);
				  $('#networkd').text(data.network);
				  $('#maskbit').text(data.maskbit);
				  $('#maskadd').text(data.maskadd);
				  $('#classip').text(data.classip);
				  $('#hostrange').text(data.hostrange);
				  $('#broadcast').text(data.broadcast);
				  $('#totalips').text(data.totalips);
				  $('#binary').text(data.binary);
			  }
			  else{
				  alert("Error al obtener datos estadísticos");
			  }
		  });
	});

	 //accesibilidad
	 $('#add').on('shown.bs.modal', function () {
  		$('#name').focus()
	  })
	 //fin de accesibilidad

	 //aditional plugins
	 $('.ip_address').mask('099.099.099.099');
	 //end aditional plugins

	// $.ajax({
	// "url":"users/isloginuser",
	// "type":"POST",
	// "data":{},
	// "dataType":"json"
	// });

});//fin del ready
