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
                        <a href="{{ URL::to('inventory/vendors') }}">{{ __('app.inventory') }}</a>
                    </li>
                    <li class="active">{{ __('app.vendors') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.vendors') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.list') }}
                        </small>
                        <button type="button" class="btn btn-sm btn-success newcl" onclick="addVendor();return false;"><i class="icon-plus"></i> Add Vendor
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

        function addVendor() {
            var url = '{{route('inventory.vendors.create')}}';

            $.ajaxModal('#addEditModal', url);
        }


        function addUpdateVendor(id) {
            if(typeof id != "undefined") {
                var url = '{{ route('inventory.vendors.update', ':id') }}';
                url = url.replace(':id', id);
            } else {
                var url = '{{ route('inventory.vendors.store') }}';
            }

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#add_edit_vendor',
                file: true,
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        window.LaravelDataTables["vendor-table"].draw();
                    }
                }
            });
        }


        function editVendor(id) {
            var url = '{{route('inventory.vendors.edit', ':id')}}';
            url = url.replace(':id', id);

            $.ajaxModal('#addEditModal', url);
        }

        function deleteVendor(id) {
            bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletethevendor') }}', function (result) {
                if (result) {
                    var url = '{{route('inventory.vendors.destroy', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#vendor-table",
                        data: {
                            _method:'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["vendor-table"].draw();
                            }
                        }
                    });
                }
            });

        }
    </script>
@endsection