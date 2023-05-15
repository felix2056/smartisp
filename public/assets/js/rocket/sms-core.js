// users Core - Funciones principales JQuery para SMS
jQuery(function($) {
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

    ///// star loading modal //////////////

    function startloading(selector, text) {

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
    function debug(xhr, thrownError) {
        $.ajax({
            "url": "config/getconfig/debug",
            "type": "GET",
            "data": {},
            "dataType": "json"
        }).done(function(deb) {

            if (deb.debug == '1') {
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            } else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }

    //// fin de la funcion de depuracion
    bootbox.setDefaults("locale", locale) //traslate bootbox
    //datepicker plugin

    //inicio de tabla mesajes enviados
    var styleb = '<div class="action-buttons">';

    var Oreload = $('#send-table').DataTable({
        "oLanguage": {
            "sUrl": Lang.app.datatables
        },
        bAutoWidth: false,
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        buttons: [
            'excel', 'csv'
        ],
        destroy: true,
        "columnDefs": [{
            "targets": 7,
            "render": function(data, type, full) {

                if (full['clname'] == "Grupo") {
                    return styleb + '<a class="blue infor" href="#" data-toggle="modal" data-target="#info" id="' + full['id'] + '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green reenviar" href="#" id="' + full['id'] + '"><i class="ace-icon fa fa-reply-all bigger-130"></i></a><a class="red del" href="#" id="' + full['id'] + '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                } else {
                    return styleb + '<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green reenviar" href="#" id="' + full['id'] + '"><i class="ace-icon fa fa-reply bigger-130"></i></a><a class="red del" href="#" id="' + full['id'] + '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }

            }
        }],
        ajax: {
            "url": "sms/listsend",
            "type": "POST",
            "cache": false,
            "dataSrc": ""
        },
        columns: [

            {
                data: 'clname'
            },
            {
                data: 'roname'
            },
            {
                data: 'phone'
            },
            {
                data: 'send_date'
            },
            {
                data: 'message'
            },
            {
                data: 'gateway'
            },
            {
                "mRender": function(data, type, full) {

                    var per = (Number(full['send_rate']) / Number(full['tcl'])) * 100;
                    var per = per.toFixed(0);
                    if (per == 100) {
                        return '<span class="label label-success">Enviado</span>';
                    } else {
                        return '<div class="progress"><div class="progress-bar progress-bar-striped active" aria-valuemin="0" aria-valuemax="100" style="width: ' + per + '%;">' + per + '%</div></div>';
                    }

                }
            },

        ]
    });
    // fin de tabla sms enviados

    // inicio tabla sms recibidos

    var Ireload = $('#insms-table').DataTable({
        "oLanguage": {
            "sUrl": Lang.app.datatables
        },
        bAutoWidth: false,
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        destroy: true,
        buttons: [
            'excel', 'csv'
        ],
        //destroy: true,
        "columnDefs": [{
            "targets": 5,
            "render": function(data, type, full) {

                if (full['open'] == 1) {

                    return styleb + '<a class="blue openanswer" href="#" data-toggle="modal" data-target="#openanswer1" id="' + full['id'] + '"><i class="ace-icon fa fa-envelope-open-o bigger-130"></i></a><a class="green answer" href="#" data-toggle="modal" data-target="#answer" id="' + full['id'] + '"><i class="ace-icon fa fa-paper-plane-o bigger-130"></i></a></div>';
                } else {
                    return styleb + '<a class="blue openanswer" href="#" data-toggle="modal" data-target="#openanswer1" id="' + full['id'] + '"><i class="ace-icon fa fa-envelope-o bigger-130"></i></a><a class="green answer" href="#" data-toggle="modal" data-target="#answer" id="' + full['id'] + '"><i class="ace-icon fa fa-paper-plane-o bigger-130"></i></a></div>';
                }
            }
        }],
        ajax: {
            "url": "sms/inbox",
            "type": "POST",
            "cache": false,
            "dataSrc": ""
        },
        "createdRow": function(row, data, index) {
            //console.log(data['open']);
            if (data['open'] == 0) {

                $(row).addClass('info');
            }
        },
        columns: [

            {
                data: 'client'
            },
            {
                data: 'phone'
            },
            {
                data: 'received_date'
            },
            {
                data: 'message'
            },
            {
                data: 'gateway'
            }

        ]
    });
    //fin de talba sms recibidos
    //


    //tabla mostrar mensajes enviados pro grupo

    var Greload = $('#groupsend-table').DataTable({
        "oLanguage": {
            "sUrl": Lang.app.datatables
        },
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        destroy: true,
        buttons: [
            'excel', 'csv'
        ],
        "columnDefs": [{
            "targets": 2,
            "render": function(data, type, full) {

                switch (full['status']) {

                    case 'ok':
                        return '<span class="label label-success arrowed">Enviado</span>';
                        break;

                    case 'pe':
                        return '<span class="label label-info arrowed">Pendiente</span>';
                        break;

                    case 'wa':
                        return '<span class="label label-warning arrowed">En proceso</span>';
                        break;

                    case 'er':
                        return '<span class="label label-danger arrowed">Error</span>';
                        break;

                }

            }
        }],
        ajax: {
            "url": "sms/listgroup",
            "type": "POST",
            "cache": false,
            "data": function(d) {
                d.extra_search = $('#val').val();
            },
            "dataSrc": ""
        },
        columns: [

            {
                data: 'name'
            },
            {
                data: 'phone'
            }

        ]
    });
    //fin de la tabla mostrar mensajes enviados por grupo

    $(document).on("click", ".infor", function(event) {
        event.stopImmediatePropagation();
        var idr = $(this).attr("id");
        $('#val').val(idr);

        Greload.ajax.reload();

    });


    function ajaxchecksms() {
        $.ajax({
            "url": "crncl32jd92t",
            "type": "POST",
            "data": {},
            "dataType": "json",
            statusCode: {
                200: function() {
                    Oreload.ajax.reload();
                }
            }
        });
        //metodo para
    }

    ajaxchecksms();


    $('#Stem,#btnpreview,#lsclient,#seltype,#sltemplate').hide();
    $('.pro').hide();

    function loadtem(ttem) {
        $.ajax({
            "url": "templates/listtem",
            "type": "POST",
            "data": {
                "tem": ttem
            },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            $('#type_temp').empty();

            if (data.msg == 'notemplates') {
                msg('No se encontraron plantillas.', 'error');
                $('#type_temp').append($('<option>').text('No se encontraron plantillas').attr('value', 'none').prop('selected', true));
                $('#btnpreview').hide('fast');
                return false;
            }


            $('#type_temp').append($('<option>').text('No usar plantilla').attr('value', 'none').prop('selected', true));
            let language;
            $.each(data, function(i, val) {
                $('#type_temp').append($('<option>').text(val['name']).attr('value', val.id));
            });
            if ($('[name="msfrom"]').val() != 4) {
                $('#showtext').show('fast');
            }
        });
    }

    //get routers
    $.ajax({
        "url": "client/getclient/routers",
        "type": "POST",
        "data": {},
        "dataType": "json",
        'error': function(xhr, ajaxOptions, thrownError) {
            debug(xhr, thrownError);
        }
    }).done(function(data) {
        if (data.msg == 'norouters')
            msg('No se encontraron routers.', 'error');
        else {
            $('#slcrouter').append($('<option>').text('Seleccione Router').attr('value', 'none').prop('selected', true));
            $.each(data, function(i, val) {
                $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
            });
        }
    });


    //funcion para cargar datos al responder o abrir sms

    function getanswer(ida, modal) {
        $.ajax({
            "url": "sms/getinfo/answersms",
            "type": "POST",
            "data": {
                id: ida
            },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            if (data.msg == 'nosms')
                msg('No se encontro el sms.', 'error');
            else {
                if (modal == 'openanswer') {
                    $('#anscliopen').text(data.client);
                    $('#ansphopen').text(data.phone);
                    $('#ansmessaopen').text(data.message);
                    $('#Blockansdate').html('<b>' + data.date_in + '<b>');

                } else {
                    $('#id').val(data.id);
                    $('#answerclient').text(data.client);
                    $('#answerphone').val(data.phone);
                    $('#Blockmessage').html(data.message + '<p><b>' + data.date_in + '</b></p>');
                }

                Ireload.ajax.reload();
            }
        });
    }

    $(document).on("click", ".answer", function(event) {
        event.stopImmediatePropagation();
        var ida = $(this).attr("id");
        getanswer(ida, 'answer');
    });

    $(document).on("click", ".openanswer", function(event) {
        event.stopImmediatePropagation();
        var ida = $(this).attr("id");
        getanswer(ida, 'openanswer');

    });

    //funcion para enviar respuesta a un mensaje
    $(document).on("click", "#btnSendanswer", function(event) {
        event.stopImmediatePropagation();
        var data = $('#formanswer').serialize();
        $('#answer').modal('toggle');

        startloading('body', 'Enviando sms...');
        var $btn = $(this).button('loading');

        $.ajax({
            "url": "sms/sendanswer",
            "type": "POST",
            "data": data,
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            if (data.msg == 'success') {
                $('body').loadingModal('destroy');
                msg('El sms fue enviado.', 'success');
            } else if (data.msg == 'errorPhone') {
                $('body').loadingModal('destroy');
                msg('No es posible enviar el sms, no se tiene acceso al número del remitente.', 'error');
            } else if (data.msg == 'errorConnect') {
                $('body').loadingModal('destroy');
                msg('No es posible acceder al router, verifique que este en línea.', 'error');
            } else {
                //Mesajes personalizados
                if (data.msg == 'error') {
                    var arr = data.errors;
                    $.each(arr, function(index, value) {
                        if (value.length != 0) {
                            msg(value, 'error');
                        }
                    });
                }
                //fin de mensajes personalizados
            }
            //restore button
            $btn.button('reset');
        });

    });

    $('[name="msfrom"]').change(function() {
        $('[name="router_id"]').trigger('change');
        if ($(this).val() == 4) {
            $('#showtext').hide();
        }
    });
    //Llenar con clientes segun router seleccionado
    $(document).on("change", "#slcrouter", function(event) {
        event.stopImmediatePropagation();
        var idr = $('#slcrouter').val();

        if (idr == 'none') {
            $('#lsclient').hide('fast');
            $('#ms').empty();
            $('#ms').multiselect('destroy');
            return false;
        }
        //recupermos el template elegido
        $.ajax({
            type: "POST",
            url: "clients/advice/clients",
            data: {
                "idr": idr
            },
            dataType: "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            if (data.msg == 'noclients') {
                if (idr == 'none') {
                    $('#lsclient').hide('fast');
                    $('#sltemplate,#tpsend').hide('fast');
                } else
                    msg('No se encontraron clientes en el router.', 'error');
            } else {


                var typ = 'sms';

                if ($('[name="msfrom"]').val() == 4) {
                    typ = 'whatsapp';
                }
                if (typ == 'none') {
                    $('#sltemplate,#tpsend').hide('fast');

                    $('#type_temp').empty();
                    return false;
                }

                if (typ == 'sms') {

                    //verificamos realizo la configuración de sms
                    $.ajax({
                        "url": "config/getconfig/sms",
                        "type": "POST",
                        "data": {},
                        "dataType": "json"
                    }).done(function(sms) {
                        if (sms.status) {
                            $('#btnSend').attr('disabled', true);
                            msg('No Twilo SMS, Whatsapp and Waboxapp configured to send SMS', 'system');
                        } else {
                            $('#btnSend').attr('disabled', false);
                        }
                    });

                }

                loadtem(typ);
                $('#sltemplate,#tpsend').show('fast');


                $('#ms').empty();
                $('#ms').multiselect('destroy');
                $('#seltype').show('fast');

                $cl = $.each(data, function(i, val) {
                    $('#ms').append($('<option>').text(val.name).attr('value', val.id));
                });

                $.when($cl).done(function() {
                    $('#lsclient').show();
                    $('#name_adv').focus();
                    $('#ms').multiselect({
                        buttonWidth: '468px',
                        dropRight: true,
                        maxHeight: 230,
                        includeSelectAllOption: true,
                        selectAllText: 'Seleccionar todos',
                        allSelectedText: 'Selecciono todos los clientes',
                        nonSelectedText: 'No selecciono clientes',
                        filterPlaceholder: 'Buscar',
                        nSelectedText: 'Seleccionados',
                        enableFiltering: true,
                        buttonClass: 'btn btn-white btn-primary',
                        templates: {
                            button: '<button type="button" class="multiselect dropdown-toggle" data-toggle="dropdown"><span class="multiselect-selected-text"></span>  <b class="fa fa-caret-down"></b></button>',
                            ul: '<ul class="multiselect-container dropdown-menu"></ul>',
                            filter: '<li class="multiselect-item filter"><div class="input-group"><span class="input-group-addon"><i class="fa fa-search"></i></span><input class="form-control multiselect-search" type="text"></div></li>',
                            filterClearBtn: '<span class="input-group-btn"><button class="btn btn-default btn-white btn-grey multiselect-clear-filter" type="button"><i class="fa fa-times-circle red2"></i></button></span>',
                            li: '<li><a tabindex="0"><label></label></a></li>',
                            divider: '<li class="multiselect-item divider"></li>',
                            liGroup: '<li class="multiselect-item multiselect-group"><label></label></li>'
                        }
                    });
                });
            }
        });
        //fin de recuperar
    });


    $(document).on("click", "#new", function(event) {

        $('#sendnewadv')[0].reset();
        $('#Stem,#btnpreview,#lsclient,#sltemplate,#sltemplate,#showtext').hide();

    });

    //funcion para mostrar el boton vista previa

    $(document).on("change", "#type_temp", function(event) {
        event.stopImmediatePropagation();
        var idt = $('#type_temp').val();

        if (idt != 'none') {
            $('#btnpreview').attr('href', 'tempview?id=' + idt).show('fast');
            $('#tpsend').show('fast');
            $('#showtext').hide('fast');
            $('#lsclient').show('fast');
        } else {
            $('#btnpreview').hide('fast');
            $('#showtext').show('fast');
        }
        //fin de recuperar
    });


    // recargar tabla
    $(document).on("click", ".recargar", function(event) {
        event.stopImmediatePropagation();
        Oreload.ajax.reload();
        Ireload.ajax.reload();
    });


    //funcion principal para enviar y guardar avisos
    $(document).on("click", "#btnSend", function(event) {
        event.stopImmediatePropagation();
        if ($('#type_temp').val() == "none") {
            msg('Seleccione al menos un Template.', 'error');
            return;
        }

        if ($('#ms').val() == null) {
            msg('Seleccione al menos un cliente.', 'error');
            return;
        }

        $('#add').modal('toggle');
        startloading('body', 'Enviando sms...');
        var $btn = $(this).button('loading');

        $.ajax({
                url: 'sms/send',
                type: 'POST',
                dataType: 'json',
                data: $('#sendnewadv').serialize(),
                error: function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                    $('body').loadingModal('destroy');
                }
            })
            .done(function(data) {
 
                if (data.msg == 'success' || data.msg == 'process') {

                    //invocamos al methodo de envio de sms
                    $.ajax({
                        url: "crncl32jd92t",
                        type: "POST",
                        data: {},
                        dataType: "json",
                        error: function(xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function(data) {

                        if (data.result == 'success') {
                            $('body').loadingModal('destroy');
                            Oreload.ajax.reload();
                            msg('El sms fue enviado correctamente.', 'success');

                        } else {

                            $('body').loadingModal('destroy');
                            msg('No se puedo enviar el sms.', 'error');

                        }

                    });

                } else if (data.msg == 'errorConnect') {
                    $('body').loadingModal('destroy');
                    msg('No es posible acceder al router, verifique que este en línea.', 'error');
                } else if (data.msg == 'errorSend') {
                    $('body').loadingModal('destroy');
                    msg('No se puedo enviar el sms.', 'error');
                } else {
                    $('body').loadingModal('destroy');
                    msg('No es posible iniciar sesión en el router, verifique los datos de acceso.', 'error');

                }

                //restore button
                $btn.button('reset');

            });

    });


    //eliminar registro
    $(document).on("click", '.del', function(event) {
        event.stopImmediatePropagation();
        var idr = $(this).attr("id");
        bootbox.confirm("¿ Esta seguro de eliminar ?", function(result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "sms/delete",
                    data: {
                        "id": idr
                    },
                    dataType: "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {

                    if (data.msg == 'notfound')
                        msg('No se encontro el sms en la BD.', 'error');
                    if (data.msg == 'success') {
                        msg('El sms fue eliminado.', 'success');
                        Oreload.ajax.reload();


                    }
                });
            }
        });
    });

    //reenviar sms
    $(document).on("click", '.reenviar', function(event) {
        event.stopImmediatePropagation();
        var idr = $(this).attr("id");
        bootbox.confirm("¿ Esta seguro de reenviar ?", function(result) {
            if (result) {

                startloading('body', 'Reenviando sms...');

                $.ajax({
                    type: "POST",
                    url: "sms/forward",
                    data: {
                        "id": idr
                    },
                    dataType: "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        $('body').loadingModal('destroy');
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {

                    if (data.msg == 'notfound') {
                        $('body').loadingModal('destroy');
                        msg('No se encontro el sms en la BD.', 'error');
                    }
                    if (data.msg == 'success') {
                        //invocamos al methodo de envio de sms
                        $.ajax({
                            url: "crncl32jd92t",
                            type: "POST",
                            data: {},
                            dataType: "json",
                            error: function(xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function(data) {

                            if (data.result == 'success') {

                                $('body').loadingModal('destroy');
                                Oreload.ajax.reload();
                                msg('El sms fue reenviado.', 'success');

                            } else {
                                $('body').loadingModal('destroy');
                                msg('No se puedo enviar el sms.', 'error');

                            }

                        });

                    }
                });
            }
        });
    });


    // other plugins
    var $remaining = $('#remaining'),
        $messages = $remaining.next();

    $('#message').keyup(function() {
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);

        $remaining.text(remaining + ' caracteres restantes');
        $messages.text(messages + ' mensaje');
    });

    $('.reload-whatsapp-chat').click(function() {
        $('#whatsapp-chat-tab').find('[name="select_client"]').trigger('change');
    });

    $(document).on("click", "#sendCustomWhatsappMessage", function (event) {
        event.stopImmediatePropagation();

        startloading('body', 'Enviando sms...');
        var $btn = $(this).button('loading');

        const formElement = $('#reply_mes');
        const phn = formElement.find('input[name="client_phn"]').val()
        const formData = {
            message: formElement.find('[name="message"]').val(),
            clients: [formElement.find('input[name="select_client"]').val()],
            msfrom: 4,
            template: 'none',
            router_id: 0
        };

        $.ajax({
            url: 'sms/send',
            type: 'POST',
            dataType: 'json',
            data: formData,
            error: function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
                $('body').loadingModal('destroy');
            }
        })
            .done(function (data) {

                if (data.msg == 'success' || data.msg == 'process') {

                    //invocamos al methodo de envio de sms
                    $.ajax({
                        url: "crncl32jd92t",
                        type: "POST",
                        data: {},
                        dataType: "json",
                        error: function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {

                        if (data.result == 'success') {
                            $('body').loadingModal('destroy');
                            Oreload.ajax.reload();
                            msg('El sms fue enviado correctamente.', 'success');

                        } else {

                            $('body').loadingModal('destroy');
                            msg('No se puedo enviar el sms.', 'error');

                        }

                    });

                } else if (data.msg == 'errorConnect') {
                    $('body').loadingModal('destroy');
                    msg('No es posible acceder al router, verifique que este en línea.', 'error');
                } else if (data.msg == 'errorSend') {
                    $('body').loadingModal('destroy');
                    msg('No se puedo enviar el sms.', 'error');
                } else {
                    $('body').loadingModal('destroy');
                    msg('No es posible iniciar sesión en el router, verifique los datos de acceso.', 'error');

                }

                //restore button
                $btn.button('reset');
                chatdiv(phn);
                formElement.find('[name="message"]').val('');
            });

    });
    //fin del ready

    var hash= window.location.hash;
    if(hash.length > 0 ) {
        $('a[role="tab"]').parent().removeClass('active');
        $('a[href="'+hash+'"]').parent().addClass('active');
        $('.tab-pane').removeClass('active');
        $(hash).addClass('active');
     }
});

function timeSince(date) {

    var seconds = Math.floor((new Date() - date) / 1000);

    var interval = seconds / 31536000;

    if (interval > 1) {
        return Math.floor(interval) + " years";
    }
    interval = seconds / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + " months";
    }
    interval = seconds / 86400;
    if (interval > 1) {
        return Math.floor(interval) + " days";
    }
    interval = seconds / 3600;
    if (interval > 1) {
        return Math.floor(interval) + " hours";
    }
    interval = seconds / 60;
    if (interval > 1) {
        return Math.floor(interval) + " minutes";
    }
    return Math.floor(seconds) + " seconds";
}

function image(){
    var x = document.getElementById("reply_mes");
    var y = document.getElementById("row_top");
    x.style.display =  "block";
    y.style.display =  "none";
}
function imagehide(){
    var x = document.getElementById("reply_mes");
    var y = document.getElementById("row_top");
    x.style.display =  "none";
    y.style.display =  "block";
}

function chatdiv(phone) {

    let client_chat_window = '',
        type_class, time_since;
    $.ajax({
        type: "POST",
        url: "sms/client-whatsapp-chat",
        data: {
            "client_number": phone
        },
        dataType: "json",
        'error': function(xhr, ajaxOptions, thrownError) {
            $('body').loadingModal('destroy');
            debug(xhr, thrownError);
        }
    }).done(function(data) {
       
        var filter = {type: '2'};
        received_msg = data.filter(obj => obj.type == filter.type)
        cli = received_msg.slice(-1).pop()
        cli_type = cli.type;
        const then = new Date(cli.received_at);
        const now = new Date();
        const msBetweenDates = Math.abs(then - now.getTime());
        const hoursBetweenDates = msBetweenDates / (60 * 60 * 1000);
        var client = data[0].client;
        var phn = data[0].phone;
        $.ajax({
            type: "POST",
            url: "sms/msg-status",
            data: {
                "client_phn": phn
            },
            dataType: "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                $('body').loadingModal('destroy');
                // debug(xhr, thrownError);
            }
        }).done(function(data_sec){
            $("#row_top").load(location.href + " #row_top");
            if(data_sec == 1){
            $('#tickets,#routers,#notifier,#errRouter,#infolicence').hide();
            }
        });

        $.ajax({
            type: "POST",
            url: "sms/ph-to-id",
            data: {
                "client_number": phn
            },
            dataType: "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                $('body').loadingModal('destroy');
                // debug(xhr, thrownError);
            }
        }).done(function(data_sec){
            var clid = data_sec[0].id
            var client_name = data_sec[0].name
      
        var e = $(
            '<style>'+
            ' @media only screen and (max-width: 600px) {.row_top {display:none;} .py-5 {display: block;}}'+
            '</style>'+
            '<input name="select_client" value='+ clid +' type="hidden" class="form-control">' +
        '<input name="client_phn" value='+ phn +' type="hidden" class="form-control">' +
        '<div class="heading">'+
        '<div class="col-sm-2 col-md-1 col-xs-3 heading-avatar">' +
        '<div class="heading-avatar-icon">' +
        '<i onclick="imagehide()" class="fa fa-arrow-left back-arr" style="" aria-hidden="true"></i>'+
        '<img src="https://demo.smartisp.us/assets/images/Admin.png">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-8 col-xs-7 heading-name">' +
        '<a class="heading-name-meta">'+ client_name +'' +
        '</a>' +
        '</div>' +
        '</div>' +
        '<div class="row_top1" id="row_top1" style="height: 458px;overflow-x: hidden; overflow-y: auto; text-align: center; flex: 1;">' +
        '<div class="col-md-12" style="position: relative;padding-bottom: 50px;">' +
        '<div class="list-unstyled">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="reply" style="bottom: 15px; height: 58px;">' +
        '<div class="col-sm-9 col-xs-9 reply-main" style="width:90%;">' +
        '<textarea class="form-control" rows="1"  name="message" id="comment"></textarea>' +
        '</div>' +
        '<div class="col-sm-1 col-xs-1 reply-send">' +
        '<i class="fa fa-send fa-2x"  id="sendCustomWhatsappMessage" aria-hidden="true"></i>' +
        '</div>' +
        '</div>'
        );
        var f = $(
            '<style>'+
            ' @media only screen and (max-width: 600px) {.row_top {display:none;} .py-5 {display: block;}}'+
            '</style>'+
            '<input name="select_client" value='+ clid +' type="hidden" class="form-control">' +
        '<input name="client_phn" value='+ phn +' type="hidden" class="form-control">' +
        '<div class="heading">'+
        '<div class="col-sm-2 col-md-1 col-xs-3 heading-avatar">' +
        '<div class="heading-avatar-icon">' +
        '<i onclick="imagehide()" class="fa fa-arrow-left back-arr" style="" aria-hidden="true"></i>'+
        '<img src="https://demo.smartisp.us/assets/images/Admin.png">' +
        '</div>' +
        '</div>' +
        '<div class="col-sm-8 col-xs-7 heading-name">' +
        '<a class="heading-name-meta">'+ client_name +'' +
        '</a>' +
        '</div>' +
        '</div>' +
        '<div class="row_top1" id="row_top1" style="height: 458px;overflow-x: hidden; overflow-y: auto; text-align: center; flex: 1;">' +
        '<div class="col-md-12" style="position: relative;padding-bottom: 50px;">' +
        '<div class="list-unstyled">' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<div class="reply" style="bottom: 15px; height: 58px;">' +
        '<div class="col-sm-9 col-xs-9 reply-main" style="width:90%;">' +
        '<p style="font-weight: bold;" >THIS USER DID NOT RESPOND SINCE LAST 24 HOURS - <a href="#add" data-toggle="modal" data-target="#add" id="new">CLICK HERE TO SEND A TEMPLATE MSG NOW</a></p>'+
        '</div>' +
        '</div>'
        );

        if (hoursBetweenDates < 24 ) {
            $('#reply_mes').empty().append(e); 
            // console.log('date is within 24 hours');
        } else {
            $('#reply_mes').empty().append(f);
        //   console.log('date is NOT within 24 hours');
        }


        if (data.length == '0') {
            $('#whatsapp-chat-window').hide();
            $('#whatsapp-chat-window').find('div.list-unstyled').html();
        } else {
            $.each(data, function(index, item) {

                type_class = item.type == 1 ? 'sent-card' : 'received-card';
                time_since = item.type == 1 ? timeSince(new Date(item.send_date)) : timeSince(new Date(item.received_at))
                sender = item.type == 1 ? '('+item.sender_id+')' : '';
                client_chat_window += `
                <div class="clearfix ${type_class}">
                    <div class="card">
                    <div class="card-body">
                        <p class="message" style="margin: 0px 0 4px;">
                        ${item.message}
                        <p class="sender_name text-muted small" style="text-align: initial;">
                        ${sender}
                        </p>
                        </p>
                        <p class="text-muted small" style="text-align:end;"><i class="fa fa-clock-o"></i> ${time_since} ago</p>
                    </div>
                    </div>
                </div>
                `;
            });
            $('#reply_mes').show();
            $('#reply_mes').find('div.list-unstyled').html(client_chat_window);
            var mydiv = $("#row_top1");
            mydiv.scrollTop(mydiv.prop("scrollHeight"));
            if (window.matchMedia('(max-width: 600px)').matches)
{
    image();}

        }
    });
});
}

function whatsappsearch() {
    var input = document.getElementById("searchText");
    var filter = input.value.toLowerCase();
    var nodes = document.getElementsByClassName('sideBar');

    for (i = 0; i < nodes.length; i++) {
        if (nodes[i].innerText.toLowerCase().includes(filter)) {
            nodes[i].style.display = "block";
        } else {
            nodes[i].style.display = "none";
        }
    }
}