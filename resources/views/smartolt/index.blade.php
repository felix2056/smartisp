@extends('layouts.master')

@section('title', __('app.smartolt'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <style type="text/css" media="screen">
        .tab-content {
            background: #fff !important;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}"/>
@endsection

@section('content')

    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="<?php echo URL::to('admin'); ?>">{{ __('app.desk') }}</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::to('smartolt'); ?>">{{ __('app.smartolt') }}</a>
                    </li>
                    <li class="active">{{ __('app.listado') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.smartolt') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.listado') }}
                        </small>
                    <!--                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#add"><i
                                class="icon-plus"></i> {{ __('app.add') }} {{ __('app.smartolt') }}
                        </button>-->

                    </h1>
                </div>
                @if (session('smart_olt_error'))
                    <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2"
                         role="alert" style="color: #fff; background: #ff4961;">
                        <span class="alert-icon"><i class="la la-info-circle"></i></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true" style="color: #000">×</span>
                        </button>
                        {{ session('smart_olt_error') }}
                    </div>
                @endif
                @if (session('smart_olt_success'))
                    <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2"
                         role="alert" style="color: #fff; background: #127707;">
                        <span class="alert-icon"><i class="la la-info-circle"></i></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true" style="color: #000">×</span>
                        </button>
                        {{ session('smart_olt_success') }}
                    </div>
                @endif

                <!--start row-->
                <div class="row">
                    <div class="col-sm-12">
                        <!--Inicio tabla planes simple queues-->
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title"> {{ __('app.smartolt') }}</h5>
                                <div class="widget-toolbar">
                                    <a data-action="reload" href="#" class="recargar white"><i
                                            class="ace-icon fa fa-refresh"></i></a>
                                    <a data-action="fullscreen" class="white" href="#"><i
                                            class="ace-icon fa fa-expand"></i></a>
                                    <a data-action="collapse" href="#" class="white"><i
                                            class="ace-icon fa fa-chevron-up"></i></a>
                                </div>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="tabbable">
                                                <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab1">
                                                    <li class="active">
                                                        <a data-toggle="tab" href="#olt_tab">
                                                            @lang('app.olts')
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-toggle="tab" href="#zona_tab" >
                                                            @lang('app.zonas')
                                                        </a>
                                                    </li>
                                                </ul>

                                                <div class="tab-content no-border padding-24">
                                                    <div id="olt_tab" class="tab-pane fade in active">
                                                        @include('smartolt.olt_list')
                                                    </div>
                                                    <div id="zona_tab" class="tab-pane fade in">
                                                        @include('smartolt.zonas_list')
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!--Fin tabla planes simple queues-->
                    </div><!--end col-->
                </div>
                <!--end row-->

                @include('layouts.modals')
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script>
        $('#olt_list').dataTable({
            "oLanguage": {
                "sUrl": '{{ asset(__('app.datatable')) }}'
            },
            processing: true,
            responsive:true,
            pageLength: '10',
            destroy: true,
            order: [
                '0', 'desc'
            ],
        });

        $('#zones_list').dataTable({
            "oLanguage": {
                "sUrl": '{{ asset(__('app.datatable')) }}'
            },
            processing: true,
            responsive:true,
            pageLength: '10',
            destroy: true,
            order: [
                '0', 'desc'
            ],
        });
    </script>

    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
@endsection

