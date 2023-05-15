@extends('layouts.master')

@section('title','Copias de seguridat')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/jQuery-File-Upload/css/jquery.fileupload.css') }}"/>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="{{URL::to('admin')}}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="#">@lang('app.system')</a>
                    </li>
                    <li class="active">@lang('app.backups')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.backups')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">

                        <!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="btn btn-info fileinput-button">
                            <i class="ace-icon fa fa-cloud-upload"></i>
                            <span>@lang('app.Uploadcopy')</span>
                                        <!-- The file input field used as target for the file upload widget -->
                            <input id="fileupload" type="file" name="files[]" multiple>
			            </span>

                        <span class="btn btn-success fileinput-button" id="newbackup">
                            <i class="ace-icon fa fa-bolt"></i>
                            <span>@lang('app.Createcopy')</span>

                        </span>

                        <br>
                        <br>
                        <!-- The global progress bar -->
                        <div id="progress" class="progress">
                            <div class="progress-bar progress-bar-success"></div>
                        </div>


                        <div class="hr hr-18 dotted hr-double"></div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.Allcopies')</h5>
                                        <div class="widget-toolbar">
                                            <a href="#" data-action="fullscreen" class="white">
                                                <i class="ace-icon fa fa-expand"></i>
                                            </a>
                                            <a href="#" data-action="reload" class="white recargar">
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
                                                <table id="backups-table" class="table table-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>@lang('app.Archive')</th>
                                                        <th>@lang('app.Created')</th>
                                                        <th>@lang('app.Size')</th>
                                                        <th>@lang('app.operations')</th>
                                                    </tr>
                                                    </thead>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('layouts.modals')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/bootbox.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.waiting.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.gritter.min.js') }}"></script>
    <script src="{{ asset('assets/js/rocket/backups-core.js') }}"></script>
    <script src="{{ asset('assets/js/jQuery-File-Upload/js/vendor/jquery.ui.widget.js') }}"></script>
    <script src="{{ asset('assets/js/jQuery-File-Upload/js/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('assets/js/jQuery-File-Upload/js/jquery.fileupload.js') }}"></script>
@endsection
