@extends('layouts.master')

@section('title',__('app.smallBox'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/chosen.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
@endsection

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
                        <a href="<?php URL::to('box'); ?>">@lang('app.smallBox')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.smallBox')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                        <button type="button" class="btn btn-sm btn-success" id="nuevo_mod" data-toggle="modal"
                                data-target="#add"><i class="icon-plus"></i> @lang('app.new')</button>

                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">

                            <div class="col-xl-4 col-lg-4 col-12">
                                <div class="card pull-up">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="verde_color ajusth3" id="ing">@lang('app.loading')
                                                        ...</h3>
                                                    <h6>@lang('app.income')</h6>
                                                </div>
                                                <div>
                                                    <i
                                                            class="la la-cloud-download verde_color font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-12">
                                <div class="card pull-up">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="color_red ajusth3" id="egr">@lang('app.loading') ...</h3>
                                                    <h6>@lang('app.expenses')</h6>
                                                </div>
                                                <div>
                                                    <i
                                                            class="la la-cloud-upload color_red font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-12">
                                <div class="card pull-up">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="info ajusth3" id="sal">@lang('app.loading') ...</h3>
                                                    <h6>@lang('app.balance')</h6>
                                                </div>
                                                <div>
                                                    <i class="la la-money info font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" role="tablist" id="myTab">
                            <li role="presentation" class="active">
                                <a href="#outputs" aria-controls="outputs" role="tab" data-toggle="tab">
                                    @lang('app.expenses')
                                </a>
                            </li>
                        </ul>
                        <!--head endtab-->
                        <!--tab content-->
                        <div class="tab-content">

                        <!--end tab home-->
                            <div role="tabpanel" class="tab-pane active" id="outputs">
                                <!--inicio tab egresos-->
                                <!--inicio tabla egresos-->
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.all') @lang('app.expenses')</h5>
                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>
                                                <ul
                                                        class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                                    <li>
                                                        <a href="#" data-toggle="modal" class="peref"
                                                           data-target="#add"><i
                                                                    class="fa fa-plus-circle"></i> @lang('app.add')
                                                            @lang('app.registry')</a></li>
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
                                <!--fin de tabla egresos-->
                            </div>
                            <!--fin del tab egresos-->
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span
                                            class="sr-only">@lang('app.close')</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa fa-money"></i>
                                    @lang('app.add') @lang('app.new') @lang('app.registry')</h4>
                            </div>
                            <div class="modal-body" id="winnew">
                                <form class="form-horizontal" id="formaddreg">
                                    <div class="form-group">
                                        <label for="name"
                                               class="col-sm-4 control-label">@lang('app.kindOf') @lang('app.operation')</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="type" id="type">
                                                {{-- <option value="in">Ingreso</option> --}}
                                                <option value="out">@lang('app.expenses')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group" id="clt">
                                        <label for="clients" class="col-sm-4 control-label">@lang('app.clients')</label>
                                        <div class="col-sm-8">
                                            <select class="chosen-select form-control" id="clients" name="client"
                                                    data-placeholder="@lang('app.selectCustomer')">
                                                <option value=""></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group" id="crouter">
                                        <label for="edit_slcrouter"
                                               class="col-sm-4 control-label">@lang('app.router')</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="router" id="slcrouter">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group" id="so">
                                        <label for="numr"
                                               class="col-sm-4 control-label">@lang('app.businessName')</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="social" class="form-control" id="soi"
                                                   maxlength="50">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="id-date-picker-1"
                                               class="col-sm-4 control-label">@lang('app.date')</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input class="form-control" maxlength="8" type="text" id="date_reg"
                                                       name="date_reg" data-date-format="dd-mm-yyyy" readonly required/>
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar bigger-110"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="numr"
                                               class="col-sm-4 control-label">@lang('app.noOf') @lang('app.voucher')</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="numr" class="form-control" id="numr"
                                                   maxlength="25">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount"
                                               class="col-sm-4 control-label">@lang('app.totalAmount')</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="amount" class="form-control" id="amount"
                                                   maxlength="10">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="detail"
                                               class="col-sm-4 control-label">@lang('app.detailOf') @lang('app.operation')</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="detail" rows="4"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="copy" class="col-sm-3 control-label">
                                            <p class="text-success"><i class="fa fa-files-o"></i> @lang('app.copy')</p>
                                        </label>
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
                                        data-dismiss="modal">@lang('app.close')</button>
                                <button type="button" class="btn btn-primary" id="addbtreg"
                                        data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                            class="fa fa-floppy-o"></i>
                                    @lang('app.save')</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span
                                            class="sr-only">@lang('app.close')</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span
                                            id="load"><i
                                                class="fa fa-cog fa-spin"></i> @lang('app.loading')</span> @lang('app.edit') @lang('app.registry')
                                </h4>
                            </div>
                            <div class="modal-body" id="winedit">
                                <form class="form-horizontal" id="RegformEdit">
                                    <div class="form-group">
                                        <label for="edit_name"
                                               class="col-sm-4 control-label">@lang('app.kindOf') @lang('app.operation')</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="edit_type" id="edit_type">
                                                <option value="in">@lang('app.income')</option>
                                                <option value="out">@lang('app.expenses')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_clients"
                                               class="col-sm-4 control-label">@lang('app.clients')</label>
                                        <div class="col-sm-8">
                                            <select class="chosen-select form-control" id="edit_clients"
                                                    name="edit_client" data-placeholder="@lang('app.selectCustomer')">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_numr"
                                               class="col-sm-4 control-label">@lang('app.noOf') @lang('app.voucher')</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="edit_numr" class="form-control" id="edit_numr"
                                                   maxlength="25">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_amount"
                                               class="col-sm-4 control-label">@lang('app.totalAmount')</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="edit_amount" class="form-control" id="edit_amount"
                                                   maxlength="10">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_detail" class="col-sm-4 control-label">@lang('app.detailOf')
                                            @lang('app.operation')</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" name="edit_detail" id="edit_detail"
                                                      rows="4"></textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="reg_id">
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">@lang('app.close')</button>
                                <button type="button" class="btn btn-primary" id="editbtnreg"
                                        data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                            class="fa fa-floppy-o"></i>
                                    @lang('app.save')</button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layouts.modals')
            </div>
        </div>
    </div>
    <input id="val" type="hidden" name="register" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/box-core.js')}}"></script>
    {!! $dataTable->scripts() !!}
@endsection
