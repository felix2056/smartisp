// Backups Core - Funciones principales JQuery para Copias de seguridad
jQuery(function ($) {

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

            if (deb.debug == '1')
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }

//// fin de la funcion de depuracion
//aditional config
    bootbox.setDefaults("locale", locale) //traslate bootbox


    var styleb = '<div class="hidden-sm hidden-xs action-buttons">';
    var stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
    var stylee = '<span class="red"><i class="ace-icon fa fa-trash-o bigger-120"></i></span></a></li></ul></div></div>';

//inicio de tabla lbackups
    var treload = $('#backups-table').DataTable({
        "oLanguage": {
            "sUrl": Lang.app.datatables
        },
        "order": [[1, "desc"]],
        bAutoWidth: false,
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        destroy: true,
        buttons:[],
        "columnDefs": [{
            "targets": 3,
            "render": function (data, type, full) {
                return styleb + '<a class="blue restore" href="#" id="' + full['file'] + '" title="' + Lang.app.restore + '"><i class="ace-icon fa fa-undo bigger-130"></i></a><a class="green download" href="../assets/backups/' + full['file'] + '" title="' + Lang.app.download + '"><i class="ace-icon fa fa-cloud-download bigger-130"></i></a><a class="red del" href="#" id="' + full['file'] + '" title="' + Lang.app.remove + '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>' + stylem + '<a class="restore" href="#"" id="' + full['file'] + '" title="' + Lang.app.restore + '"><span class="blue"><i class="ace-icon fa fa-undo bigger-120"></i></span></a></li><li><a href="../assets/backups/' + full['file'] + '" class="restore" title="' + Lang.app.restore + '"><span class="green"><i class="ace-icon fa fa-cloud-download bigger-120"></i></span></a></li><li><a href="#" class="del" id="' + full['file'] + '" title="' + Lang.app.remove + '">' + stylee;
            }
        }
        ],
        ajax: {
            "url": "backups/list",
            "type": "POST",
            "cache": false,
            "dataSrc": ""
        },
        columns: [
            {data: 'file'},
            {data: 'date'},
            {data: 'size'}
        ]
    });
// fin de tabla logs
// recargar tabla
    $(document).on("click", '.recargar', function (event) {
        event.stopImmediatePropagation();
        treload.ajax.reload();

    });


//funcion para crear copias de seguridad manualmente
    $(document).on('click', '#newbackup', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */

        $.ajax({
            type: "POST",
            url: "backups/create",
            data: {},
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {

            if (data.msg == 'errorcreate')
                msg(Lang.messages.Couldnotcreatedatabasebackup, 'error');
            if (data.msg == 'success') {
                msg(Lang.messages.Backupcreatedsuccessfully, 'success');
                treload.ajax.reload();
            }

            //Mesajes personalizados
            if (data[0].msg == 'error') {
                var arr = data[0].errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }
            //fin de mensajes personalizados
        });

    });

//funcion para eliminar una copia de seguridad
    $(document).on('click', '.del', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */

        var backup = $(this).attr("id");

        bootbox.confirm(Lang.messages.deleteTheBackup + ': <strong>' + backup + '</strong>  ?', function (result) {

            if (result) {

                $.ajax({
                    type: "POST",
                    url: "backups/delete",
                    data: {'file': backup},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.msg == 'errordelete')
                        msg(Lang.messages.backupcouldnotbedeleted, 'error');
                    if (data.msg == 'success') {
                        msg(Lang.messages.Backupdeletedsuccessfully, 'success');
                        treload.ajax.reload();
                    }
                });
            }
        });

    });

//funcion para restaurar la base de datos
    $(document).on('click', '.restore', function (event) {
        event.stopImmediatePropagation();
        /* Act on the event */

        var backup = $(this).attr("id");

        bootbox.confirm(Lang.messages.restorethebackup + ': <strong>' + backup + '</strong>  ?', function (result) {

            if (result) {

                $.easyAjax({
                    type: 'POST',
                    url: "backups/restore",
                    container: "#backups-table",
                    data: {'file': backup},
                    dataType: "json",
                    success: function (data) {
                        if (data.msg == 'errordelete')
                        //msg(data.Message)
                            msg(Lang.messages.Couldnotrestorebackup, 'error');
                        if (data.msg == 'success') {//console.log(data.Message);
                            //	msg(data.Message);
                            msg(Lang.messages.Backupwasrestoredsuccessfully, 'success');
                            treload.ajax.reload();
                        }

                        //Mesajes personalizados
                        if (data.msg == 'error') {
                            var arr = data.errors;
                            $.each(arr, function (index, value) {
                                if (value.length != 0) {
                                    msg(value, 'error');
                                }
                            });
                        }
                    }
                });
            }
        });


    });


    $('#progress').hide();

    $(function () {
        //'use strict';
        // Change this to the location of your server-side upload handler:
        //var url = window.location.hostname === 'backups/upload';
        $('#fileupload').fileupload({
            url: "backups/upload",
            dataType: 'json',
            done: function (e, data) {
                $('#progress').hide();
                treload.ajax.reload();
            },
            progressall: function (e, data) {
                $('#progress').show();
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });


//fin del ready
});
