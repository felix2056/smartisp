@extends('layouts.master')

@section('title',__('app.templates'))

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="<?php echo URL::to('admin'); ?>">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::to('templates'); ?>">@lang('app.templates')</a>
                    </li>
                    <li class="active">@lang('app.listado')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.templates')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.listado')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.allTheTemplates')</h5>
                                        <div class="widget-toolbar">

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
                            <div class="modal fade" id="addadv" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                        aria-hidden="true">&times;</span><span
                                                        class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-bullhorn"></i>
                                                @lang('app.sendNewNotice')</h4>
                                        </div>
                                        <div class="modal-body" id="winnew">
                                            <form class="form-horizontal" id="sendnewadv">
                                                <div class="form-group">
                                                    <label for="slcrouter"
                                                           class="col-sm-2 control-label">@lang('app.router')</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" name="router_id"
                                                                id="slcrouter"></select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name_adv"
                                                           class="col-sm-2 control-label">@lang('app.name')</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="name"
                                                               id="name_adv">
                                                    </div>
                                                </div>
                                                <div class="form-group" id="seltype">
                                                    <label for="slctype"
                                                           class="col-sm-2 control-label">@lang('app.type')</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" id="slctype" name="typetem">
                                                            <option value="none"
                                                                    selected>@lang('app.selectTheTypeOfNotice')</option>
                                                            <option value="screen">@lang('app.onScreenNotice')</option>
                                                            <option value="html">@lang('app.html')</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group" id="sltemplate">
                                                    <label for="type_temp"
                                                           class="col-sm-2 control-label">@lang('app.template')</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" id="type_temp"
                                                                name="template"></select>
                                                        <br>
                                                        <a href="" class="btn btn-success btn-xs" id="btnpreview"
                                                           target="_blank"><i class="fa fa-desktop"></i>
                                                            @lang('app.preview')</a>
                                                    </div>
                                                </div>
                                                <div class="form-group" id="lsclient">
                                                    <label for="ms"
                                                           class="col-sm-2 control-label">@lang('app.sendTo')</label>
                                                    <div class="col-sm-10">
                                                        <select id="ms" multiple class="multiselect"
                                                                name="clients[]"></select>
                                                    </div>
                                                </div>
                                                <div class="form-group" id="timesh">
                                                    <label for="timepicker1"
                                                           class="col-sm-2 control-label">@lang('app.end')</label>
                                                    <div class="col-sm-10">
                                                        <div class="input-group">
                                                            <input id="timepicker1" name="time" type="text"
                                                                   class="form-control" readonly required/>
                                                            <span class="input-group-addon">
																<i class="fa fa-clock-o bigger-110"></i>
															</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">@lang('app.close')</button>
                                            <button type="button" class="btn btn-primary" id="sendbtn"
                                                    data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                                        class="fa fa-share-square"></i>
                                                @lang('app.send')</button>
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
    <input id="val" type="hidden" name="register" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/templates-core.js')}}"></script>
    {!! $dataTable->scripts() !!}
@endsection
