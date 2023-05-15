@extends('layouts.master')

@section('title','Actas')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}" />

    <style>
       .mr-10 {
           margin-right: 10px;
       }
       .tab-content {
           background-image: initial;
           background-position-x: initial;
           background-position-y: initial;
           background-size: initial;
           background-repeat-x: initial;
           background-repeat-y: initial;
           background-attachment: initial;
           background-origin: initial;
           background-clip: initial;
           background-color: #ffffff;
       }
        .date-range-picker-height {
            height: 37px !important;
        }
        .input-group>.btn.btn-sm {
            line-height: 29px;
        }
        .form-control {
            height: 36px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="{{ URL::to('admin') }}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('clients') }}">@lang('app.finance')</a>
                    </li>
                    <li class="active">@lang('app.bills')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Comprobantes Electronicos SRI
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            Actas
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-12">
                                <div class="card pull-up">
                                    <div class="card-content">
                                        <div class="card-body">

                                            <form class="form-inline center_div" method="get" action="reports">
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar bigger-110"></i>
                                                    </span>
                                                    <input class="form-control date-range-picker-height" type="text"
                                                           name="date-range" id="date-range-picker" readonly/>
                                                </div>
                                                <div class="input-group">
                                                    <button type="button" id="searchall"
                                                            class="btn btn-sm btn-purple pull-right"><i
                                                                class="fa fa-search-plus"></i>
                                                        @lang('app.showAll')
                                                    </button>
                                                    <button type="button" id="search"
                                                            class="btn cero_margin btn-sm btn-success"><i
                                                                class="fa fa-search"></i>
                                                        @lang('app.filter')
                                                    </button>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.bills')</h5>

                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown"
                                                   class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>
                                            </div>
                                            <a href="#" data-action="fullscreen" class="white">
                                                <i class="ace-icon fa fa-expand"></i>
                                            </a>
                                            <a href="#" data-action="reload" class="recargar white">
                                                <i class="ace-icon fa fa-refresh"></i>
                                            </a>
                                            <a href="#" data-action="collapse" class="white">
                                                <i class="ace-icon fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                        <a href="javascript:;" class="btn btn-primary btn-sm pull-right mr-10" onclick="OpenExport(); return false;">Exportar</a>
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                <table id="transaction-table" class="table table-bordered table-hover dt-bootstrap responsive" style="width:100%">
                                                    <thead>
                                                    <tr>
                                                        <th width="5%">Nro Factura</th>
                                                        <th width="5%">Id Error</th>
                                                        <th width="20%">@lang('app.message')</th>
                                                        <th width="20%">Info Adicional</th>
                                                        <th width="10%">@lang('app.type')</th>
                                                        <th width="10%">Clave Acceso</th>
                                                        <th width="10%">@lang('app.state')</th>
                                                        <th width="10%">@lang('app.date')</th>
                                                        <th width="10%">@lang('app.behavior')</th>
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
                                        <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
                                    </div>
                                    <div class="panel-body">
                                        <table class="display supertable table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.type')</font></font></th>
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Total</font></font></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $classes = [
                                                    'No Autorizadas' => 'danger',
                                                    'Devueltas' => 'warning',
                                                    'autorizadas' => 'success',
                                                    'recibidas' => 'success',
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
                    </div>
                </div>

                @include('layouts.modals')

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($map!='0')
        <script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
    @endif
    <script src="{{asset('assets/js/highchart/code/highcharts.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/modules/exporting.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/themes/grid.js')}}"></script>
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/date-time/locales/bootstrap-datepicker.es.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    <script src="{{asset('assets/js/pGenerator.jquery.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-typeahead.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/tcl.js')}}"></script>

    <script type="text/javascript" src="{{ asset('assets/plugins/daterangepicker/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/plugins/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/fiddle.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/uft8.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/forge.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/buffer.js') }}"></script>
    <script>
        var table = '';
        $(function () {

            renderDataTable();

            $('input[name=date-range]').daterangepicker({
                'applyClass': 'btn-sm btn-success',
                'cancelClass': 'btn-sm btn-default',
                'separator': '|',
                startDate: moment().subtract(1, 'month'),
                endDate: moment(),
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

            $(document).on("click", "#search", function () {
                renderDataTable();
            });

            //funcion para recuperar todos los registros
            $(document).on('click', '#searchall', function (event) {
                $('#date-range-picker').val('');
                renderDataTable();
            });

        });

        function renderDataTable () {
            var range = $('#date-range-picker').val();
            table = $('#transaction-table').DataTable({
                "oLanguage": {
                    "sUrl": "{{ asset('assets/js/dataTables/dataTables.spanish.txt') }}"
                },
                dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
                processing: true,
                serverSide: true,
                pageLength: '10',
                responsive: true,
                destroy: true,
                buttons:[],
                ajax: {
                    "url": "{{ route('sri.invoice.lists') }}",
                    "type": "POST",
                    "cache": false,
                    "data": {
                        "extra_search": range
                    }
                },
                columns: [
                    {name:'id_factura', data: 'id_factura'},
                    {name:'id_error', data: 'id_error' },
                    {name:'mensaje', data: 'mensaje', sortable: false},
                    {name:'informacionAdicional', data: 'informacionAdicional'},
                    {name:'tipo', data: 'tipo'},
                    {name:'claveAcceso', data: 'claveAcceso'},
                    {name:'estado', data: 'estado'},
                    {name:'updated_at', data: 'updated_at'},
                    {name:'action', data: 'action', sortable: false, searchable: false},
                ],
                "createdRow": function( row, data, dataIndex ) {
                }
            });
        }

        function editTransaction (id) {
            var url = '{{route('transaction.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function send_sri (id) {
            bootbox.confirm("Enviar a SRI ?", function(result) {
                if(result) {
                    var url = '{{route('invoice.payment.send', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#transaction-table",
                        success: function(response) {

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

        function removeSri(id) {
            var url = '{{ route('sri.delete', ':id') }}';
            url = url.replace(':id', id)

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#export-history-table",
                success: function(response) {
                    if (response.status == "success") {
                        table.draw();
                    }
                }
            });
        }
        function deleteTransaction (id) {
            var url = '{{route('transaction.delete', ':id')}}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#transaction-table",
                success: function(response) {
                    if (response.status == "success") {
                        table.draw();
                    }
                }
            });
        }

        function OpenExport () {
            var url = '{{route('sri.export-popup')}}';

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

        function editInvoice (id) {
            var url = '{{route('invoice.edit', ':id')}}';
            url = url.replace(':id', id);

            $('.modal-content').addClass('modal-lg');
            $.ajaxModal('#addEditModal', url);
        }

        function deleteInvoice (id) {

            bootbox.confirm("Are you sure want to remove invoice ?", function(result) {
                if(result) {
                    var url = '{{route('invoice.delete', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#invoice-table",
                        success: function(response) {
                            if (response.status == "success") {
                                table.draw();
                            }
                        }
                    });
                }
            });

        }

        function editInvoicePayment (id) {
            var url = '{{route('invoice.payment.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteInvoicePayment (id) {
            bootbox.confirm("Are you sure want to make invoice as unpaid ?", function(result) {
                if(result) {
                    var url = '{{route('invoice.payment.delete', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#transaction-table",
                        success: function(response) {
                            if (response.status == "success") {
                                table.draw();
                            }
                        }
                    });
                }
            });
        }

        function editRecurringInvoice (id) {
            var url = '{{route('invoice.editRecurring', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }
    </script>
@endsection
