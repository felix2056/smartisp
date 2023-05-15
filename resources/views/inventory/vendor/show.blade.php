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
                        <a href="{{ URL::to('inventory/vendors') }}">{{ __('app.vendors') }}</a>
                    </li>
                    <li class="active">{{ $vendor->name }}</li>
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
                                <strong>Vendor Information</strong>
                                <div class="pull-right">
                                    <div class="btn-group btn-group-xs" role="group">
                                        <button type="button" onclick="editVendor('{{ $vendor->id }}')" class="btn btn-sm btn-primary"  title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-striped">
                                    <a class="list-group-item">
                                        <strong>Name: </strong>
                                        <span class="pull-right">{{ $vendor->name }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6">
                        <div class="card">
                            <div class="card-header">
                            <span class="icon-wrap">
                                <i class="fa fa-info-circle orange"></i>
                            </span>
                                <strong>Vendor Suppliers</strong>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-striped">
                                    @forelse($vendorSuppliers as $supplier)
                                        <a class="list-group-item" href="{{ route('inventory.suppliers.show', $supplier->id) }}">
                                            <strong>{{ $supplier->name }}</strong>
                                            <span class="pull-right"><i class="fa fa-share-square-o"></i></span>
                                        </a>
                                    @empty
                                        <a class="list-group-item">
                                            <span>No supplier found.</span>
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
                                <h5 class="widget-title">Products of vendor</h5>
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