// Clients Core - Funciones principales JQuery para planes
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

// fin de tabla planes Simple Queues

// recargar tabla planes simple queues
    $(document).on("click", '.recargar', function (event) {
        event.stopImmediatePropagation();
        window.LaravelDataTables["plan-table"].draw();

    });

    $(document).on("click", '.sb', function (event) {
        event.stopImmediatePropagation();
        var idp = $(this).attr('id');
        $('[name=plan_id_sb]').val(idp);
        //recover info for configuration plan

        $.ajax({
            type: "POST",
            url: "sb/getinfo/data",
            data: {idp: idp},
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.success) {
                var myText = data.mode;

                var days = [
                    {
                        id: 'Mon',
                        text: 'Lunes'

                    },
                    {
                        id: 'Tue',
                        text: 'Marters'

                    },
                    {
                        id: 'Wed',
                        text: 'Miercoles'

                    },
                    {
                        id: 'Thu',
                        text: 'Jueves'

                    },
                    {
                        id: 'Fri',
                        text: 'Viernes'

                    },
                    {
                        id: 'Sat',
                        text: 'Sabado'

                    },
                    {
                        id: 'Sun',
                        text: 'Domingo'

                    }

                ];

                var $exampleMulti = $(".select2").select2({
                    data: days,
                    allowClear: true,
                    tags: true,
                    width: '266px'
                });

                if (myText == 'd') {
                    $('#swdays').hide('fast');
                }
                if (myText == 'w') {
                    $('#swdays').show('fast');
                }

                if (data.for_all == 1)
                    $('#allplans').prop('checked', 'true');
                else
                    $('#allplans').removeAttr('checked');

                $exampleMulti.val(data.days).trigger("change");

                $("#act_ser").children().filter(function () {
                    return $(this).val() == myText;
                }).prop('selected', true);


                $('.timepicker').timepicker('setTime', data.star_time);
                $('.timepicker2').timepicker('setTime', data.end_time);
                $('#formsb input[name="speedx"]').val(data.bandwidth);

            } else {
                msg('No pudo cargar la información de la base de datos', 'error');
            }
        });
        //end ajax
        $('#smartb').modal('show');
    });

    //eliminar plan
    $(document).on("click", '.del', function (event) {
        event.stopImmediatePropagation();
        var idp = $(this).attr("id");
        bootbox.confirm("¿ Esta seguro de eliminar el plan ? este no debe tener clientes asociados.", function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "plans/delete",
                    data: {"id": idp},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {
                    if (data.msg == 'errorclient')
                        msg('No se puede eliminar el plan, existen clientes asociados.', 'error');
                    if (data.msg == 'error')
                        msg('No se encontro el plan.', 'error');
                    if (data.msg == 'success') {
                        msg('El plan fue eliminado.', 'success');
                        window.LaravelDataTables["plan-table"].draw();
                    }
                });
            }
        });
    });

    //añadir plan
    $(document).on("click", "#addbtnplan", function (event) {
        event.stopImmediatePropagation();
        var routerdata = $('#formaddplan').serialize();
        // var $btn = $(this).button('loading');

        $.easyAjax({
            type: "POST",
            url: "plans/create",
            data: routerdata,
            container: '#formaddplan',
            success: (function (data) {
                console.log('hello ');
                //Mesajes personalizados
                // if (data.msg == 'error') {
                //     var arr = data.errors;
                //     $.each(arr, function (index, value) {
                //         if (value.length != 0) {
                //             msg(value, 'error');
                //         }
                //     });
                // }

                if (data.msg == 'errorDownload')
                    msg('EL campo descarga no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');
                if (data.msg == 'errorUpload')
                    msg('EL campo subida no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');

                if (data.msg == 'success') {
                    $('#add').modal('toggle');
                    $('#formaddplan')[0].reset();//reseteamos el formulario
                    msg('El plan fue añadido correctamente.', 'success');
                    window.LaravelDataTables["plan-table"].draw();
                }
                //
                // //restore button
                // $btn.button('reset');
            })
        })
    });

//mostrar cursor en cuadro de texto una vez cargado el modal
    $('#add').on('shown.bs.modal', function () {
        $('#namepl').focus()
    });

//limpiar o copiar formulario
    $('.peref').click(function () {
        // verificamos is esta copiando el formulario
        if (!$('#copy').is(':checked')) {
            //reseteamos el formulario
            $('#formaddplan')[0].reset();
        }
    });

//fin de limpiar o copiar formulario
    $('#swdays').hide();

    $(document).on('change', '#act_ser', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */

        if ($(this).val() == 'd') {
            $('#swdays').hide('fast');

        }
        if ($(this).val() == 'w') {
            $('#swdays').show('fast');
        }
    });

// guardar editar plan
    $(document).on("click", "#editbtnplan", function (event) {
        event.stopImmediatePropagation();
        var plandata = $('#PlanformEdit').serialize();
        // var $btn = $(this).button('loading');
        //
        // startloading('body', 'Guardando...');

        $.easyAjax({
            type: "POST",
            url: "plans/update",
            data: plandata,
            container: "#PlanformEdit",
            success: function (data) {

                if (data.msg == 'errorConnect') {
                    // $('body').loadingModal('destroy');
                    $('#edit').modal('toggle');
                    msg('Se produjo un error no se tiene acceso al router, verifique los datos de autentificación, además si esta encendido y conectado a la red.', 'error');
                }

                if (data.msg == 'errorDownload') {
                    // $('body').loadingModal('destroy');
                    msg('EL campo descarga no es válido, la velocidad debe estar en kilobytes ejemplos: 512, 1000', 'error');
                }

                if (data.msg == 'errorUpload') {
                    // $('body').loadingModal('destroy');
                    msg('EL campo subida no es válido, la velocidad debe estar en Kilobytes ejemplos: 512, 1000', 'error');
                }

                if (data.msg == 'success') {
                    // $('body').loadingModal('destroy');
                    msg('El Plan fue actualizado correctamente.', 'info');
                    $('#edit').modal('toggle');
                    window.LaravelDataTables["plan-table"].draw();
                }
                //restore button
                // $btn.button('reset');
            }
        })
    });
//fin guardar editar plan


// guardar editar smart bandwidth
    $(document).on("click", "#editbtnsb", function (event) {
        event.stopImmediatePropagation();
        var plandata = $('#formsb').serialize();
        var $btn = $(this).button('loading');

        startloading('body', 'Guardando...');

        $.ajax({
            type: "POST",
            url: "smartbandwidth/update",
            data: plandata,
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {

            //Mesajes personalizados
            if (data.msg == 'error') {

                $('body').loadingModal('destroy');

                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
                //restore button
                $btn.button('reset');
            }

            if (data.msg == 'success') {
                $('body').loadingModal('destroy');
                msg('La configuracion fue actualizada correctamente.', 'info');
                $('#smartb').modal('toggle');

                //restore button
                $btn.button('reset');
            }

            if (data.msg == 'emptyDays') {
                $('body').loadingModal('destroy');
                msg('Debe seleccionar al menos un día de la semana.', 'error');
                //restore button
                $btn.button('reset');
            }

            window.LaravelDataTables["plan-table"].draw();

        });
    });

//get editar plan
    $(document).on("click", '.editar', function (event) {
        event.stopImmediatePropagation();
        $('[name=plan]').val($(this).attr('id'));
        $('#winedit').waiting({fixed: true});
        var fdata = $('#val').serialize();
        $('#load2').show();

        $('#PlanformEdit').find(".has-error").each(function () {
            $(this).find(".help-block").text("");
            $(this).removeClass("has-error");
        });

        $.ajax({
            type: "POST",
            url: "plan/getplan/data",
            data: fdata,
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.success) {

                var myText = data.priority;

                $("#editpriority").children().filter(function () {
                    return $(this).val() == myText;
                }).prop('selected', true);

                $('#PlanformEdit input[name="plan_id"]').val(data.id);
                $('#PlanformEdit input[name="edit_name"]').val(data.name);
                $('#PlanformEdit input[name="edit_title"]').val(data.title);
                $('#PlanformEdit input[name="edit_download"]').val(data.download);
                $('#PlanformEdit input[name="edit_upload"]').val(data.upload);
                $('#PlanformEdit input[name="edit_cost"]').val(data.price);
                $('#PlanformEdit input[name="edit_iva"]').val(data.iva);
                //advanced
                $('#PlanformEdit input[name="edit_aggregation"]').val(data.aggregation);
                $('#PlanformEdit input[name="edit_limitat"]').val(data.limitat);
                $('#PlanformEdit input[name="edit_bl"]').val(data.bl);
                $('#PlanformEdit input[name="edit_bth"]').val(data.bth);
                $('#PlanformEdit input[name="edit_bt"]').val(data.bt);
                // $('#PlanformEdit input[name="address_list_name"]').val(data.address_list_name);

                if(data.no_rules == "1") {
                    $('#PlanformEdit input[name="no_rules"]').attr('checked', 'checked');
                }

                $('#winedit').waiting('done');
                $('#load2').hide();
            } else {
                $('#load2').hide();
                msg('No pudo cargar la información de la base de datos', 'error');
            }
        });

    }); // fin de editar


//aditional plugins
    $('.download,.upload').mask('YYYYYYY', {
        'translation': {

            Y: {pattern: /[0-9]/}
        }
    });

    $('.enteros').mask('YYYYYYYYYS', {
        'translation': {

            Y: {pattern: /[0-9.]/, optional: true}
        }
    });

    $(".timepicker").timepicker({
        showInputs: false,
        minuteStep: 15,
        showMeridian: false,
    });

    $(".timepicker2").timepicker({
        showInputs: false,
        minuteStep: 15,
        showMeridian: false,
    });

//end aditional plugins
//fin del ready
});
