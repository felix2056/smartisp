// Core tools users
jQuery(function ($) {
///// General Messages for system ///////
//Mesages for confirmatios success
    function msg(msg, type) {
        if (type == 'success') {
            var clase = 'gritter-success';
            var tit = Lang.app.registered;
            var img = 'assets/img/ok.png';
            var stincky = false;
        }
        if (type == 'error') {
            var clase = 'gritter-error';
            var tit = Lang.app.error;
            var img = 'assets/img/error.png';
            var stincky = false;
        }
        if (type == 'debug') {
            var clase = 'gritter-error gritter-center';
            var tit = Lang.app.errorInternoDebugMode;
            var img = '';
            var stincky = false;
        }
        if (type == 'info') {
            var clase = 'gritter-info';
            var tit = Lang.app.information;
            var img = 'assets/img/info.png';
            var stincky = false;
        }
        if (type == 'mkerror') {
            var clase = 'gritter-error';
            var tit = 'Error en Router';
            var img = '';
            var stincky = false;
        }
        if (type == 'system') {
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
    function debug(xhr, thrownError) {
        $.ajax({
            "url": "config/getconfig/debug",
            "type": "GET",
            "data": {},
            "dataType": "json"
        }).done(function (deb) {

            if (deb.debug == '1') {
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            } else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }

//// fin de la funcion de depuracion
    bootbox.setDefaults("locale", locale) //traslate bootbox
//ocultamos tipo de importacion
    $('#typeimp').hide();

    var date = new Date();
    date = ('0' + date.getDate()).slice(-2) + '-' + ('0' + (date.getMonth() + 2)).slice(-2) + '-' + date.getFullYear();


//datepicker plugin
    $('#id-date-picker-1').val(date);


    //link
    var fecha = $('.date-picker').datepicker({
        language: 'es',
        autoclose: true,
        todayHighlight: true
        //startView: 'year',
    });

//end aditional plugin
    $('#faq-list-1,#faq-tab-2,#btnimportprofile,#swprifiles').hide();
//funcion para recuperar los routers
    $('#faq-list-1').show();
//get routers
//     $.ajax({
//         "url": "client/getclient/routers",
//         "type": "POST",
//         "data": {},
//         "dataType": "json",
//         'error': function (xhr, ajaxOptions, thrownError) {
//             debug(xhr, thrownError);
//         }
//     }).done(function (data) {
//         if (data.msg == 'norouters') {
//             msg('No se encontraron routers, debe agregar al menos un router.', 'system');
//             $('#faq-list-1').hide();
//         } else {
//              $('#faq-list-1').show();
//             $('#slcrouter').append($('<option>').text('Seleccione Router').attr('value', '').prop('selected', true));
//             $.each(data, function (i, val) {
//                 $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
//             });
//         }
//
//     });
//fin de la funcion para recuperar los routers

    $(document).on('click', '#plsm', function (event) {
        event.stopImmediatePropagation();
        $('#CRouter').empty();
        //get routers
        $.ajax({
            "url": "client/getclient/router",
            "type": "POST",
            "data": {},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == 'norouters') {
                msg('No se encontraron routers con perfiles pppoe o hotspot, debe agregar al menos un router con estos perfiles.', 'system');
                $('#faq-5-1').hide();
            } else {
                $('#faq-5-1').show();
                $('#CRouter').append($('<option>').text('Seleccione Router').attr('value', '').prop('selected', true));
                $.each(data, function (i, val) {
                    $('#CRouter').append($('<option>').text(val['name']).attr('value', val.id));
                });
            }

        });
//fin de la funcion para recuperar los routers

    });


//funcion para obtener tipos de importacion segun el router seleccionado
    $(document).on('change', '#CRouter', function (event) {
        event.preventDefault();
        var sl = $('#CRouter').val();
        if (sl == '') { //si es vacio ocultamos todos campos
            $('#btnimportprofile,#swprifiles').hide();
            $('#btnimportprofile').html('<i class="fa fa-cog"></i> Importar');
        } else {

            //$('select[name="duallistbox_demo1[]"]').bootstrapDualListbox('refresh');

            //obtenemos la configuracion del router seleccionado

            $.ajax({
                "url": "router/getrouter/configplan",
                "type": "POST",
                "data": {"id": sl},
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {


                if (data.msg == true) {

                    //mostrar datos para ppp y hotspot

                    $('#duallist').empty();

                    $('select[name="profileslistbox[]"]').bootstrapDualListbox('refresh', true);

                    //reseteo de select boxes
                    //llenamos selects boxes
                    $ro = $.each(data[0], function (i, val) {
                        $('#duallist').append($('<option>').text(val['name']).attr('value', val.name));
                    });

                    var demo1 = $('select[name="profileslistbox[]"]').bootstrapDualListbox(
                        {
                            infoTextFiltered: '<span class="label label-purple label-lg">Filtrado</span>',
                            filterPlaceHolder: 'Filtrar',
                            infoTextEmpty: 'Lista vacía',
                            filterTextClear: 'mostrar todo',
                            removeAllLabel: 'Quitar todo',
                            moveAllLabel: 'Mover todo',
                            infoText: 'Items totales {0}'
                        }
                    );
                    var container1 = demo1.bootstrapDualListbox('getContainer');
                    container1.find('.btn').addClass('btn-white btn-info btn-bold');


                    $.when($ro).done(function () {
                        $('select[name="profileslistbox[]"]').bootstrapDualListbox('refresh', true);
                    });

                    //mostramos el Boton de inportación
                    $('#btnimportprofile').html('<i class="fa fa-cog"></i> Importar');
                    $('#btnimportprofile,#swprifiles').show();

                } else if (data.msg == 'errorConnect') {
                    msg('No es posible acceder al router, verifique que este en línea.', 'error');
                } else if (data.msg == 'errorConnectLogin') {
                    msg('No es posible iniciar sesión en el router, verifique los datos de acceso.', 'error');
                } else {

                    msg('No se encontraron configuraciones para este router, debe configurar el router, ingrese a <b>routers</b> opción editar "icono del lápiz".', 'error');
                }
            });


        }
    });


////guardar e importar perfile de router a planes
    $(document).on('click', '#btnimportprofile', function (event) {
        event.stopImmediatePropagation();
        var data = $('#formprofile').serialize();

        if ($('#duallist').val() == null) {
            alert("Debe importar al menos un perfil");
        } else {

            $('#btnimportprofile').html('<i class="fa fa-cog fa-spin"></i> Importando...');

            $.ajax({
                "type": "POST",
                "url": "toolsImport/profile",
                "data": data,
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {

                if (data.msg == 'error') {
                    var arrs = data.errors;
                    $.each(arrs, function (index, value) {
                        if (value.length != 0) {
                            msg(value, 'error');
                        }
                    });
                }

                if (data.msg == 'success')
                    msg('Los perfiles fueron importados con exito.', 'success');

                $('#btnimportprofile').html('<i class="fa fa-cog"></i> Importar');

            });
        }
    });


//funcion para verificar si configuro el correo smtp
    $('#tabsys').click(function (event) {
        event.stopImmediatePropagation();
        //verificamos si cambio la configuración de email por defecto del sistema
        $.ajax({
            "url": "config/getconfig/email",
            "type": "POST",
            "data": {},
            "dataType": "json"
        }).done(function (email) {
            if (email.status) {
                $('#faq-tab-2').hide();
                msg('No configuro el email smtp del sistema, ingrese a <b>configuración</b> opción "Email SMTP".', 'system');
            } else {
                $('#faq-tab-2').show();
            }
        });
    });

//fin de la funcion smtp

//funcion para obtener tipos de importacion segun el router seleccionado
    $(document).on('change', '#slcrouter', function (event) {
        event.preventDefault();
        var sl = $('#slcrouter').val();
        if (sl == '') {
            $('#typeimp').hide();
        } else {
            //obtenemos todas las ip redes del router seleccionado
            $.ajax({
                "url": "router/getrouter/control",
                "type": "POST",
                "data": {"id": sl},
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {
                $('#lstypes').empty();

                if (data != false) {

                    switch (data.control) {
                        case 'sq':
                            var json = [
                                {'type': 'Simple Queues', 'id': 'sq'}
                            ];

                            break;
                        case 'st':
                            var json = [
                                {'type': 'Simple Queues (with Tree)', 'id': 'st'}
                            ];

                            break;
                        case 'ho':
                            var json = [
                                {'type': 'Hotspot - User Profiles', 'id': 'ho'}
                            ];

                            break;
                        case 'dl':

                            var json = [
                                {'type': 'DHCP Leases', 'id': 'dl'},
                            ];

                            break;

                        case 'pp':
                            var json = [
                                {'type': 'PPP Secrets', 'id': 'pp'},
                            ];
                            break;

                        case 'ha':
                            var json = [
                                {'type': 'Hotspot - PCQ Address List', 'id': 'ha'},
                            ];
                            break;

                        case 'pa':
                            var json = [
                                {'type': 'PPPoE - Secrets - PCQ Address List', 'id': 'pa'},
                            ];

                            break;

                        case 'ps':
                            var json = [
                                {'type': 'PPPoE - Simple Queues', 'id': 'ps'}
                            ];
                            break;

                        case 'pt':
                            var json = [
                                {'type': 'PPPoE - Simple Queues (with Tree)', 'id': 'pt'}
                            ];
                            break;

                        case 'no':
                                var json = [
                                    {'type': 'Nose puede importar', 'id': 'none'}
                                ];
                            break;
                    }


                    $.each(json, function (i, val) {
                        $('#lstypes').append($('<option>').text(val['type']).attr('value', val.id));
                    });

                } else {
                    $('#lstypes').append($('<option>').text('No hay configuraciones').attr('value', 'none'));
                    msg('No se encontraron configuraciones para este router, debe configurar el router, ingrese a <b>routers</b> opción editar "icono del lápiz".', 'error');
                }
            });

            $('#typeimp').show();
        }
    });


//Importar clientes desde router
    $('#btnImport').click(function (event) {
        event.stopImmediatePropagation();
        bootbox.confirm("¿ Advertencia, al importar se sobreescribirán los clientes que se encuentren en el sistema asociados al router, desea continuar ?", function (result) {
            if (result) {
                importclients();
            }
        });


        function importclients() {

            let myForm = document.getElementById('formImport');
            var data = new FormData(myForm);
            console.log('hello from submit');
            $('#btnImport').html('<i class="fa fa-cog fa-spin fa-lg"></i> Importando...');
            $.ajax({
                "url": "toolsImport/import",
                "type": "POST",
                "data": data,
                "contentType": false,
                "cache": false,
                "processData": false,
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                    $('#btnImport').html('Importar');
                }
            }).done(function (data) {


                if (data.msg == 'error') {
                    var arrs = data.errors;
                    $('#btnImport').html('Importar');
                    $.each(arrs, function (index, value) {
                        if (value.length != 0) {
                            msg(value, 'error');
                        }
                    });
                    $('#btnImport').html('Importar');
                }


                if (data.msg == 'errornoclients') {
                    msg('No se encontraron clientes en el router.', 'error');
                    $('#btnImport').html('Importar');
                }
                if (data.msg == 'errorConnect') {
                    msg('No se pudo conectar con el router verifique que este en línea.', 'error');
                    $('#btnImport').html('Importar');
                }

                if (data.msg == 'successno') {
                    msg('No es posible importar con la configuración actual del router.', 'info');
                    $('#btnImport').html('Importar');
                }

                if (data.msg == 'allowedByYourLicense') {
                    msg('usted ha creado la totalidad de clientes permitidos por su licencia.', 'error');
                    $('#btnImport').html('Importar');
                }

                if (data.msg == 'Expiredlicense') {
                    msg('Licença expirada', 'error');
                    $('#btnImport').html('Importar');
                }

                if (data.msg == 'LicenseError') {
                    msg('Error de licencia.', 'error');
                    $('#btnImport').html('Importar');
                }

                if (data.msg == 'PleaseActivateALicense') {
                    msg('Por favor active una licencia para poder continuar con el registro.', 'error');
                    $('#btnImport').html('Importar');
                }
                if (data.msg == 'success') {
                    msg('La importación se ha completado.', 'success');
                    $('#btnImport').html('Importar');
                    $("#formImport")[0].reset()

                }


            });

        } //end function

    });

//Fin de inportar clientes

//enviar emails
    $('#bdtsend').click(function (event) {
        event.stopImmediatePropagation();
        var data = $('#formemail').serialize();

        $('#bdtsend').html('<i class="fa fa-cog fa-spin fa-lg"></i> Enviando...');
        $.ajax({
            "url": "tools/send",
            "type": "POST",
            "data": data,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
                $('#bdtsend').html('Enviar');
            }
        }).done(function (data) {

            if (data.msg == 'error') {
                var arrs = data.errors;
                $('#bdtsend').html('Enviar');

                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });

            }
            if (data.msg == 'noconfig')
                msg('No se encontro la configuración SMPT, debe establecer una configuración su cuenta de correo, ingrese a sistema - configuración.', 'error');
            if (data.msg == 'success') {
                msg('Se ha enviado el email.', 'success');
                $('#bdtsend').html('Enviar');

            }

        });
    });
//fin de enviar emails


//enviar emails
    $('#btnsendsms').click(function (event) {
        event.stopImmediatePropagation();
        var data = $('#formsms').serialize();

        $('#btnsendsms').html('<i class="fa fa-cog fa-spin fa-lg"></i> Enviando...');
        $.ajax({
            "url": "tools/sendsms",
            "type": "POST",
            "data": data,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
                $('#bdtsend').html('Enviar');
            }
        }).done(function (data) {

            if (data.msg == 'error') {
                var arrs = data.errors;
                $('##btnsendsms').html('Enviar');

                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });

            }
            if (data.msg == 'noconfig')
                msg('No se encontro la configuración de SMS, debe establecer una configuración de su modem, ingrese a sistema - configuración.', 'error');
            if (data.msg == 'success') {
                msg('Se ha enviado el SMS.', 'success');
                $('#btnsendsms').html('Enviar');

            }

        });
    });
//fin de enviar emails


//fin del ready
});
