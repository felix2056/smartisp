@extends('layouts.master')

@section('title', __('app.inventory'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" href="assets/css/select2.min.css" />
    <style type="text/css">
        .form-controls {
            display: block;
            width: 100%;
            height: 34px;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
            box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
            -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
        /*#idrefre {
            top: 85px !important;
            cursor: pointer;
            z-index: 99999;
        }

        #idrefre:hover {
            background: #000;
        }

        #document-table_wrapper .dt-buttons {
            display: none;
        }*/
    </style>

@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="{{ URL::to('admin') }}">{{ __('app.desk') }}</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('inventory/supplier-invoices') }}">{{ __('app.inventory') }}</a>
                    </li>
                    <li class="active">{{ __('app.supplierInvoices') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.supplierInvoices') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.list') }}
                        </small>
                        <button type="button" class="btn btn-sm btn-success newcl" onclick="addSupplierInvoice();return false;"><i class="icon-plus"></i> Add Invoice
                        </button>

                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function () {
            $("#idrefre").click(function () {
                location.reload();
            });
        });

        function addBarCodeSupplierInvoice(id) {
            var url = '{{route('inventory.supplier-invoices.add-bar-code', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }



        function storeBarCodeSupplierInvoice(id) {
            var url = '{{ route('inventory.supplier-invoices.store-bar-code', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#store_barcode',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["supplier-invoices-table"].draw();
                    }
                }
            });
        }

        function addSerialCodeSupplierInvoice(id) {
            var url = '{{route('inventory.supplier-invoices.add-serial-code', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }



        function storeSerialCodeSupplierInvoice(id) {
            var url = '{{ route('inventory.supplier-invoices.store-serial-code', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#store_barcode',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["supplier-invoices-table"].draw();
                    }
                }
            });
        }

        function addSupplierInvoice() {
            var url = '{{route('inventory.supplier-invoices.create')}}';

            $.ajaxModal('#addEditModal', url);
        }


        function addUpdateSupplierInvoice(id) {
            if(typeof id != "undefined") {
                var url = '{{ route('inventory.supplier-invoices.update', ':id') }}';
                url = url.replace(':id', id);
            } else {
                var url = '{{ route('inventory.supplier-invoices.store') }}';
            }

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#add_edit_supplier_invoices',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["supplier-invoices-table"].draw();
                    }
                }
            });
        }


        function editSupplierInvoice(id) {
            var url = '{{route('inventory.supplier-invoices.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteSupplierInvoice(id) {
            bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletethesupplier-invoicesinvoice') }}', function (result) {
                if (result) {
                    var url = '{{route('inventory.supplier-invoices.destroy', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#supplier-invoices-table",
                        data: {
                            _method:'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["supplier-invoices-table"].draw();
                            }
                        }
                    });
                }
            });

        }
    </script>
@endsection