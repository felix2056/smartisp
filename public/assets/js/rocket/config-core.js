// Config Core - Funciones principales JQuery para configuraciones
$(document).ready(function (e) {
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
//aditional
    bootbox.setDefaults("locale", locale) //traslate bootbox
//ocultamos campos
    $('#btnsavemkapi,#showport').hide();
//cargamos información de General
    $.ajax({
        "type": "POST",
        "url": "config/getconfig/general",
        "data": {},
        "dataType": "json",
        'error': function (xhr, ajaxOptions, thrownError) {
            debug(xhr, thrownError);
        }
    }).done(function (data) {
        if (data.success) {
			console.log(data);
            $('#name').val(data.company);
            $('#company_email').val(data.company_email);
            $('#dni').val(data.dni);
            $('#phone').val(data.phone);
            $('#smoney').val(data.smoney);
            $('#money').val(data.money);
            $('#nbill').val(data.numbill);
            $('#inputdays').val(data.numdays);
            $('#hrsemail').val(data.hrs);
            $('#hrsbackup').val(data.hrsbackups);
            $('#tolerance').val(data.tolerance);
            //sistem
            $('#smtpserver').val(data.server);
            $('#email').val(data.email);
            $('#port').val(data.port);
            $('#zone').val(data.zone);
            $('#locationdefault').val(data.default_map);
            $('#emailtickets').val(data.email_ticket);
            $('#delaysend').val(data.delay_sms);
            $('#phonecode').val(data.phone_code);

            var myText = data.zone;
            $("#zone").children().filter(function () {
                return $(this).val() == myText;
            }).prop('selected', true);

            var myText2 = data.protocol;
            $("#protocol").children().filter(function () {
                return $(this).val() == myText2;
            }).prop('selected', true);

            if (data.backups == 1)
                $('#backups').prop('checked', 'true');
            else
                $('#backups').removeAttr('checked');

            if (data.sendprebill == 1)
                $('#preadv').prop('checked', 'true');
            else
                $('#preadv').removeAttr('checked');

            if (data.sendpresms == 1)
                $('#presms').prop('checked', 'true');
            else
                $('#presms').removeAttr('checked');

            if (data.sendprewhatsapp == 1)
                $('#prewhatsapp').prop('checked', 'true');
            else
                $('#prewhatsapp').removeAttr('checked');

			if (data.sendprewaboxapp == 1)
                $('#prewaboxapp').prop('checked', 'true');
            else
                $('#prewaboxapp').removeAttr('checked');

            if (data.sendprewhatsappcloudapi == 1)
                $('#prewhatsappcloudapi').prop('checked', 'true');
            else
                $('#prewhatsappcloudapi').removeAttr('checked');

            if (data.debug == 1)
                $('#debug').prop('checked', 'true');
            else
                $('#debug').removeAttr('checked');
        } else
            msg('No se pudo obtener información de la base de datos', 'error');
    });
//fin de cargar informacion general

//guardar conf sistema smtp
    $(document).on('click', '#btnsavesmtp', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#smtpform').serialize();


        $.ajax({
            "type": "POST",
            "url": "config/smtp",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });
//guardar conf facturacion electronica
    $(document).on('submit', "#logoform2", (function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            "url": "config/factel",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"

        }).done(function (data) {
            //Mesajes personalizados

            if (data.msg == 'nofile') {
                msg('No selecciono el archivo.', 'error');
            }
            if (data.msg == 'errorupload') {
                msg('No se pudo subir la imágen.', 'error');
            }
            if (data.msg == 'noformat') {
                msg('La extesión ó tamaño de la imágen no son correctos.', 'error');
            }
            if (data.msg == 'nofile') {
                msg('No selecciono un archivo válido.', 'error');
            }
            //fin de mensajes personalizados
            if (data.msg == 'success') {

                msg('Los datos fueron guardados con exito.', 'success');
                setInterval(function () {
                    document.location = "config";
                }, 1200); //1 seconds
            }
        });
    }));

    //guardar conf facturacion electronica
    $(document).on('click', "#saveVenezuala1", (function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $.easyAjax({
            type: 'POST',
            url: 'config/venezuala',
            container: "#saveVenezuala",
            data:$('#saveVenezuala').serialize()
        });

    }));

//guardar conf sistema stado facturacion electronica
    $(document).on('click', '#btnsavefactel', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var status = $($("input[name='status_factel']:checked")).val();

        $.ajax({
            "type": "POST",
            "url": "config/factelstatus",
            "data": {"status": status},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == 'errorproduct') {
                msg('No existe una firma digital', 'lic');
            }

            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

            if (data.msg == 'nosmtp')
                msg('No configuro el Email SMTP principal.', 'system');

        });
    });

// funcion para guardar la informacion del emisor
    $(".form_emisor").on('submit', (function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            "url": "config/emisor",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"

        }).done(function (data) {
            //Mesajes personalizados

            if (data.msg == 'nofile') {
                msg('No selecciono el archivo.', 'error');
            }
            if (data.msg == 'errorupload') {
                msg('No se pudo subir la imágen.', 'error');
            }
            if (data.msg == 'noformat') {
                msg('La extesión ó tamaño de la imágen no son correctos.', 'error');
            }
            if (data.msg == 'nofile') {
                msg('No selecciono un archivo válido.', 'error');
            }
            //fin de mensajes personalizados
            if (data.msg == 'success') {

                msg('Los datos fueron guardados con exito.', 'success');
                setInterval(function () {
                    document.location = "config";
                }, 1200); //1 seconds
            }
        });
    }));


//guardar conf stripe gateway
    $(document).on('click', '#btnstripe', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form-stripe').serialize();


        $.ajax({
            "type": "POST",
            "url": "config/stripegateway",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });

    //guardar conf stripe gateway
    $(document).on('click', '#btnDirectoPago', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form-directo-pago').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/directopago",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });

    $('#form-pay-valida').on('submit', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $(event.target).serialize();

        $.ajax({
            "type": "POST",
            "url": "config/payvalida",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });

//guardar conf payu gateway
    $(document).on('click', '#btnpayu', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form-payu').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/payugateway",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });

//guardar conf paypal gateway
    $(document).on('click', '#btnpaypal', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form-paypal').serialize();


        $.ajax({
            "type": "POST",
            "url": "config/paypalgateway",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });


//guardar conf payu gateway
    $(document).on('click', '#btnsaveemail_f', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form_email_f').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/email_f",
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });


//guardar conf sistema email tickets
    $(document).on('click', '#btnsavesemailticket', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var email = $('#emailtickets').val();

        $.ajax({
            "type": "POST",
            "url": "config/emailticket",
            "data": {"email": email},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == 'errorproduct') {
                msg('SmartISP no esta activado, deberá renovar o adquirir una nueva licencia.', 'lic');
            }

            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

            if (data.msg == 'nosmtp')
                msg('No configuro el Email SMTP principal.', 'system');

        });
    });

//guardar conf sistema zona horaria
    $(document).on('click', '#btnsavezone', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var zone = $('#zone').val();

        $.ajax({
            "type": "POST",
            "url": "config/zone",
            "data": {"zone": zone},
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
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });

//guardar coordenadas por defecto
    $(document).on('click', '#btnsavedefaultmap', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        /* Act on the event */
        var map = $('#locationdefault').val();

        $.ajax({
            "type": "POST",
            "url": "config/defaultmap",
            "data": {"map": map},
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
                msg('Los datos fueron guardados con exito.', 'success');

        });

    });


//guardar conf save language
    $(document).on('click', '#btnsavelanguage', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form_select_language').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/savelocale",
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
                msg('Los datos fueron guardados con exito.', 'success');
            window.location.reload();
        });
    });
//guardar conf save language
    $(document).on('click', '#btnsavelanguagesettings', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var data = $('#form_language_settings').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/language-setting",
            "data": data,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                if ('errors' in JSON.parse(xhr.responseText)) {
                    msg(JSON.parse(xhr.responseText).errors.language_code[0], 'error');
                } else {
                    debug(xhr, thrownError)
                }
            }
        }).done(function (data) {


            if (data.message == 'The given data was invalid.') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');
            window.location.reload();

        });
    });

    //Save map type
    $(document).on('click', '#btnsavemap', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#form_choose_map').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/maptype",
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
                msg('Los datos fueron guardados con exito.', 'success');
            window.location.reload();
        });
    });

    function load_device() {
        $('#getdeviceid').html('<i class="fa fa-cog fa-spin fa-lg"></i>');
        $('#deviceid').empty();

        $.ajax({
            "type": "POST",
            "url": "config/getconfig/deviceid",
            "data": {"token": $('#tokensms').val()},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {


            $.each(data, function (i, val) {

                $('#deviceid').append($('<option>').text(val['name']).attr('value', val.id));

            });


            if (data.msg == 'error') {
                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            $('#getdeviceid').html('<i class="fa fa-bolt"></i>');

        });
    }


//obtener la id del sipositivo para SMS Gateway
    $(document).on('click', '#getdeviceid', function () {

        //load_device();

    });

    $('#tokensms').focusout(function (event) {
        //load_device();
    });


//guardar conf sms modem
    $(document).on('click', '#btnsmsmikrotik', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formsmsmikrotik').serializeArray();

        if ($('#smsg').is(':checked')) {
            var eng = 1;
        } else {
            var eng = 0;
        }

        data.push({name: 'eng', value: eng});

        $('#btnsmsmikrotik').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/modem",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnsmsmikrotik').html('Guardar');

        });
    });


//guardar configuracion generar de sms
    $(document).on('click', '#btnsmsgeneral', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formsmsgeneral').serialize();

        $('#btnsmsgeneral').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/generalsms",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnsmsgeneral').html('Guardar');

        });
    });


    $(document).on('click', '#btnformwhatsapp', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formwhatsapp').serialize();

        $('#btnformwhatsapp').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/whatsappsms",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnformwhatsapp').html('Guardar');

        });
    });

    $(document).on('click', '#btnformwhatsappcloudapi', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formwhatsappcloudapi').serialize();

        $('#btnformwhatsappcloudapi').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/whatsappcloudapi",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnformwhatsappcloudapi').html('Guardar');

        });
    });






    $(document).on('click', '#btnformweboxapp', function (event) {

        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formweboxapp').serialize();

        $('#btnformweboxapp').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/weboxapp",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnformweboxapp').html('Guardar');

        });
    });






//auto cambiar cambiar swich
    $(document).on('change', '#defaulsms', function () {
        if ($('#smsg').is(':checked')) {
            $('#defaulsms').removeAttr('checked');
        }
    });

    $(document).on('change', '#smsg', function () {
        if ($('#defaulsms').is(':checked')) {
            $('#smsg').removeAttr('checked');
        }
    });


//guardar configuracion sms gateway
    $(document).on('click', '#btnsmsmgateway', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var data = $('#formsmsgateway').serializeArray();

        if ($('#defaulsms').is(':checked')) {
            var enm = 1;
        } else {
            var enm = 0;
        }

        data.push({name: 'enm', value: enm});

        //data.push({name:'enm',value:enm},{name:'eng',value:'2'});


        $('#btnsmsmgateway').html('<i class="fa fa-cog fa-spin fa-lg"></i> Guardando...');

        $.ajax({
            "type": "POST",
            "url": "config/smsgateway",
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

            if (data.msg == 'success') {
                msg('Los datos fueron guardados con exito.', 'success');

            }

            $('#btnsmsmgateway').html('Guardar');

        });
    });

//lastar puertos usb al seleccionar router
    $(document).on('change', '#slcrouter', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if ($('#slcrouter').val() == '') {
            $('#showport').hide();
            return false;
        }

        $.ajax({
            "type": "POST",
            "url": "sms/getinfo/usb",
            "data": {"id": $('#slcrouter').val()},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {

            $('#lsport').empty();

            $.each(data, function (i, val) {
                $('#lsport').append($('<option>').text(val['name']).attr('value', val.name));
            });

            $('#showport').show();

        });

    });

//guardar y subir logo facturacion
    $(document).on('submit', '#logoform_f', (function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            "url": "config/logo_f",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"

        }).done(function (data) {
            //Mesajes personalizados

            if (data.msg == 'nofile') {
                msg('No selecciono el archivo.', 'error');
            }
            if (data.msg == 'errorupload') {
                msg('No se pudo subir la imágen.', 'error');
            }
            if (data.msg == 'noformat') {
                msg('La extesión ó tamaño de la imágen no son correctos.', 'error');
            }
            if (data.msg == 'nofile') {
                msg('No selecciono un archivo válido.', 'error');
            }
            //fin de mensajes personalizados
            if (data.msg == 'success') {

                msg('Los datos fueron guardados con exito.', 'success');
                setInterval(function () {
                    document.location = "config";
                }, 1200); //1 seconds
            }
        });
    }));


//guardar y subir logo
    $("#logoform").on('submit', (function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            "url": "config/logo",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"

        }).done(function (data) {
            //Mesajes personalizados

            if (data.msg == 'nofile') {
                msg('No selecciono el archivo.', 'error');
            }
            if (data.msg == 'errorupload') {
                msg('No se pudo subir la imágen.', 'error');
            }
            if (data.msg == 'noformat') {
                msg('La extesión ó tamaño de la imágen no son correctos.', 'error');
            }
            if (data.msg == 'nofile') {
                msg('No selecciono un archivo válido.', 'error');
            }
            //fin de mensajes personalizados
            if (data.msg == 'success') {

                msg('Los datos fueron guardados con exito.', 'success');
                setInterval(function () {
                    document.location = "config";
                }, 1200); //1 seconds
            }
        });
    }));

//funcion para verificar si esta configurado el smtp
    $('#preadv').change(function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        //verificamos si cambio la configuración de email por defecto del sistema
        $.ajax({
            "url": "config/getconfig/email",
            "type": "POST",
            "data": {},
            "dataType": "json"
        }).done(function (email) {


            if (email.status) {
                $('#preadv').removeAttr('checked');
                msg('No configuro el email smtp del sistema, ingrese a <b>configuración</b> pestaña <b>Sistema</b> opción "Email SMTP".', 'system');
            }

        });
    });

//obtener y seleccionar el router
    $(document).on("click", '#tabsms', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        //recuperamos todos los routers
        $.ajax({
            "url": "client/getclient/routers",
            "type": "POST",
            "data": {},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == 'norouters') {
                msg('No se encontraron routers, debe agregar al menos un router.', 'system');
            } else {

                $('#slcrouter').empty();

                $('#slcrouter').append($('<option>').text('Seleccione Router').attr('value', '').prop('selected', true));

                $ro = $.each(data, function (i, val) {
                    $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
                });


                //buscamos la información de la configuracion si exite mikrotik
                $.ajax({
                    type: "POST",
                    url: "sms/getinfo/gateway",
                    data: {},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    $('#delaysend').val(data.delaysms);
                    $('#tokensms').val(data.token);
                    var token = data.token;
                    var deviceid = data.deviceid;
                    //recuperamos todos los device id y seleccionamos el actual elegido

                    $('#deviceid').empty();

                    /*$.ajax({
                        "type":"POST",
                        "url":"config/getconfig/deviceid",
                        "data":{"token":token},
                        "dataType":"json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr,thrownError);
                        }
                    }).done(function(data){

                        $pr = $.each(data, function(i, val) {

                            $('#deviceid').append($('<option>').text(val['name']).attr('value', val.id));

                        });

                        $.when($pr).done(function() {

                            $("#deviceid").children().filter(function() {
                                return $(this).val() == deviceid;
                            }).prop('selected', true);

                        });

                    });
                    */


/////////////////////////////////////////////////////////////////


                    $("#phonecode").children().filter(function () {
                        return $(this).val() == data.phonecode;
                    }).prop('selected', true);

                    if (data.smsgateway == '1')
                        $('#smsg').prop('checked', 'true');
                    else
                        $('#smsg').removeAttr('checked');

                    if (data.smsmodem == 1)
                        $('#defaulsms').prop('checked', 'true');
                    else
                        $('#defaulsms').removeAttr('checked');

                    if (data.smsmodem == 1) {

                        var port = data.port;
                        var channel = data.channel;

                        $.when($ro).done(function () {
                            var myText = data.router;
                            $("#slcrouter").children().filter(function () {
                                return $(this).val() == myText;
                            }).prop('selected', true);
                        });
                        //list an auto select port usb

                        $.ajax({
                            "type": "POST",
                            "url": "sms/getinfo/usb",
                            "data": {"id": $('#slcrouter').val()},
                            "dataType": "json",
                            'error': function (xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function (data) {

                            $('#lsport').empty();

                            $po = $.each(data, function (i, val) {
                                $('#lsport').append($('<option>').text(val['name']).attr('value', val.name));
                            });

                            $.when($po).done(function () {
                                var myText = port;
                                $("#lsport").children().filter(function () {
                                    return $(this).val() == myText;
                                }).prop('selected', true);
                            });

                            $('#channel').val(channel);


                            $('#showport').show();

                            //codigos de paises
                            $("#phonecode").select2();

                        });


                    }//end if
                    else {
                        //codigos de paises
                        $("#phonecode").select2();
                    }

                });//end ajax
                //fin de informacion de la configuracion sms mikrotik

            }//end else

        });
        //fin de la funcion para recuperar los routers

    });


//guardar conf General
    $(document).on('click', '#savebtnGeneral', function (event) {
        event.stopImmediatePropagation();
        var name = $('#name').val();
        var company_email = $('#company_email').val();
        var company_street = $('#street').val();
        var company_state = $('#state').val();
        var company_country = $('#country').val();
        var dni = $('#dni').val();
        var phone = $('#phone').val();
        var smoney = $('#smoney').val();
        var money = $('#money').val();
        var nbill = $('#nbill').val();
        var email = $('#gemail').val();
        var numdays = $('#inputdays').val();
        var tolerance = $('#tolerance').val();
        var hrsemail = $('#hrsemail').val();
        var hrsbackup = $('#hrsbackup').val();
        var template_id = $('#invoice_template_id').val();
        var router_interval = $('#router-interval').val();

        if ($('#preadv').is(':checked'))
            var preadv = 1;
        else
            var preadv = 0;

        if ($('#backups').is(':checked'))
            var autoba = 1;
        else
            var autoba = 0;

        if ($('#presms').is(':checked'))
            var presms = 1;
        else
            var presms = 0;

        if ($('#prewhatsapp').is(':checked'))
            var prewhatsapp = 1;
        else
            var prewhatsapp = 0;

		if ($('#prewaboxapp').is(':checked'))
            var prewaboxapp = 1;
        else
            var prewaboxapp = 0;

        if ($('#prewhatsappcloudapi').is(':checked'))
            var prewhatsappcloudapi = 1;
        else
            var prewhatsappcloudapi = 0;


        $.ajax({
            "type": "POST",
            "url": "config/general",
            "data": {
                "backup": autoba,
                "router_interval": router_interval,
                "hrsbackup": hrsbackup,
                "company": name,
                "company_email": company_email,
                "dni": dni,
                "phone": phone,
                "smoney": smoney,
                "money": money,
                "nbill": nbill,
                "email": email,
                "preadv": preadv,
                "presms": presms,
                "prewhatsapp": prewhatsapp,
				"prewaboxapp": prewaboxapp,
                "prewhatsappcloudapi": prewhatsappcloudapi,
                "numdays": numdays,
                "tolerance": tolerance,
                "hrsemail": hrsemail,
                "invoice_template_id": template_id,
                "street": company_street,
                "state": company_state,
                "country": company_country,
            },
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {


            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');
            //Mesajes personalizados
            if (data.msg == 'error') {
                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }
            //fin de mensajes personalizados
        });
    });
//fin de guardar conf General

    //guardad aviso
    $(document).on('click', '#savebtnadv', function (event) {
        event.stopImmediatePropagation();
        var datos = $('#formaddadv').serialize();
        $.ajax({
            "type": "POST",
            "url": "config/adv",
            "data": datos,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados


            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            //fin de mensajes personalizados
            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });
    //fin de guardar aviso

    //guardar api mikrotik
    $(document).on('click', '#btnsavemkapi', function (event) {
        event.stopImmediatePropagation();
        var datos = $('#formapimk').serialize();
        $.ajax({
            "type": "POST",
            "url": "config/apimikrotik",
            "data": datos,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados


            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            //fin de mensajes personalizados
            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

        });
    });
    //fin de guardar api mikrotik

    //guardar api google maps
    $(document).on('click', '#btnsaveapimaps', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */
        var datos = $('#formapimaps').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/apimaps",
            "data": datos,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados


            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            //fin de mensajes personalizados
            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

        });


    });

    $(document).on('click', '#btnsavesmartoltapi', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */
        var datos = $('#formapiolt').serialize();

        $.ajax({
            "type": "POST",
            "url": "config/smartolt",
            "data": datos,
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados


            if (data.msg == 'error') {
                var arrs = data.errors;
                $.each(arrs, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            //fin de mensajes personalizados
            if (data.msg == 'success')
                msg('Los datos fueron guardados con exito.', 'success');

        });


    });


    //recuperar datos de apis
    $(document).on('click', '#lsapis', function (event) {
        event.stopImmediatePropagation();
        $.ajax({
            "type": "POST",
            "url": "config/getconfig/apis",
            "data": {},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == true) {

                $('#attempts').val(data.mk_attempts);
                $('#Timeout').val(data.mk_timeout);
                $('#btnsavemkapi').show();
                if (data.mk_debug == 'true') {
                    $('#mkdebug').prop('checked', 'true');
                } else {
                    $('#mkdebug').removeAttr('checked');
                }
                if (data.mk_ssl == 'true') {
                    $('#mkssl').prop('checked', 'true');
                } else {
                    $('#mkssl').removeAttr('checked');
                }
                //google maps api
                $('#maps').val(data.gmap_key);

                $('#url_smartolt').val(data.url_smartolt);
                $('#apikey_smartolt').val(data.apikey_smartolt);
                if (data.check_smartolt == 'true') {
                    $('#check_smartolt').prop('checked', 'true');
                } else {
                    $('#check_smartolt').removeAttr('checked');
                }


            } else {
                $('#btnsavemkapi').hide();
                alert('Error al cargar imformación de las APIS');
            }
        });
    });
    //fin de recuperar datos de apis

    //recuperar mensaje de corte
    $('#adv').click(function (event) {
        event.stopImmediatePropagation();
        $.ajax({
            "type": "POST",
            "url": "config/getconfig/adv",
            "data": {},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.success) {
                $('#idadv').val(data.id);

                $("#url").attr("placeholder", data.ips);

                $('#url').val(data.url);
                $('#path').val(data.path);

            } else {
                $('#url').val();
            }
        });
    });
    //fin de mensaje de corte


    //reestablecer valores sistema
    $('#ressys').change(function (event) {
        event.stopImmediatePropagation();
        idp = "reset-System421"
        bootbox.confirm("¿ Esta seguro de reestablecer el sistema ?", function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "config/ressys",
                    data: {"idp": idp},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.msg == 'error') {
                        $('#ressys').removeAttr('checked');
                        msg('No se pudo realizar la operación.', 'error');
                    }
                    if (data.msg == 'success') {
                        $('#ressys').removeAttr('checked');
                        msg('El sistema fue restaurado al momento de la instalación', 'info');
                        setInterval(function () {
                            window.location = 'config';
                        }, 1600); //1 seconds

                    }
                });
            } else
                $('#ressys').removeAttr('checked');
        });
    });

    //Activar modo depuración
    $('#debug').change(function (event) {
        event.stopImmediatePropagation();
        bootbox.confirm("¿ Esta seguro de activar o desactivar el modo depuración ?", function (result) {
            if (result) {
                if ($('#debug').is(':checked'))
                    var idb = 1;
                else
                    var idb = 0;

                $.ajax({
                    type: "POST",
                    url: "config/debug",
                    data: {"idb": idb},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {
                    if (data.msg == 'error') {

                        msg('No se pudo realizar la operación.', 'error');
                    }
                    if (data.msg == 'success') {

                        msg('Los datos fueron guardados con exito.', 'success');
                    }
                });
            } else {
                $('#debug').removeAttr('checked');
            }

        });
    });
    //fin de modo depuración

    //Borrar Cache del sistema

    $('#cache').change(function (event) {
        event.stopImmediatePropagation();
        if ($('#cache').is(':checked'))
            var idc = 1;
        else
            var idc = 0;
        $.ajax({
            type: "POST",
            url: "config/cache",
            data: {"idc": idc},
            dataType: "json",
            error: function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.msg == 'error') {
                msg('No se pudo realizar la operación.', 'error');
                $('#cache').removeAttr('checked');
            }
            if (data.msg == 'success') {
                msg('Se ha borrado todo el cache del sistema.', 'success');
                $('#cache').removeAttr('checked');
                setInterval(function () {
                    window.location = 'config';
                }, 1600); //1 seconds
            }
            $('#cache').removeAttr('checked');
        });
    });
    //fin de borrar cache del sistema

    //fin de reestablecer valores sistema

    //reestablecer valores pagos
    $('#respay').change(function (event) {
        event.stopImmediatePropagation();
        idp = "reset-System325"
        bootbox.confirm("¿ Esta seguro de reestablecer todos los pagos ?", function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "config/ressys",
                    data: {"idp": idp},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.msg == 'error')
                        msg('No se pudo realizar la operación.', 'error');
                    if (data.msg == 'success') {
                        $('#respay').removeAttr('checked');
                        msg('Operación realizada con exito.', 'info');
                    }
                });
            } else
                $('#respay').removeAttr('checked');
        });
    });
    //fin de reestablecer valores pagos
    //reestablecer valores logs
    $('#reslog').change(function (event) {
        event.stopImmediatePropagation();
        idp = "reset-System121"
        bootbox.confirm("¿ Esta seguro de reestablecer todos los logs ?", function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "config/ressys",
                    data: {"idp": idp},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {


                    if (data.msg == 'error') {
                        $('#reslog').removeAttr('checked');
                        msg('No se pudo realizar la operación.', 'error');
                    }
                    if (data.msg == 'success') {
                        $('#reslog').removeAttr('checked');
                        msg('Operación realizada con exito.', 'info');
                    }
                });
            } else
                $('#reslog').removeAttr('checked');
        });
    });
    //fin de reestablecer valores logs

    $('#file').ace_file_input({
        no_file: Lang.app.Selectafileonlyimages,
        btn_choose: Lang.app.select,
        btn_change: Lang.app.change,
        droppable: false,
        onchange: null,
        thumbnail: true, //| true | large
        whitelist: 'png',
        blacklist: 'exe|php|sql|gif|jpg'
        //onchange:''
        //
    });

//end aditional plugins
    //Timepicker
    $(".timepicker").timepicker({
        showInputs: false,
        minuteStep: 10,
        showMeridian: false,
    });

    $(".timepicker2").timepicker({
        showInputs: false,
        minuteStep: 15,
        showMeridian: false,
    });


    //google maps

    //validate coordinates
    function validateNewPlantsForm(latlng) {
        var latlngArray = latlng.split(",");
        for (var i = 0; i < latlngArray.length; i++) {
            if (isNaN(latlngArray[i]) || latlngArray[i] < -127 || latlngArray[i] > 90) {
                msg('Coordenadas no validas.', 'error');
                return false;
            }
        }

        return latlngArray;
    }


    function openmap(lat, lon, windowSelector, searchBox, locatioBox, map_type = 'google_map') {

        switch (map_type) {
            case 'google_map':
                load_google_map(lat, lon, windowSelector, searchBox, locatioBox);
                break;
            case 'open_street_map':
                load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox);
                break;
        }
    }

    function load_google_map(lat, lon, windowSelector, searchBox, locatioBox) {
        $('#' + windowSelector).locationpicker({

            location: {
                latitude: lat,
                longitude: lon
            },
            radius: 0,
            inputBinding: {
                locationNameInput: $(searchBox)
            },
            mapOptions: {mapTypeControl: true, streetViewControl: true},
            enableAutocomplete: true,
            //markerIcon: 'http://www.iconsdb.com/icons/preview/tropical-blue/map-marker-2-xl.png'
            onchanged: function (currentLocation, radius, isMarkerDropped) {
                $(locatioBox).val(currentLocation.latitude + "," + currentLocation.longitude);
            }
        });
    }

    function open_street_map(lat, lon, windowSelector, locatioBox) {
        if (typeof window.osmMap == 'undefined' || window.osMap == null) {
            let osm_layer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            });
            window.osmMap = L.map(windowSelector, {
                center: [lat, lon],
                zoom: 15,
                layers: [osm_layer]
            });

            let esri_satellite_layer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });
            let esri_places_layer = L.tileLayer('https://server.arcgisonline.com/arcgis/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}');
            var baseMaps = {
                "OpenStreetMap": osm_layer,
                "Satellite": esri_satellite_layer
            };

            var overlayMaps = {
                "Boundaries & Places": esri_places_layer
            };
            L.control.layers(baseMaps, overlayMaps).addTo(window.osmMap);
            window.osmMarker = L.marker([lat, lon], {
                'draggable': true
            }).addTo(window.osmMap);

            window.osmMarker.on('moveend', function () {
                let currentLocation = window.osmMarker.getLatLng();
                $(locatioBox).val(currentLocation.lat + "," + currentLocation.lng);
            });
        }
        return {
            'map': window.osmMap,
            'marker': window.osmMarker
        };
    }
    
    function load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox) {

        let osm = open_street_map(lat, lon, windowSelector, locatioBox);

        $(`[data-map-type="open_street_map"] ${searchBox}`).typeahead({
            highlight: true,
        },
        {
            name: 'brands',
            display: 'value',
            source: function(query, syncResults, asyncResults) {
                return $.get('https://photon.komoot.io/api/?q=' + query, function(data) {
                    data_set = [];
                    $(data.features).each(function (index, item) {
                        const city = typeof item.properties.city != 'undefined' ? ` - ${item.properties.city}` : '';
                        const state = typeof item.properties.state != 'undefined' ? ` - ${item.properties.state}` : '';
                        const postcode = typeof item.properties.postcode != 'undefined' ? ` - ${item.properties.postcode}` : '';

                        data_set.push({
                            id: index,
                            latlng: item.geometry.coordinates, 
                            value: `${item.properties.name} ${city} ${state} - ${item.properties.country} ${postcode}`
                        });

                    });
                    return asyncResults(data_set);
                }, 'json');
            }
        });

        $(`[data-map-type="open_street_map"] ${searchBox}`).on('typeahead:selected', function(evt, item) {
            osm.map.flyTo([item.latlng[1], item.latlng[0]], 15);
            osm.marker.setLatLng([item.latlng[1], item.latlng[0]]);
            $(locatioBox).val(item.latlng[1] + "," + item.latlng[0]);
        })
    }

    $('#modalmapedit').on('shown.bs.modal', function () {

        let map_type = $(this).data('map-type');
        var map = validateNewPlantsForm($('#locationdefault').val());

        if (map != false) {

            var lat_e = map[0];
            var lon_e = map[1];

            openmap(lat_e, lon_e, 'us4', '#us4-address', '#locationdefault', map_type);

        } else {

            var lat_e = '-34.60368440000001';
            var lon_e = '-58.381559100000004';

            openmap(lat_e, lon_e, 'us4', '#us4-address', '#locationdefault', map_type);
        }

    });


///End Google maps


});//end ready
