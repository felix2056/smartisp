// Reports Detail Core - Funciones principales JQuery para reportes
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
                msg(Lang.app.error + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            } else
            alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }
    //// fin de la funcion de depuracion
    //aditional config
    bootbox.setDefaults("locale",locale) //traslate bootbox
    // cargamos los montos de dinero general
    function payments(op) {

        if (op == 1) {
            var range = $('#date-range-picker').val();
            var admin = $('#admin').val();
        } else {
            var range = "";
            var admin = "";
        }

        $.ajax({
            "url": "reports/amount",
            "type": "POST",
            "data": {
                "extra_search": range,
                "admin": admin,
            },
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            $('#ing').text(data.total_in + '' + data.simbol);
            $('#egr').text(data.total_out + '' + data.simbol);
            $('#sal').text(data.total + '' + data.simbol);
        });
    }

    payments();
    // fin de montos

    //inicio de tabla reportes
    var styleb = '<div class="action-buttons">';


    var Reports = $('#reports-table').DataTable({
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        pageLength: '10',
        responsive: true,
        "oLanguage": {
            "sUrl": Lang.app.datatable
        },
        destroy: true,
        bAutoWidth: false,
        buttons:[],
        //destroy: true,
        "columnDefs": [{
            "targets": 6,
            "render": function (data, type, full) {
                return styleb + '<a class="red del" href="javascript:void(0);" id="' + full['id'] + '" data-type="' + full['typepay'] + '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
            }
        }],
        ajax: {
            "url": "reports/list",
            "type": "POST",
            "cache": false,
            "data": function (d) {
                d.extra_search = $('#date-range-picker').val();
                d.admin = $('#admin').val();
            },
            "dataSrc": ""
        },
        columns: [{
            data: 'client'
        },{
            data: 'user'
        },
        {
            data: 'detail'
        },

        {
            "mRender": function (data, type, full) {
                if (full['typepay'] == 'ou')
                    return '<span class="label label-danger arrowed">' + Lang.app.expenses + '</span>';
                else
                    return '<span class="label label-success arrowed">' + Lang.app.income + '</span>';

            }
        },
            {
                data: 'date'
            },
            {
                data: 'amount'
            }

            ],
        "fnDrawCallback": function( row, data, dataIndex ) {
            filterTotals();
        }
        });
    // fin de tabla reportes
    function filterTotals()
    {
        $.easyAjax({
            type: 'POST',
            url: '/reports/filter-totals',
            container: "#totals",
            data: {
                extra_search:$('#date-range-picker').val(),
                admin: $('#admin').val()
            },
            success: function(response) {
                $('#totals').html(response.view);
            }
        });
    }

    //TableTools settings

    TableTools.classes.container = "btn-group btn-overlap";
    TableTools.classes.print = {
        "body": "DTTT_Print",
        "info": "tableTools-alert gritter-item-wrapper gritter-info gritter-center white",
        "message": "tableTools-print-navbar"
    }




    var tableTools = new $.fn.dataTable.TableTools(Reports, {
        "sSwfPath": "assets/js/dataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
        "aButtons": [{
            "sExtends": "copy",
            "sToolTip": "Copiar",
            "sButtonClass": "btn btn-white btn-primary btn-bold",
            "sButtonText": "<i class='fa fa-copy bigger-110 pink'></i>",
            "fnComplete": function () {
                this.fnInfo('<h3 class="no-margin-top smaller">'+Lang.app.tableCopied+'</h3>\
                 <p> '+Lang.app.rowToTheClipboard + (oTable1.fnSettings().fnRecordsTotal()) + ' '+Lang.app.rowToTheClipboard+'</p>',
                 1500
                 );
            }
        },

        {
            "sExtends": "xls",
            "sToolTip": "Exportar a exel",
            "sButtonClass": "btn btn-white btn-primary  btn-bold",
            "sButtonText": "<i class='fa fa-file-excel-o bigger-110 green'></i>"
        },

        {
            "sExtends": "pdf",
            "sToolTip": "Exportar a PDF",
            "sButtonClass": "btn btn-white btn-primary  btn-bold",
            "sButtonText": "<i class='fa fa-file-pdf-o bigger-110 red'></i>"
        },

        {
            "sExtends": "print",
            "sToolTip": "Vista de impresión",
            "sButtonClass": "btn btn-white btn-primary  btn-bold",
            "sButtonText": "<i class='fa fa-print bigger-110 grey'></i>",

            "sMessage": "<div class='navbar navbar-default'><div class='navbar-header pull-left'><a class='navbar-brand' href='#'><small>Reportes</small></a></div></div>",

            "sInfo": "<h3 class='no-margin-top'>"+Lang.app.printView+"</h3>\
            <p>"+Lang.messages.printThisTable+"\
            <br />"+Lang.messages.pressEscapeToExit+"</p>",
        }
        ]
    });
    // $( tableTools.fnContainer() ).insertBefore('#reports-table');
    $(tableTools.fnContainer()).appendTo($('.tableTools-container'));
    setTimeout(function () {
        $(tableTools.fnContainer()).find('a.DTTT_button').each(function () {
            var div = $(this).find('> div');
            if (div.length > 0) div.tooltip({
                container: 'body'
            });
                else $(this).tooltip({
                    container: 'body'
                });
            });
    }, 200);

    //
    $(document).on("click", "#search", function () {

        payments(1);
        Reports.ajax.reload();
    });
    //

    //funcion para recuperar todos los registros
    $(document).on('click', '#searchall', function (event) {
        // event.preventDefault();

        $('#date-range-picker').val('');
        payments(0);
        Reports.ajax.reload();
    });

    // recargar tabla
    $(document).on("click", ".recargar", function (event) {
        Reports.ajax.reload();
    });

    //eliminar pago
    $(document).on("click", '.del', function (event) {
        var idr = $(this).attr("id");
        var typepay = $(this).attr('data-type');
        bootbox.confirm(Lang.messages.areYouSureToPermanentlyDeleteTheRecord, function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "reports/delete",
                    data: {
                        "id" : idr,
                        "typepay" : typepay,
                        "extra_search" : $('#date-range-picker').val()
                    },
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.msg == 'error')
                        msg(Lang.messages.theRecordWasNotFound, 'error');
                    if (data.msg == 'success') {
                        msg(Lang.messages.theRecordWasDeleted, 'success');
                        $('#ing').text(data.total_in + '' + data.simbol);
                        $('#egr').text(data.total_out + '' + data.simbol);
                        $('#sal').text(data.total + '' + data.simbol);
                        Reports.ajax.reload();
                    }
                });
            }
        });
    });

    $('input[name=date-range]').daterangepicker({
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        'applyClass': 'btn-sm btn-success',
        'cancelClass': 'btn-sm btn-default',
        'separator': '|',
        locale: {
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Del',
            toLabel: 'Hasta',
            separator: '|',
            format: "DD-MM-YYYY",
            customRangeLabel: "Personalizado",
            daysOfWeek: [
            "Do",
            "Lu",
            "Ma",
            "Mi",
            "Ju",
            "Vi",
            "Sa"
            ],
            monthNames: [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre"
            ],
        },
        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
            'Este Mes': [moment().startOf('month'), moment().endOf('month')],
            'El Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    //fin del reary
});
