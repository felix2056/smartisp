// users Core - Funciones principales JQuery para usuarios
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
            "url": baseUrl+"/config/getconfig/debug",
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
//other plugins

    $('#timepicker1').timepicker({
        minuteStep: 1,
        showSeconds: true,
        showMeridian: false

    }).next().on(ace.click_event, function () {
        $(this).prev().focus();
    });

//fin de talba templates
    $('#Stem,#btnpreview,#lsclient,#seltype,#sltemplate,#timesh').hide();

//cargamos todos los templates

    function loadtem(ttem) {
        $.ajax({
            "url": baseUrl+"/templates/listtem",
            "type": "POST",
            "data": {"tem": ttem},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            $('#type_temp').empty();

            if (data.msg == 'notemplates') {
                msg(Lang.messages.notemplatesfound, 'error');
                $('#type_temp').append($('<option>').text(Lang.messages.notemplatesfound).attr('value', 'none').prop('selected', true));
                return false;
            }

            $('#type_temp').append($('<option>').text('Seleccione plantilla').attr('value', 'none').prop('selected', true));
            $.each(data, function (i, val) {
                $('#type_temp').append($('<option>').text(val['name']).attr('value', val.id));
            });
        });
    }

//fin de cargar

//get routers
    $.ajax({
        "url": baseUrl+"/client/getclient/routers",
        "type": "POST",
        "data": {},
        "dataType": "json",
        'error': function (xhr, ajaxOptions, thrownError) {
            debug(xhr, thrownError);
        }
    }).done(function (data) {
        if (data.msg == 'norouters')
            msg(Lang.messages.noroutersfound, 'error');
        else {
            $('#slcrouter').append($('<option>').text(Lang.messages.selectrouter).attr('value', 'none').prop('selected', true));
            $.each(data, function (i, val) {
                $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
            });
        }
    });


// recargar tabla
    $(document).on("click", ".recargar", function (event) {
        window.LaravelDataTables["advice-table"].draw();
    });

//fin de guardar template

//eliminar aviso
    $(document).on("click", '.del', function (event) {
        var ida = $(this).attr("id");
        bootbox.confirm(Lang.messages.areyousureyouwanttodeleteemail, function (result) {
            if (result) {

                //mostramos mensaje
                startloading('body', Lang.messages.deletingemail);

                $.ajax({
                    type: "POST",
                    url: "advice/delete",
                    data: {"id": ida},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {
                    if (data.msg == 'errortemp') {
                        $('body').loadingModal('destroy');
                        msg(Lang.messages.unabletodeleteemail, 'error');
                    }
                    if (data.msg == 'error') {
                        $('body').loadingModal('destroy');
                        msg(Lang.messages.emailnotfound, 'error');
                    }
                    if (data.msg == 'success') {
                        $('body').loadingModal('destroy');
                        msg(Lang.messages.theemailwasdeleted, 'success');
                        window.LaravelDataTables["advice-table"].draw();
                        $('.pro').hide('fast');
                    }
                });
            }
        });
    });


//fin de la funcion enviar y guardar aviso

//Llenar con clientes segun router seleccionado
    $(document).on("change", "#slcrouter", function (event) {
        event.preventDefault();
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
            url: "advice/clients",
            data: {"idr": idr},
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {

            if (data.msg == 'noclients') {
                if (idr == 'none') {
                    $('#lsclient').hide('fast');
                    $('#sltemplate,#tpsend').hide('fast');
                } else
                    msg(Lang.messages.noclientswerefoundontherouter, 'error');
            } else {

                var typ = $('#slctype').val();
                if (typ == 'none') {
                    $('#sltemplate,#tpsend').hide('fast');
                    $('#type_temp').empty();
                    return false;
                }

                if (typ == 'email') {

                    //verificamos si cambio la configuración de email por defecto del sistema
                    $.ajax({
                        "url": baseUrl+"/config/getconfig/email",
                        "type": "POST",
                        "data": {},
                        "dataType": "json"
                    }).done(function (email) {
                        if (email.status) {
                            $('#btnSend').attr('disabled', true);
                            msg(Lang.messages.iDoNotconfigurethesystemsmtpemail, 'system');
                        } else {
                            $('#timesh').hide();
                            $('#btnSend').attr('disabled', false);
                        }
                    });

                }

                loadtem(typ);
                $('#sltemplate,#tpsend').show('fast');


                $('#ms').empty();
                $('#ms').multiselect('destroy');
                $('#seltype').show('fast');
                $cl = $.each(data, function (i, val) {
                    $('#ms').append($('<option>').text(val.name).attr('value', val.id));
                });

                $.when($cl).done(function () {
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


    $(document).on("click", "#new", function (event) {

        $('#sendnewadv')[0].reset();
        $('#Stem,#btnpreview,#lsclient,#seltype,#sltemplate,#sltemplate,#timesh').hide();
        $('.pro').hide();

    });

//funcion para mostrar el boton vista previa

    $(document).on("change", "#type_temp", function (event) {

        var idt = $('#type_temp').val();

        if (idt == 'none') {
            msg(Lang.messages.selectTemplate, 'error');
            $('#btnpreview,#tpsend').hide('fast');
        } else {
            var domain = window.location.origin;
            $('#btnpreview').attr('href', domain+'/'+'tempview?id=' + idt).show('fast');
            $('#tpsend').show('fast');
        }
        //fin de recuperar
    });

//funcion principal para enviar y guardar avisos
    $(document).on("click", "#btnSend", function (event) {

        if ($('#ms').val() == null) {
            msg(Lang.messages.selectAtleastOneCustomer, 'error');
            return;
        }

        $('#addadv').modal('toggle');

        startloading('body', Lang.messages.sending);

        var $btn = $(this).button('loading');

        $.ajax({
            url: 'advice/send',
            type: 'POST',
            dataType: 'json',
            data: $('#sendnewadv').serialize(),
            error: function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
                $('.pro').hide('fast');
            }
        }).done(function (data) {

            if (data.msg == 'success') {
                $('body').loadingModal('destroy');
                window.LaravelDataTables["advice-table"].draw();
                ;
                msg(Lang.messages.emailWasSentToClient, 'success');
            } else if (data.msg == 'noadv') {
                $('body').loadingModal('destroy');
                msg(Lang.messages.routerDoesNotHaveActiveEmail, 'error');

            } else if (data.msg == 'errorConnect') {
                $('body').loadingModal('destroy');
                msg(Lang.messages.itIsNotPossibleToAccess, 'error');
            } else if (data.msg == 'errorSend') {
                $('body').loadingModal('destroy');
                msg(Lang.messages.iCantSendTheEmail, 'error');
            } else {
                $('body').loadingModal('destroy');
                msg(Lang.messages.ItIsNotPossibleToLogInToRouter, 'error');
            }

            //restore button
            $btn.button('reset');

        });

    });

//fin del ready
});
