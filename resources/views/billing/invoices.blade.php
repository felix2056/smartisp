<style type="text/css">
    .btn-success {
        margin-bottom: 14px !important;
    }

    .widget-toolbar:before {
        border: none;
    }

    .btn-group > .btn.btn-sm > .caret {
        margin-top: 1px !important;
    }

    .btn-group > .btn.btn-success {
        border-radius: 8px;
        padding: 6px;
    }

    #invoice-table_wrapper .dt-buttons {
        display: none;
    }

    #recurring-invoice-table_wrapper .dt-buttons {
        display: none;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                        @lang('app.addInvoice') <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">

                        <li><a href="javascript:;"
                               onclick="addOneTimeInvoice({{ $clients->id }});return false;">@lang('app.addSingleInvoice')</a>
                        </li>

                        <li><a href="javascript:;"
                               onclick="addRecurringInvoices({{ $clients->id }});return false;">@lang('app.addRecurringInvoice')</a>
                        </li>

                        <li><a href="javascript:;"
                               onclick="addInvoice({{ $clients->id }});return false;">@lang('app.addCustomInvoice')</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="widget-box widget-color-blue2">
            <div class="widget-header">
                <h5 class="widget-title">Invoice</h5>
                <div class="widget-toolbar">
                    <a href="#" data-action="reload" id="idrefres" class="recargar white">
                        <i class="ace-icon fa fa-refresh"></i>
                    </a>
                </div>
            </div>
            <div class="widget-body">
                <div class="widget-main">
                    <!--Contenido widget-->
                    <div class="table-responsive">

                        <table id="invoice-table" class="table table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('app.billNumber')</th>
                                <th>@lang('app.releaseDate')</th>
                                <th>@lang('app.serviceCut')</th>
                                <th>@lang('app.total')</th>
                                <th>@lang('app.paymentDate')</th>
                                <th>@lang('app.state')</th>
                                <th>@lang('app.behavior')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="widget-box widget-color-blue2">
            <div class="widget-header">
                <h5 class="widget-title">Recurring Invoice</h5>
                <div class="widget-toolbar">
                    <a href="#" data-action="reload" class="recargars white">
                        <i class="ace-icon fa fa-refresh"></i>
                    </a>
                </div>
            </div>
            <div class="widget-body">
                <div class="widget-main">
                    <!--Contenido widget-->
                    <div class="table-responsive">
                        <table id="recurring-invoice-table" class="table table-bordered table-hover" width="100%">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('app.startDate')</th>
                                <th>@lang('app.endDate')</th>
                                <th>@lang('app.nextPaymentDate')</th>
                                <th>@lang('app.total')</th>
                                <th>@lang('app.service')</th>
                                <th>@lang('app.behavior')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-lg-6 col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><font style="vertical-align: inherit;"><font
                            style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
            </div>
            <div class="panel-body">
                <table class="display supertable table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.type')</font></font></th>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.total')</font></font></th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $classes = [
                            'Unpaid' => 'warning',
                            'Paid Out' => 'success',
                            'Paid (account balance)' => 'success',
                            'Late' => 'danger',
                            'Total' => 'primary',
                            'Removed' => 'default'
                        ];
                    @endphp
                    @foreach($data as $key => $total)
                        <tr>
                            <td>
                                <label class="label label-{{ $classes[$key] }}">
                                    <font style="vertical-align: inherit;">
                                        <font style="vertical-align: inherit;">
                                            {{ ucFirst($key) }}
                                        </font>
                                    </font>
                                </label>
                            </td>
                            <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total['quantity'] }}</td>
                            <td id="admin_customers_view_billing_transactions_totals_debit_total">{{ $total['total'] }}</td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#idrefres").click(function () {
            table.draw();
        });
        $(".recargars").click(function () {
            recurringTable.draw();
        });
    });
    $(function () {
        var table = '';
        renderDataTable();
        renderRecurringDatatable();
    });

    function send_sri(id) {
        bootbox.confirm("{{ __('messages.sendToSRI') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.payment.send', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#transaction-table",
                    success: function (response) {

                        if (response.status == "success") {
                            obtenerComprobanteFirmado_sri(response.ruta_certificado,
                                response.contrasena,
                                response.ruta_respuesta,
                                response.ruta_factura,
                                response.host_email,
                                response.email,
                                response.passEmail,
                                response.port,
                                response.host_bd,
                                response.pass_bd,
                                response.user_bd,
                                response.database,
                                response.port_bd,
                                response.id_factura,
                            )
                        }

                    }
                });
            }
        });
    }


    function send_fiscal(id) {
        bootbox.confirm("{{ __('messages.sendToFiscal') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.fiscal.send', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#invoice-table",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                        }
                    }
                });
            }
        });
    }

    function send_DIAN(id) {
        bootbox.confirm("{{ __('messages.sendtoDIAN') }}", function (result) {
            if (result) {
                var url = '{{route('invoice_colombia.payment.send', ':id')}}';
                url = url.replace(':id', id);
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#transaction-table",
                    success: function (response) {
                        if (response.status == "success") {
                            sendEmail_dian(response.cufe, response.qr, response.typeoperation_cod, response.prefix, response.number, response.date, response.typedocEmisor, response.identificationEmisor, response.nameEmisor, response.tradename, response.typetaxpayerEmisor, response.directionEmisor, response.emailEmisor, response.phoneEmisor, response.typedocAdquiriente, response.identificationAdquiriente, response.nameAdquiriente, response.taxnameAdquiriente, response.typetaxpayerAdquiriente, response.directionAdquiriente, response.emailAdquiriente, response.phoneAdquiriente, response.detalle, response.money, response.subtotal, response.iva, response.total, response.resolution_number, response.resolution_desde, response.resolution_hasta, response.resolution_date, response.filename, response.correo, response.host_email, response.email_origen, response.passEmail, response.port);
                        }
                    }
                });
            }
        });
    }

    function send_Note_DIAN(id) {
        var url = '{{route('note.create', ':id')}}';
        url = url.replace(':id', id);
        $.ajaxModal('#addCreateModal', url);
    }

    function renderDataTable() {
        if ($.fn.DataTable.isDataTable('#invoice-table')) {
            $('#invoice-table').dataTable().fnClearTable();
            $('#invoice-table').dataTable().fnDestroy();
        }
        table = $('#invoice-table').DataTable({
            "oLanguage": {
                "sUrl": '{{ __('app.datatable') }}'
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            serverSide: true,
            pageLength: '5',
            responsive: true,
            destroy: true,
            order: [
                '0', 'desc'
            ],
            buttons: [
                'excel', 'csv'
            ],
            ajax: {
                "url": "{{ route('invoice.list', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'num_bill', data: 'num_bill'},
                {name: 'release_date', data: 'release_date'},
                {name: 'cortado_date', data: 'cortado_date'},
                {name: 'total_pay', data: 'total_pay'},
                {name: 'paid_on', data: 'paid_on'},
                {name: 'status', data: 'status'},
                {name: 'action', data: 'action', sortable: false, searchable: false},
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass('details-control');
                $(row).attr('data-id', data.id);
            }
        });

    }

    function renderRecurringDatatable() {
        recurringTable = $('#recurring-invoice-table').DataTable({
            "oLanguage": {
                "sUrl": '{{ __('app.datatable') }}'
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            serverSide: true,
            pageLength: '5',
            responsive: true,
            destroy: true,
            order: [
                '0', 'desc'
            ],
            buttons: [
                'excel', 'csv'
            ],
            ajax: {
                "url": "{{ route('invoice.recurringInvoiceList', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'start_date', data: 'start_date'},
                {name: 'end_date', data: 'end_date'},
                {name: 'next_pay_date', data: 'next_pay_date'},
                {name: 'price', data: 'price'},
                {name: 'service_status', data: 'service_status'},
                {name: 'action', data: 'action', sortable: false, searchable: false},
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass('details-control');
                $(row).attr('data-id', data.id);
            }
        });

    }

    function sendEmail(id) {

        bootbox.confirm("{{ __('messages.sendInvoiceMail') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.sendEmail', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#invoice-table",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                        }
                    }
                });
            }
        });

    }

    function addOneTimeInvoice(id) {
        var url = '{{ route('invoice.addOneTimeInvoiceView', ':id') }}';
        url = url.replace(':id', id);
        $('.modal-content').addClass('modal-lg');
        $.ajaxModal('#addEditModal', url);
    }

    function addInvoice(id) {
        var url = '{{ route('transactionsToBillView', ':id') }}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    function addRecurringInvoices(id) {
        var url = '{{ route('invoice.recurringInvoice', ':id') }}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    function showInvoice(id) {
        var url = '{{ route('invoice.showInvoice', ':id') }}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    function payInvoice(id) {
        var url = '{{ route('invoice.payInvoiceView', ':id') }}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    function editInvoice(id) {
        var url = '{{route('invoice.edit', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';
        $('.modal-content').addClass('modal-lg');
        $.ajaxModal('#addEditModal', url);
    }

    function editCustomInvoice(id) {
        var url = '{{route('invoice.editRecurring', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';

        $.ajaxModal('#addEditModal', url);
    }

    function editRecurringInvoice(id) {
        var url = '{{route('invoice.editRecurringInvoice', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';

        $.ajaxModal('#addEditModal', url);
    }

    function deleteInvoice(id) {
        bootbox.confirm("{{ __('messages.deleteInvoice') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.delete', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#invoice-table",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                        }
                    }
                });
            }
        });
    }

    function deleteRecurringInvoice(id) {
        bootbox.confirm("{{ __('messages.deleteInvoice') }}", function (result) {
            if (result) {
                var url = '{{route('recurring-invoice.delete', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#invoice-table",
                    success: function (response) {
                        if (response.status == "success") {
                            recurringTable.draw();
                        }
                    }
                });
            }
        });
    }

    function deleteInvoicePayment(id) {
        bootbox.confirm("{{ __('messages.markUnpaid') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.payment.delete', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#transaction-table",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                        }
                    }
                });
            }
        });
    }

    function editInvoicePayment(id) {
        var url = '{{route('invoice.payment.edit', ':id')}}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    // function showPDF (id) {
    //     console.log(id)
    //     window.location.href = '{{ route("invoice.showPDF", ["id" => 'id']) }}'
    // }

    function banRecurring(id) {

        var url = '{{ route('recurring.services.ban', ':id') }}';
        url = url.replace(':id', id);
        bootbox.confirm('{{ __('messages.activateCustomerService') }}', function (result) {
            if (result) {
                $.ajax({
                    "type": "POST",
                    "url": url,
                    "data": {"id": id},
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {
                    console.log(data)

                    if (data[0].msg == 'banned') {
                        msg('{{ __('messages.serviceWasCut') }}', 'info');
                        recurringTable.ajax.reload();
                    }
                    if (data[0].msg == 'unbanned') {
                        msg('{{ __('messages.serviceIsActivated') }}', 'info');
                        recurringTable.ajax.reload();
                    }
                });
            }
        });


    }

    function generateRecurringInvoice(id) {

        bootbox.confirm("{{ __('messages.areyousurewanttogenerateinvoice') }}", function (result) {
            if (result) {
                var url = '{{route('invoice.recurringInvoiceGenerate', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "body",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                            recurringTable.draw();
                        }
                    }
                });
            }
        });

    }

    function send_SAT(idInvoice) {
        console.log('BILLING INVOICE VIEW');
        bootbox.confirm("{{ __('messages.sendtoSAT') }}", function (result) {
            if (result) {
                var url = '{{route('invoice_mx.payment.send', ':id')}}';
                url = url.replace(':id', idInvoice);
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#transaction-table",
                    success: function (response) {
                        if (response.status == "success") {
                            console.log(response);
                            table.draw();
                        }
                    }
                });
            }
        });
    }

    function send_email_sat(idInvoice) {
        bootbox.confirm("Enviar factura por correo", function (result) {
            if (result) {
                var url = '{{route('invoice_mx.payment.email')}}';
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        doc_id: idInvoice
                    },
                    container: "#transaction-table",
                    success: function (response) {
                        if (response.status == "success") {
                            console.log(response);
                            table.draw();
                        }
                    }
                });
            }
        });
    }

</script>
