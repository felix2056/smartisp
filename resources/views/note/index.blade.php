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
                        Comprobantes electrónicos DIAN
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            Actas
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">Notas crédito</h5>

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
                                                <table id="notecredit-table" class="table table-bordered table-hover" width="100%">
                                                    <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Fecha</th>
                                                        <th>Hora</th>
                                                        <th>N° Factura</th>
                                                        <th>N° nota</th>
                                                        <th>Valor</th>
                                                        <th>Estado</th>
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
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">Notas débito</h5>

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
                                                <table id="notedebit-table" class="table table-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Fecha</th>
                                                        <th>Hora</th>
                                                        <th>N° Factura</th>
                                                        <th>N° nota</th>
                                                        <th>Valor</th>
                                                        <th>Estado</th>
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

                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                <table id="invoices_dian-table" class="table table-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Fecha</th>
                                                        <th>Hora</th>
                                                        <th>N° Factura</th>
                                                        <th>Valor</th>
                                                        <th>Estado</th>
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
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">accepted</font></font></th>
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">rejected</font></font></th>
                                                <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.total')</font></font></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $classes = [
                                                    'factura' => 'success',
                                                    'notecredit' => 'info',
                                                    'notedebit' => 'default',
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
                                                    <td id="admin_customers_view_billing_notecredits_totals_debit_amount">{{ $total['accepted'] }}</td>
                                                    <td id="admin_customers_view_billing_notecredits_totals_debit_amount">{{ $total['rejected'] }}</td>
                                                    <td id="admin_customers_view_billing_notecredits_totals_debit_amount">{{ $total['quantity'] }}</td>
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

    <script>
        var url="{{ route('table.note.lists','1') }}";
        $(function () {
            var table = '';
            renderDataTable('notecredit-table');
            url="{{ route('table.note.lists','2') }}";
            renderDataTable('notedebit-table');
             url="{{ route('table.note.lists','3') }}";
            renderDataTable_invoice('invoices_dian-table');
        });
        function renderDataTable (id) {
            table = $('#'+id).DataTable({
                "oLanguage": {
                    "sUrl": "{{ asset('assets/js/dataTables/dataTables.spanish.txt') }}"
                },
                dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
                processing: true,
                serverSide: true,
                responsive: true,
                pageLength: '10',
                destroy: true,
                buttons:[],
                ajax: {
                    "url": url,
                    "type": "POST",
                    "cache": false,
                },
                columns: [
                    {name:'id', data: 'id'},
                    {name:'date', data: 'date' },
                    {name:'hour', data: 'hour' },
                    {name:'numberfactura', data: 'numberfactura'},
                    {name:'number', data: 'number'},
                    {name:'total', data: 'total'},
                    {name:'status_note', data: 'status_note'},
                    //{name:'action', data: 'action', sortable: false, searchable: false},
                ],
                "createdRow": function( row, data, dataIndex ) {
                }
            });
        }
        function renderDataTable_invoice (id) {
            table = $('#'+id).DataTable({
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
                    "url": url,
                    "type": "POST",
                    "cache": false,
                },
                columns: [
                    {name:'id', data: 'id'},
                    {name:'date', data: 'date' },
                    {name:'hour', data: 'hour' },
                    {name:'number', data: 'number'},
                    {name:'total', data: 'total'},
                    {name:'status_dian', data: 'status_dian'},
                    //{name:'action', data: 'action', sortable: false, searchable: false},
                ],
                "createdRow": function( row, data, dataIndex ) {
                }
            });
        }
    </script>
@endsection
