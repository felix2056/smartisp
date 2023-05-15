@extends('layouts.master')

@section('title', __('app.tools'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <!-- page specific plugin styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-duallistbox.min.css') }}"/>

    <style>
        .input-group-addon {
            padding: .55rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="<?php echo URL::to('admin'); ?>">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="#">@lang('app.system')</a>
                    </li>
                    <li class="active">@lang('app.tools')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.tools')
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                                <li class="active">
                                    <a data-toggle="tab" href="#faq-tab-1">
                                        <i class="ace-icon la la-users bigger-120"></i>
                                        @lang('app.clients')
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#toolsTabTestEmail">

                                        <i class="ace-icon la la-cog bigger-120"></i>
                                        @lang('app.system')
                                    </a>
                                </li>
                                {{--<li>--}}
                                    {{--<a data-toggle="tab" href="#faq-tab-5" id="plsm">--}}
                                        {{--<i class="ace-icon fa fa-tachometer bigger-120"></i>--}}
                                        {{--@lang('app.Profiles')--}}
                                    {{--</a>--}}
                                {{--</li>--}}
                            </ul>
                            <div class="tab-content no-border padding-24">
                                <div id="faq-tab-1" class="tab-pane fade in active">
                                    <div class="space-8"></div>

                                    <div id="faq-list-1" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-1-1" data-parent="#faq-list-1" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-user-plus bigger-130"></i>
                                                    &nbsp; @lang('app.ImportClients')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-1-1">
                                                <div class="panel-body">
                                                    <div class="col-xs-12">
                                                        <form class="form-horizontal" id="formImport"
                                                              enctype="multipart/form-data">
                                                            <div class="form-group">
                                                                <label for="inputEmail3" class="col-sm-3 control-label">Router</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control" name="router"
                                                                            id="slcrouter">
                                                                        <option value="">Seleccione Router</option>
                                                                        @foreach($routers as $router)
                                                                            <option value="{{ $router->id }}">{{ $router->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="typeimp">
                                                                <label for="inputPassword3"
                                                                       class="col-sm-3 control-label">@lang('app.controlType')</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control" name="control"
                                                                            id="lstypes"></select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="inputEmail3" class="col-sm-3 control-label">Plans</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control" name="plan_id"
                                                                            id="plan_id">
                                                                        @foreach($allPlans as $plan)
                                                                            <option value="{{$plan->id}}">{{ $plan->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label col-sm-3">
                                                                    @lang('app.billingDay')
                                                                </label>

                                                                <div class="col-sm-9">
                                                                    <select type="select" id="billing_day"
                                                                            style="width: 100%;" original-value="1"
                                                                            force-send="0"
                                                                            class="select2 select2-hidden-accessible"
                                                                            name="billing_day" tabindex="-1"
                                                                            aria-hidden="true">
                                                                        @for ($i = 1; $i < 32; $i++)
                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label col-sm-3">
                                                                    @lang('app.billingDue')
                                                                </label>

                                                                <div class="col-sm-9">
                                                                    <select type="select" id="billing_due"
                                                                            style="width: 100%;" original-value="15"
                                                                            force-send="0"
                                                                            class="select2 select2-hidden-accessible"
                                                                            name="billing_due" tabindex="-1"
                                                                            aria-hidden="true">
                                                                        @for ($i = 1; $i < 32; $i++)
                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                        @endfor
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label col-sm-3">
                                                                    @lang('app.invoicePayType')
                                                                </label>

                                                                <div class="col-sm-9">
                                                                    <select type="select" id="invoice_pay_type"
                                                                            style="width: 100%;" original-value="15"
                                                                            force-send="0"
                                                                            class="select2 select2-hidden-accessible"
                                                                            name="invoice_pay_type" tabindex="-1"
                                                                            aria-hidden="true">
                                                                        <option value="prepay">{{ __('app.prepay') }}</option>
                                                                        <option value="postpay" >{{ __('app.postpay') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="id-date-picker-1"
                                                                       class="col-sm-3 control-label">@lang('app.chooseFile')</label>
                                                                <div class="col-sm-9">
                                                                    <div class="input-group">
                                                                        <input class="form-control" id="file"
                                                                               name="file" type="file">
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <hr>
                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <a href="{{ asset('sample/importar_SmartISP.xlsx') }}"
                                                                       target="_blank"
                                                                       class="btn btn-success m-l-10">@lang('app.downloadSample')</a>
                                                                    <button type="button" id="btnImport"
                                                                            class="btn btn-success">@lang('app.import')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div id="faq-tab-5" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon fa fa-tachometer bigger-120"></i>
                                        @lang('app.Profiles')
                                    </h4>
                                    <div class="space-8"></div>

                                    <div id="faq-list-5" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-5-1" data-parent="#faq-list-5" data-toggle="collapse"
                                                   class="accordion-toggle collapsed" id="">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                    <i class="ace-icon fa fa-server bigger-130"></i>
                                                    &nbsp; @lang('app.Importmikrotikprofiles')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-5-1">
                                                <div class="panel-body">
                                                    <p>@lang('app.HereyoucanimportHotspotandPPPoE').</p>

                                                    <form class="form-horizontal" id="formprofile">
                                                        <div class="form-group">
                                                            <label for="CRouter"
                                                                   class="col-sm-2 control-label">Router</label>
                                                            <div class="col-sm-8">
                                                                <select class="form-control" name="routerid"
                                                                        id="CRouter"></select>
                                                            </div>
                                                        </div>


                                                        <div class="form-group" id="swprifiles">
                                                            <label class="col-sm-2 control-label no-padding-top"
                                                                   for="duallist"> @lang('app.Profiles') </label>

                                                            <div class="col-sm-8">
                                                                <select multiple="multiple" size="10"
                                                                        name="profileslistbox[]" id="duallist">

                                                                </select>

                                                                <div class="hr hr-16 hr-dotted"></div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-sm-offset-2 col-sm-10">
                                                                <button type="button" id="btnimportprofile"
                                                                        class="btn btn-primary"></button>
                                                            </div>
                                                        </div>
                                                    </form>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div id="toolsTabTestEmail" class="tab-pane fade">

                                    <h4 class="title_con">
                                        <i class="ace-icon la la-cog bigger-120"></i>
                                        @lang('app.system')
                                    </h4>

                                    <div class="space-8"></div>

                                    <div id="faq-list-2" class="panel-group accordion-style1 accordion-style2">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-2-1" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>&nbsp;
                                                    @lang('app.TestsendingSMTPemail')
                                                </a>
                                            </div>
                                            <div class="panel-collapse collapse" id="faq-2-1">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-horizontal" id="formemail">
                                                            <div class="form-group">
                                                                <label for="inputEmail3"
                                                                       class="col-sm-2 control-label">@lang('app.For')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="email" name="email"
                                                                           class="form-control" id="inputEmail3"
                                                                           maxlength="60"
                                                                           placeholder="ejemplo@ejemplo.com">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="menssage"
                                                                       class="col-sm-2 control-label">@lang('app.affair')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="subje" class="form-control"
                                                                           id="subje" maxlength="30"
                                                                           value="Prueba de envío de email">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <button type="button" class="btn btn-success"
                                                                            id="bdtsend">@lang('app.send')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{--<div class="panel panel-default">
                                            <div class="panel-heading">
                                                <a href="#faq-2-2" data-parent="#faq-list-2" data-toggle="collapse"
                                                   class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right"
                                                       data-icon-hide="ace-icon fa fa-chevron-down"
                                                       data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    &nbsp; @lang('app.SMSsendintest')
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-2-2">
                                                <div class="panel-body">
                                                    <div class="col-xs-6">
                                                        <form class="form-horizontal" id="formsms">
                                                            <div class="form-group">
                                                                <label for="inputphone"
                                                                       class="col-sm-2 control-label">@lang('app.For')
                                                                    Nº</label>
                                                                <div class="col-sm-10">
                                                                    <input type="tel" name="phone" class="form-control"
                                                                           id="inputphone">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="message"
                                                                       class="col-sm-2 control-label">@lang('app.message')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="message"
                                                                           class="form-control" id="message"
                                                                           maxlength="160"
                                                                           value="Prueba de envio de sms desde SmartISP">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <button type="button" class="btn btn-success"
                                                                            id="btnsendsms">@lang('app.send')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>--}}


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
@endsection

@section('scripts')
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/date-time/locales/bootstrap-datepicker.es.js')}}"></script>
    <script src="{{asset('assets/js/jquery.bootstrap-duallistbox.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/tools-core.js')}}"></script>
@endsection
