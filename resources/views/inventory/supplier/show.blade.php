@extends('layouts.master')

@section('title', __('app.inventory'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.css')}}" />
    <style type="text/css">
        .card-header {
            padding: 20px;
            background: #fff;
            font-size: 15px;
        }
        .dropdown-menu button {
            background-color: white !important;
            color: black !important;
            border: none;
            border-bottom: 1px solid #bbb;
            border-radius: 0;
            margin-bottom: 5px;
        }
        .open >.dropdown-menu {
            min-width: 95px;
        }
        .action-buttons > .dropdown-menu {
            min-width: 95px;
        }
        .btn:hover {
            color: #ffffff !important;
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
                        <a href="{{ URL::to('admin') }}">{{ __('app.desk') }}</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('inventory/products') }}">{{ __('app.inventory') }}</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('inventory/suppliers') }}">{{ __('app.suppliers') }}</a>
                    </li>
                    <li class="active">{{ $supplier->name }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="card">
                            <div class="card-header">
                            <span class="icon-wrap">
                                <i class="fa fa-info-circle orange"></i>
                            </span>
                                <strong>Supplier Information</strong>
                                <div class="pull-right">
                                    <div class="btn-group btn-group-xs" role="group">
                                        <button type="button" onclick="editSupplier('{{ $supplier->id }}')" class="btn btn-sm btn-primary"  title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-striped">
                                    <a class="list-group-item">
                                        <strong>Name: </strong>
                                        <span class="pull-right">{{ $supplier->name }}</span>
                                    </a>
                                    <a class="list-group-item">
                                        <strong>Address: </strong>
                                        <span class="pull-right">{{ $supplier->address }}</span>
                                    </a>
                                    <a class="list-group-item">
                                        <strong>Contact name: </strong>
                                        <span class="pull-right">{{ $supplier->contact_name }}</span>
                                    </a>
                                    <a class="list-group-item">
                                        <strong>Email: </strong>
                                        <span class="pull-right">{{ $supplier->email }}</span>
                                    </a>
                                    <a class="list-group-item">
                                        <strong>Phone: </strong>
                                        <span class="pull-right">{{ $supplier->phone }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6">
                        <div class="card">
                            <div class="card-header">
                            <span class="icon-wrap">
                                <i class="fa fa-users orange"></i>
                            </span>
                                <strong>Supplier vendors</strong>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-striped">
                                    @forelse($supplierVendors as $vendor)
                                        <a class="list-group-item" href="{{ route('inventory.vendors.show', $vendor->id) }}">
                                            <strong>{{ $vendor->name }}</strong>
                                            <span class="pull-right"><i class="fa fa-share-square-o"></i></span>
                                        </a>
                                    @empty
                                        <a class="list-group-item">
                                            <span>No vendor found.</span>
                                        </a>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">Invoices of supplier</h5>
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
                <div class="row">
                    <div class="col-xs-12">
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">Supplier Products</h5>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <!--Contenido widget-->
                                    <div class="table-responsive">
                                        <table id="supplier_products" class="table table-bordered table-hover" width="100%">
                                            <thead>
                                            <tr>
                                                <th>@lang('app.name')</th>
                                                <th>@lang('app.vendor')</th>
                                                <th>@lang('app.sellPrice')</th>
                                                <th>@lang('app.rentPrice')</th>
                                                <th>@lang('app.photo')</th>
                                                <th>@lang('app.inStock')</th>
                                                <th>@lang('app.internalUsages')</th>
                                                <th>@lang('app.rented')</th>
                                                <th>@lang('app.sold')</th>
                                                <th>@lang('app.returned')</th>
                                                <th>@lang('app.assigned')</th>
                                                <th>@lang('app.operations')</th>
                                            </tr>
                                            </thead>
                                        </table>
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
    <script src="{{asset('assets/plugins/select2/select2.min.js')}}"></script>
    {!! $dataTable->scripts() !!}
    <script>
        renderDataTable();
        function renderDataTable() {
            if ($.fn.DataTable.isDataTable('#invoice-table')) {
                $('#supplier_products').dataTable().fnClearTable();
                $('#supplier_products').dataTable().fnDestroy();
            }

            table = $('#supplier_products').DataTable({
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

                ],
                ajax: {
                    "url": "{{ route('inventory.suppliers.products', $supplier->id) }}",
                    "type": "POST",
                    "cache": false,
                },
                columns: [
                    {name: 'name', data: 'name'},
                    {name: 'vendor.name', data: 'vendor.name'},
                    {name: 'sell_price', data: 'sell_price'},
                    {name: 'rent_price', data: 'rent_price'},
                    {name: 'photo', data: 'photo'},
                    {name: 'in_stock', data: 'in_stock', sortable: false, searchable: false},
                    {name: 'internal_usages', data: 'internal_usages', sortable: false, searchable: false},
                    {name: 'rented', data: 'rented', sortable: false, searchable: false},
                    {name: 'sold', data: 'sold', sortable: false, searchable: false},
                    {name: 'returned', data: 'returned', sortable: false, searchable: false},
                    {name: 'assigned', data: 'assigned', sortable: false, searchable: false},
                    {name: 'action', data: 'action', sortable: false, searchable: false},
                ],
            });
        }

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

        function addUpdateProduct(id) {
            if(typeof id != "undefined") {
                var url = '{{ route('inventory.products.update', ':id') }}';
                url = url.replace(':id', id);
            } else {
                var url = '{{ route('inventory.products.store') }}';
            }

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#add_edit_product',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["product-table"].draw();
                    }
                }
            });
        }


        function editProduct(id) {
            var url = '{{route('inventory.products.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteProduct(id) {
            bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletetheproduct') }}', function (result) {
                if (result) {
                    var url = '{{route('inventory.products.destroy', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#product-table",
                        data: {
                            _method:'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["product-table"].draw();
                            }
                        }
                    });
                }
            });

        }
    </script>
@endsection