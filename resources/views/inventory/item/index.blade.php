@extends('layouts.master')

@section('title', __('app.inventory'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.css')}}" />

    <style type="text/css">
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
                        <a href="{{ URL::to('inventory/items') }}">{{ __('app.inventory') }}</a>
                    </li>
                    <li class="active">{{ __('app.items') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.items') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.list') }}
                        </small>
                        <button type="button" class="btn btn-sm btn-success newcl" onclick="addItem();return false;"><i class="icon-plus"></i> Add Item
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
    <script src="{{asset('assets/plugins/select2/select2.min.js')}}"></script>

    {!! $dataTable->scripts() !!}
    <script>
        $(document).ready(function () {
            $("#idrefre").click(function () {
                location.reload();
            });
        });

        function addItem() {
            var url = '{{route('inventory.items.create')}}';

            $.ajaxModal('#addEditModal', url);
        }

        function internalUsagesItem(id) {
            var url = '{{route('inventory.items.internal-usages-model', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }


        function internalUsagesItemSave(id) {
            var url = '{{ route('inventory.items.internal-usages-save', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#set_internal_usage',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }

        // rent item model
        function rentItem(id) {
            var url = '{{route('inventory.items.rent-item-model', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }

        // rent item save
        function rentItemSave(id) {
            var url = '{{ route('inventory.items.rent-item-save', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#rent_item',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }

        // sell item model
        function sellItem(id) {
            var url = '{{route('inventory.items.item-sell-modal', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }

        // sell item save
        function sellItemSave(id) {
            var url = '{{ route('inventory.items.item-sell-save', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#sell_item',
                data: $('#sell_item').serialize(),
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }

        // return item
        function returnItem(id) {
            var url = '{{route('inventory.items.return-item-modal', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }


        function returnItemSave(id) {
            var url = '{{ route('inventory.items.return-item-save', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#return_item',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }
        // assign customer item
        function itemAssignCustomer(id) {
            var url = '{{route('inventory.items.item-assign-modal', ':id')}}';
            url = url.replace(':id', id);
            $.ajaxModal('#addEditModal', url);
        }


        function itemAssignCustomerSave(id) {
            var url = '{{ route('inventory.items.item-assign-save', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#set_assign_customer',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }

        function addUpdateItem(id) {
            if(typeof id != "undefined") {
                var url = '{{ route('inventory.items.update', ':id') }}';
                url = url.replace(':id', id);
            } else {
                var url = '{{ route('inventory.items.store') }}';
            }

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#add_edit_item',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["item-table"].draw();
                    }
                }
            });
        }


        function editItem(id) {
            var url = '{{route('inventory.items.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteItem(id) {
            bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletethevendor') }}', function (result) {
                if (result) {
                    var url = '{{route('inventory.items.destroy', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#item-table",
                        data: {
                            _method:'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["item-table"].draw();
                            }
                        }
                    });
                }
            });

        }
    </script>
@endsection