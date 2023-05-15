// Clients Core - Funciones principales JQuery para tickets cliente
jQuery(function ($) {
///// General Messages for system ///////
//Mesages for confirmatios success
    function msg(msg, type) {
        if (type == 'success') {
            var clase = 'gritter-success';
            var tit = Lang.app.registered;
            var img = '../assets/img/ok.png';
            var stincky = false;
        }
        if (type == 'error') {
            var clase = 'gritter-error';
            var tit = Lang.app.error;
            var img = '../assets/img/error.png';
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

    // recargar tablas
    $(document).on("click", '.recargar', function (event) {
        event.stopImmediatePropagation();
        window.LaravelDataTables["ticket-table"].draw()
    });

    //añadir ticket

    $("#ticketform").on('submit', (function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $('#addbtnticket').attr('disabled', true);

        $.ajax({
            "url": "tickets/create",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"
        }).done(function (data) {
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
            if (data.msg == 'success') {

                msg('Ticket creado correctamente.', 'success');

                $('#add').modal('toggle');
                $('#addbtnticket').attr('disabled', false);
                window.LaravelDataTables["ticket-table"].draw()
            }
        });
    }));

    //ocultamos el icono de loader
    $('#loads').hide();

    //mostrar cursor en cuadro de texto una vez cargado el modal
    $('#add').on('shown.bs.modal', function () {
        $('#ticketform')[0].reset();
        $('#loads').hide();
        $('#subject').focus();
        $('#addbtnticket').attr('disabled', false);
    });

    $('#addbtnticket').click(function (event) {
        $('#loads').show();
    });

    //guardar respuesta ticket
    $("#resticketform").on('submit', (function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        $.ajax({
            "url": "tickets/reply",
            "type": "POST",
            "contentType": false,
            "cache": false,
            "processData": false,
            "data": new FormData(this),
            "dataType": "json"

        }).done(function (data) {
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
            if (data.msg == 'success') {

                $('#edit').modal('toggle');
                msg('Respuesta enviada correctamente.', 'success');
                window.LaravelDataTables["ticket-table"].draw()
            }

            $('#loads').hide();
        });
    }));

    $('#accordion2').hide();

    //get responder ticket
    $(document).on("click", '.editar', function (event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        $('#menrep').val('');

        $('[name=ticket]').val($(this).attr('id'));
        $('#winedit').waiting({fixed: true});
        var fdata = $('#val').serialize();
        $('#load2').show();

        //verificamos el estado del ticket si esta abierto o cerrado
        $.ajax({
            type: "POST",
            url: "get/status",
            data: fdata,
            dataType: 'json',
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (dt) {
            if (dt.st != 'resolved') {
                $('#accordion2').show();
            } else {
                $('#accordion2').hide();
            }
        });

        $.ajax({
            type: "POST",
            url: "get/show",
            data: fdata,
            dataType: 'json',
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            $('#navticket').empty();

            $tk = $.each(data, function (i, val) {
                if (val['file'] == 'none') {
                    var file = '';
                } else {
                    var file = '<hr><p><a class="btn btn-xs btn-success" href="../assets/support_uploads/' + val['file'] + '" target="_blanck"><i class="fa fa-cloud-download"></i> Visualizar archivo</a></p>';
                }

                if (val['user'] == 'administracion' || val['user'] == 'tecnico') {
                    $('#navticket').append($('<span>').html('<div class="panel panel-info"><div class="panel-heading"><i class="fa fa-user"></i> ' + val['user'] + ' <div class="pull-right">' + val['created_at'] + '</div></div><div class="panel-body">' + val['message'] + file + '</div></div>'));
                } else {
                    $('#navticket').append($('<span>').html('<div class="panel panel-default"><div class="panel-heading"><i class="fa fa-user"></i> ' + val['user'] + ' <div class="pull-right">' + val['created_at'] + '</div></div><div class="panel-body">' + val['message'] + file + '</div></div>'));
                }
            });

            $.when($tk).done(function () {
                $('#load2').hide();
                $('#winedit').waiting('done');
                // window.LaravelDataTables["ticket-table"].draw()
            });
        });
    });

    //fin de obtener velocidad del plan seleccionado
    $('#file,#efile').ace_file_input({
        no_file: Lang.app.Selectafileonlyimages,
        btn_choose: Lang.app.select,
        btn_change: Lang.app.change,
        droppable: false,
        onchange: null,
        thumbnail: false, //| true | large
        whitelist: 'gif|png|jpg|jpeg',
        blacklist: 'exe|php'
    });
});
