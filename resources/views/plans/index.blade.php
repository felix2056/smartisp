@extends('layouts.master')

@section('title', __('app.plans'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <style type="text/css" media="screen">
        .negro_c {
            color: #000 !important;
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
                        <a href="<?php echo URL::to('plans'); ?>">{{ __('app.plans') }}</a>
                    </li>
                    <li class="active">{{ __('app.listado') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.plans') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.listado') }}
                        </small>
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#add"><i
                                class="icon-plus"></i> {{ __('app.add') }} {{ __('app.plan') }}</button>

                    </h1>
                </div>
                <!--start row-->
                <div class="row">
                    <div class="col-sm-12">
                        <!--Inicio tabla planes simple queues-->
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">{{ __('app.all') }} {{ __('app.plans') }}</h5>
                                <div class="widget-toolbar">
                                    <div class="widget-menu">
                                        <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                            <i class="ace-icon fa fa-bars"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                            <li>
                                                <a href="#" data-toggle="modal" class="peref" data-target="#add"><i
                                                        class="fa fa-plus-circle"></i> {{ __('app.new') }} {{ __('app.plan') }}
                                                </a></li>

                                        </ul>
                                    </div>
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
                                    <!--Contenido widget-->
                                    <div class="table-responsive">
                                        {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--Fin tabla planes simple queues-->
                    </div><!--end col-->
                </div>
                <!--end row-->

                <!---------------------Inicio de Modals------------------------------->

                <!--Incio modal añadir plan-->
                <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                        class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-tachometer"></i>
                                    {{ __('app.add') }} {{ __('app.new') }} {{ __('app.plan') }}</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal" role="form" id="formaddplan">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-3 control-label">{{ __('app.title') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="title" class="form-control" id="titlepl"
                                                   maxlength="30">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name"
                                               class="col-sm-3 control-label">{{ __('app.serviceName') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="name" class="form-control" id="namepl"
                                                   maxlength="30">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="download"
                                               class="col-sm-3 control-label">{{ __('app.discharge') }}</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" name="download" class="form-control download"
                                                       id="donwload" maxlength="7" placeholder="0">
                                                <span class="input-group-addon">
															Kbps
														</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="upload" class="col-sm-3 control-label">{{ __('app.rise') }}</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" name="upload" class="form-control upload" id="upload"
                                                       maxlength="7" placeholder="0">
                                                <span class="input-group-addon">
															Kbps
														</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="price" class="col-sm-3 control-label">{{ __('app.cost') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="cost" class="form-control enteros" id="price"
                                                   maxlength="11" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="iva" class="col-sm-3 control-label">IVA %</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="iva" class="form-control enteros" id="iva"
                                                   maxlength="6" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="iva" class="col-sm-3 control-label">{{ __('app.noRules') }}</label>
                                        <div class="col-sm-9">
                                            <label><input
                                                        id="no_rules"
                                                        name="no_rules"
                                                        value="1"
                                                        class="ace ace-switch ace-switch-6"
                                                        type="checkbox"/>
                                                <span class="lbl"></span>
                                            </label>
                                        </div>
                                    </div>

                                    {{--<div class="form-group">--}}
                                        {{--<label for="address_list_name" class="col-sm-3 control-label">{{ __('app.addressList') }}</label>--}}
                                        {{--<div class="col-sm-9">--}}
                                            {{--<input type="text" name="address_list_name" class="form-control" id="address_list_name">--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div id="accordion" class="accordion-style1 panel-group">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle collapsed" data-toggle="collapse"
                                                       data-parent="#accordion" href="#collapseThree">
                                                        <i class="ace-icon fa fa-angle-right bigger-110"
                                                           data-icon-hide="ace-icon fa fa-angle-down"
                                                           data-icon-show="ace-icon fa fa-angle-right"></i>
                                                        &nbsp;{{ __('app.advanced') }}
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="panel-collapse collapse" id="collapseThree">
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label for="limitat"
                                                               class="col-sm-5 control-label">{{ __('app.speed') }} {{ __('app.guaranteed') }}
                                                            Limit At</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="limitat" class="form-control"
                                                                       id="limitat" value="100">
                                                                <span class="input-group-addon">
																			%
																		</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="priority"
                                                               class="col-sm-5 control-label">{{ __('app.priority') }}</label>
                                                        <div class="col-sm-7">
                                                            <select class="form-control" name="priority" id="priority">
                                                                <option value="8">{{ __('app.low') }}</option>
                                                                <option value="5" selected>Normal</option>
                                                                <option value="1">{{ __('app.high') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="aggregation"
                                                               class="col-sm-5 control-label">{{ __('app.aggregation') }}</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
																		<span class="input-group-addon">
																			1:
																		</span>
                                                                <input type="text" name="aggregation"
                                                                       class="form-control" id="aggregation" value="1">

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="" class="col-sm-5 control-label">Burst Limit</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="bl" class="form-control"
                                                                       id="bursld" value="0">
                                                                <span class="input-group-addon">
																			%
																		</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="btd" class="col-sm-5 control-label">Burst
                                                            Threshold</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="bth" class="form-control"
                                                                       id="burstdo" value="0">
                                                                <span class="input-group-addon">
																			%
																		</span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="form-group">
                                                        <label for="" class="col-sm-5 control-label">Burst Time</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="bt" class="form-control"
                                                                       id="bursttim" value="0">
                                                                <span class="input-group-addon">
																			Seg
																		</span>
                                                            </div>
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="regpay" class="col-sm-3 control-label"><p class="text-success"><i
                                                    class="fa fa-files-o"></i> {{ __('app.copy') }}</p></label>
                                        <div class="col-sm-9">
                                            <div class="checkbox">
                                                <label>
                                                    <input name="copy" type="checkbox" class="ace" id="copy"/>
                                                    <span class="lbl"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}</button>
                                <button type="button" class="btn btn-primary" id="addbtnplan"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Fin de modal añadir plan-->


                <!--Incio modal smart bandwidth-->
                <div class="modal fade" id="smartb" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                        class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-cogs"></i>
                                    Configuración de {{ __('app.plan') }}</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal" role="form" id="formsb">

                                    <div id="accordion2" class="accordion-style1 panel-group">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle collapsed" data-toggle="collapse"
                                                       data-parent="#accordion2" href="#collapseThree2">
                                                        <i class="ace-icon fa fa-angle-right bigger-110"
                                                           data-icon-hide="ace-icon fa fa-angle-down"
                                                           data-icon-show="ace-icon fa fa-angle-right"></i>
                                                        &nbsp;{{ __('app.extraSpeed') }}
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="panel-collapse collapse" id="collapseThree2">
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label for="act_ser"
                                                               class="col-sm-5 control-label">{{ __('app.activate') }} {{ __('app.service') }}</label>
                                                        <div class="col-sm-7">
                                                            <select class="form-control" name="act_ser" id="act_ser">
                                                                <option value="d"
                                                                        selected>{{ __('app.diary') }}</option>
                                                                <option value="w">{{ __('app.weekly') }}</option>

                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group" id="swdays">
                                                        <label
                                                            class="control-label col-xs-10 col-sm-5 no-padding-right"> {{ __('app.days') }}
                                                            &nbsp;&nbsp;&nbsp; </label>

                                                        <div class="col-xs-12 col-sm-7">
                                                            <select multiple="multiple" id="state" name="days[]"
                                                                    class="select2" data-placeholder="Clic para elegir">
                                                            </select>

                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="stcl"
                                                               class="col-sm-5 control-label">{{ __('app.since') }}</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="stcl"
                                                                       class="form-control timepicker" id="stcl"
                                                                       readonly>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="encl"
                                                               class="col-sm-5 control-label">{{ __('app.until') }}</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="encl"
                                                                       class="form-control timepicker2" id="encl"
                                                                       readonly>
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="plan_id_sb">

                                                    <div class="form-group">
                                                        <label for="speedx"
                                                               class="col-sm-5 control-label">{{ __('app.speed') }}</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="speedx" class="form-control"
                                                                       maxlength="3" id="speedx" value="0">
                                                                <span class="input-group-addon">
                                                                    %
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}</button>
                                <button type="button" class="btn btn-primary" id="editbtnsb"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Fin de modal añadir plan-->


                <!--Inicio de modal editar plan -->
                <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                        class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i>
                                    <span
                                        id="load2"><i class="fa fa-cog fa-spin"></i> @lang('app.loading')
                                    </span> @lang('app.edit') @lang('app.plan')
                                </h4>
                            </div>
                            <div class="modal-body" id="winedit">
                                <form class="form-horizontal" role="form" id="PlanformEdit">

                                    <div class="form-group">
                                        <label for="name" class="col-sm-3 control-label">@lang('app.title')</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="edit_title" class="form-control" id="edit_title"
                                                   maxlength="30">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-sm-3 control-label">@lang('app.serviceName')</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="edit_name" class="form-control" id="edit_name"
                                                   maxlength="30">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="download"
                                               class="col-sm-3 control-label">{{ __('app.discharge') }}</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" name="edit_download" class="form-control download"
                                                       id="edit_donwload" maxlength="6" placeholder="0">
                                                <span class="input-group-addon">
                                                    Kbps
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="upload" class="col-sm-3 control-label">{{ __('app.rise') }}</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <input type="text" name="edit_upload" class="form-control upload"
                                                       id="edit_upload" maxlength="6" placeholder="0">
                                                <span class="input-group-addon">
                                                    Kbps
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="price" class="col-sm-3 control-label">{{ __('app.cost') }}</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="edit_cost" class="form-control enteros"
                                                   id="edit_price" maxlength="11" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="iva" class="col-sm-3 control-label">IVA %</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="edit_iva" class="form-control enteros"
                                                   id="edit_iva" maxlength="6" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="iva" class="col-sm-3 control-label">{{ __('app.noRules') }}</label>
                                        <div class="col-sm-9">
                                            <label>
                                                <input
                                                    id="no_rules"
                                                    name="no_rules"
                                                    value="1"
                                                    class="ace ace-switch ace-switch-6"
                                                    type="checkbox"
                                                />
                                                <span class="lbl"></span>
                                            </label>
                                        </div>
                                    </div>

                                    {{--<div class="form-group">--}}
                                        {{--<label for="address_list_name" class="col-sm-3 control-label">{{ __('app.addressList') }}</label>--}}
                                        {{--<div class="col-sm-9">--}}
                                            {{--<input type="text" name="address_list_name" class="form-control" id="address_list_name">--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div id="accordion2" class="accordion-style1 panel-group">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle collapsed" data-toggle="collapse"
                                                       data-parent="#accordion2" href="#advedit">
                                                        <i class="ace-icon fa fa-angle-right bigger-110"
                                                           data-icon-hide="ace-icon fa fa-angle-down"
                                                           data-icon-show="ace-icon fa fa-angle-right"></i>
                                                        &nbsp;{{ __('app.advanced') }}
                                                    </a>
                                                </h4>
                                            </div>
                                            <div class="panel-collapse collapse" id="advedit">
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label for="editlimitat"
                                                               class="col-sm-5 control-label">{{ __('app.speed') }} {{ __('app.guaranteed') }}
                                                            Limit At</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="edit_limitat"
                                                                       class="form-control" id="editlimitat"
                                                                       value="100">
                                                                <span class="input-group-addon">
                                                                    %
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="editpriority"
                                                               class="col-sm-5 control-label">{{ __('app.priority') }}</label>
                                                        <div class="col-sm-7">
                                                            <select class="form-control" name="edit_priority"
                                                                    id="editpriority">
                                                                <option value="8">{{ __('app.low') }}</option>
                                                                <option value="5">Normal</option>
                                                                <option value="1">{{ __('app.high') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="editaggregation" class="col-sm-5 control-label">Agregación</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    1:
                                                                </span>
                                                                <input type="text" name="edit_aggregation"
                                                                       class="form-control" id="editaggregation">

                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="editbursld" class="col-sm-5 control-label">Burst Limit</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="edit_bl" class="form-control"
                                                                       id="bursld" value="0">
                                                                <span class="input-group-addon">
                                                                    %
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="plan_id">
                                                    <div class="form-group">
                                                        <label for="editbtd" class="col-sm-5 control-label">Burst
                                                            Threshold
                                                        </label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="edit_bth" class="form-control"
                                                                       id="editbtd" value="0">
                                                                <span class="input-group-addon">
                                                                    %
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="editbursttim" class="col-sm-5 control-label">Burst
                                                            Time</label>
                                                        <div class="col-sm-7">
                                                            <div class="input-group">
                                                                <input type="text" name="edit_bt" class="form-control"
                                                                       id="editbursttim" value="0">
                                                                <span class="input-group-addon">
                                                                    Seg
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}
                                </button>

                                <button type="button" class="btn btn-primary" id="editbtnplan"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.modals')
            </div>
        </div>
    </div>
    <input id="val" type="hidden" name="plan" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    {!! $dataTable->scripts() !!}
    <script src="{{asset('assets/js/rocket/plans-core.js')}}"></script>
@endsection
