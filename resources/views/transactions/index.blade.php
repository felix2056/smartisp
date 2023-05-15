@extends('layouts.master')

@section('title',__('app.proceedings'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}"/>

    <style>
        .pac-container {
            z-index: 99999;
        }

        #table_estadis {
            border-spacing: 0;
            border-collapse: collapse;
            width: 100%;

        }


        #table_estadis th {
            text-transform: uppercase;
            color: black;
            font-size: 12px;
            line-height: 24px !important;
            background: #f0f0f0;
            border-bottom: #bfbfbf 1px solid;
            text-align: right;
        }

        #table_estadis th:first-child {
            text-align: left;
        }

        #table_estadis td,
        #table_estadis th {
            line-height: 12px;
            border-top: #e8e8e8 1px solid;
            font-size: 12px;
            padding: 3px;
        }

        #table_estadis td.link {
            width: 10px;
            text-align: center
        }

        #table_estadis td.link a {
            text-decoration: none;
            color: black;
        }

        #table_estadis td:nth-child(2) {
            width: 150px;
            padding-left: 6px;
        }

        #table_estadis td:nth-child(3),
        #table_estadis td:nth-child(4),
        #table_estadis td:nth-child(5),
        #table_estadis td:nth-child(6) {
            width: 70px;
            text-align: right;
        }


        #table_estadis td:nth-child(5) {
            padding-left: 3px;
            border-right: 1px solid #e8e8e8;
        }

        .bars {
            font-size: 0px;
        }

        .bars > div {
            font-size: 12px;
            background: rgba(0, 0, 0, 0.2);
            white-space: nowrap;
            box-sizing: border-box;
        }

        .bars > div.total {
            background: #51cb5f;
            height: 20px;
            display: none;

        }

        .bars > div.down {
            background: #43c453;
            height: 15px;
            display: inline-block;
        }

        .bars > div.up {
            background: #8fdd98;
            height: 15px;
            display: inline-block;
        }

        .selector {
            width: 130px;
            -webkit-touch-callout: none;
            margin-left: auto;
            margin-right: 5px;
            border: 1px solid #b4b4b4;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .selector > div {
            display: inline-block;
            font-size: 14px;
            color: black;
            font-family: monospace;
            line-height: 20px;
        }

        .selector > div.pre,
        .selector > div.next {
            cursor: pointer;
            width: 20px;
            user-select: none;
        }

        .selector > div.pre:hover,
        .selector > div.next:hover {
            background: white;
        }

        .selector > div.pre {
            margin-left: 0px;
            margin-right: auto;
            border-right: #b4b4b4 1px solid;
        }

        .selector > div.next {
            margin-left: auto;
            margin-right: 0px;
            border-left: #b4b4b4 1px solid;
        }

        .supertotal,
        .refresh {

            margin-left: 5px;
            margin-right: 5px;

            line-height: 20px;
            border: 1px solid #b4b4b4;
            width: 170px;
            font-family: monospace;
            text-align: center;
            font-size: 14px;
            color: black;
        }

        .refresh {
            text-transform: uppercase;
            cursor: pointer;
            color: #57a6ff;
            letter-spacing: 1px;
            font-size: 16px;

            background: url(../images/reload.svg) center center no-repeat;
            background-size: 15px;
            font-size: 0px;
            width: 22px;
            box-shadow: inset 0px 0px 2px white;
        }

        .refresh:hover {
            opacity: 0.8;
        }

        .refresh.active {
            pointer-events: none;
            opacity: 0.5;
            color: white;
        }

        .loading table {
            opacity: 0.5;
        }


        .brandbar {
            display: block;
            float: left;
            width: 30px;
            height: calc(100% - 4px);
            background: blue;
            margin: 2px;
            background: #3f3fc2; /* Old browsers */
            background: -moz-linear-gradient(top, #3f3fc2 0%, #000076 100%); /* FF3.6-15 */
            background: -webkit-linear-gradient(top, #3f3fc2 0%, #000076 100%); /* Chrome10-25,Safari5.1-6 */
            background: linear-gradient(to bottom, #3f3fc2 0%, #000076 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#3f3fc2', endColorstr='#000076', GradientType=0); /* IE6-9 */
        }

        .refresh {
            width: 95px !important;
            font-size: 14px;
            background: #000;
            color: #fff;
        }

        .brandbar:before {
            content: 'MikroStats';
            color: #8080b3;
            transform: rotate(270deg);
            display: block;
            font-size: 18px;
            position: absolute;
            bottom: 45px;
            left: -27px;
        }

        .topbar {
            width: 100%;
            line-height: 35px;
            height: 35px;
            border-bottom: #7c7c7c 1px solid;
            background: #f0f0f0;
            display: flex;
            align-items: center;
        }


        .window {
            position: relative;
            top: 35px;
            left: 170px;
            width: calc(100% - 170px);
            background: #f0f0f0;
        }


        .clock {
            border: 1px solid #a3a3a3;
            width: 100px;
            line-height: 18px;
            height: 18px;
            margin-left: auto;
            margin-right: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-shadow: 0px 0px 1px rgba(255, 255, 255, 0.5);
            font-family: monospace;
        }

        .movebar {
            background: #4a4dce;
            color: white;
            font-size: 17px;
            line-height: 24px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            border-top: 1px solid #9b9de9;
            border-left: 1px solid #9b9de9;
            border-right: 1px solid #9b9de9;
            margin-top: 20px;
        }

        .window {
            border: 1px solid black;
            height: calc(100vh - 37px);
            position: absolute;
        }

        .movebar .close {
            margin-left: auto;
            width: 16px;
            height: 16px;
            line-height: 16px;
            font-size: 15px;
            margin-right: 3px;
            border: 1px solid black;
            text-align: center;
            background: #f0f0f0;
            color: black;
            box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .toolbar {
            background: #f0f0f0;
            height: 35px;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .content {
            position: absolute;
            background: white;
            margin: 5px;
            border: 1px solid #7c7c7c;
            bottom: 0px;
            left: 0px;
            right: 0px;
            top: 10px;
            overflow: scroll;
            -webkit-overflow-scrolling: touch;

        }

        .queues {
            color: grey;
            font-size: 11px;
        }

        [data-server="ffk1.skynet-jc.at"] .bars > div.down {
            background: #eaac34;
        }

        [data-server="ffk1.skynet-jc.at"] .bars > div.up {
            background: #facf49;
        }

        /* !iPad CSS */
        @media only screen and (max-width: 600px) {
            .sidebar {
                display: none;
            }

            .window {
                left: 0px;
                width: 100%;
            }

            .close {
                display: none;
            }
        }

        .date-range-picker-height {
            height: 37px !important;
        }

        .input-group > .btn.btn-sm {
            line-height: 29px;
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
                        <a href="{{ route('finance.dashboard') }}">@lang('app.finance')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.financiar')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.proceedings')
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
                                                            class="btn btn-sm btn-purple pull-right">
                                                        <i class="fa fa-search-plus"></i>
                                                        @lang('app.showAll')
                                                    </button>

                                                    <button type="button" id="search"
                                                            class="btn cero_margin btn-sm btn-success">
                                                        <i class="fa fa-search"></i>
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
                                        <h5 class="widget-title">@lang('app.transactions')</h5>

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
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
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
                                    <div class="panel-body" id="totals">

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
        <script
            src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
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
    <script type="text/javascript" src="{{ asset('assets/plugins/daterangepicker/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/plugins/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/moment.min.js') }}"></script>
    <script src="{{asset('assets/js/rocket/tcl.js')}}"></script>

    {!! $dataTable->scripts() !!}
    <script>

        $(function () {

            $('#transaction-table').on('preXhr.dt', function (e, settings, data) {

                var extra_search = $('#date-range-picker').val();

                data['extra_search'] = extra_search;
            });

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
                window.LaravelDataTables["transaction-table"].draw();
            });

            //funcion para recuperar todos los registros
            $(document).on('click', '#searchall', function (event) {
                $('#date-range-picker').val('');
                window.LaravelDataTables["transaction-table"].draw();
            });
        });

        function editTransaction(id) {
            var url = '{{route('transaction.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteTransaction(id) {

            bootbox.confirm("{{ __('messages.areyousureyouwanttodeletethetransaction') }}", function (result) {
                if (result) {
                    var url = '{{route('transaction.delete', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#transaction-table",
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["transaction-table"].draw();
                            }
                        }
                    });
                }
            });
        }

        function filterTotals() {
            $.easyAjax({
                type: 'POST',
                url: '{{ route('finance.transaction.filter-totals') }}',
                container: "#totals",
                data: {
                    extra_search: $('#date-range-picker').val()
                },
                success: function (response) {
                    $('#totals').html(response.view);
                }
            });
        }
    </script>
@endsection
