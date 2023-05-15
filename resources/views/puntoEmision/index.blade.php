@extends('layouts.master')

@section('title',__('app.proceedings'))

@section('styles')
@parent
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
@stop

@section('content')
<div id="navbar" class="navbar navbar-default">
    <div class="navbar-container" id="navbar-container">
        <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
            <span class="sr-only">Toggle sidebar</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        @include('layouts.sidebartopleft')
        <!-- navbarheader right -->
        @include('layouts.navbartopright')
        <!-- navbarheader right -->
    </div>
</div>

<div class="main-container" id="main-container">

    <!-- sidebar left menu -->
    @include('layouts.sidebarmenu')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Puntos de Emision
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            SRI
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <a style = "font-size: 20px;" href="javasscript:;" title="New" onclick="new_establecimeinto(); return false;"><span class="glyphicon glyphicon-new-window">Nuevo</span></a>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">Puntos de Emision</h5>

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
                                                <table id="transaction-table" class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Establecimiento</th>
                                                            <th>Nombre</th>
                                                            <th>Codigo</th>
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
                                        <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
                                    </div>
                                    <div class="panel-body">
                                        <table class="display supertable table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.type')</font></font></th>
                                                    <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
                                                    <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.total')</font></font></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $classes = [
                                                'punto_emision' => 'danger',

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
    
    <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
        <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
    </a>
</div>

@section('scripts')
@parent
@if($map!='0')
<script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
{{-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&key={{$map}}&libraries=places"></script> --}}
{{-- <script type="text/javascript" src='https://maps.google.com/maps/api/js?key={{$map}}&libraries=places'></script> --}}
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

                                 $(function () {
                                 var table = '';
                                         renderDataTable();
                                 });
                                 function new_establecimeinto(){
                                 var url = '{{route('ptoEmision.newInvoice')}}';
                                         $.ajaxModal('#addEditModal', url)
                                 }
                         function renderDataTable () {
                         table = $('#transaction-table').DataTable({
                         "oLanguage": {
                         "sUrl": "{{ asset('assets/js/dataTables/dataTables.spanish.txt') }}"
                         },
                                 processing: true,
                                 serverSide: true,
                                 pageLength: '10',
                                 buttons:[],
                                 ajax: {
                                 "url": "{{ route('ptoEmision.invoice.lists') }}",
                                         "type": "POST",
                                         "cache": false,
                                 },
                                 columns: [
                                 {name:'id', data: 'id'},
                                 {name:'establecimiento', data: 'id_establecimiento' },
                                 {name:'nombre', data: 'nombre'},
                                 {name:'codigo', data: 'codigo'},
                                 {name:'action', data: 'action', sortable: false, searchable: false},
                                 ],
                                 "createdRow": function(row, data, dataIndex) {
                                 }
                         });
                         }

                         function editTransaction (id) {
                         var url = '{{route('transaction.edit', ':id')}}';
                                 url = url.replace(':id', id);
                                 $.ajaxModal('#addEditModal', url);
                         }
                         function editarPtoEmision (id) {
                         var url = '{{ route('ptoEmision.showInvoice', ':id') }}';
                                 url = url.replace(':id', id);
                                 $.ajaxModal('#addEditModal', url);
                         }

                         function eliminarPtoEmision (id){
                         var url = '{{ route('ptoEmision.delete', ':id') }}';
                                 url = url.replace(':id', id);
                                 $.easyAjax({
                                 type: 'GET',
                                         url: url,
                                         container: "#transaction-table",
                                         success: function(response) {
                                         if (response.status == "success") {
                                         table.draw();
                                         }
                                         }
                                 });
                         }

                         function send_sri (id) {
                         bootbox.confirm("Enviar a SRI ?", function(result) {
                         if (result) {
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
                         function edit_establecimiento(id) {
                         
                                 var url = '{{ route('secuenciales.update', ':id') }}';
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

                      

                         function showInvoice(id) {
                         var url = '{{ route('secuenciales.showInvoice', ':id') }}';
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
                         if (result) {
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
                         if (result) {
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
@stop
@stop
