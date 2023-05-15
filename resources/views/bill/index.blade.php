@extends('layouts.master')

@section('title',__('app.payments'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
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
                        <a href="<?php echo URL::to('bill'); ?>">@lang('app.payments')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.payments')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add"><i
                                    class="icon-plus"></i> @lang('app.new')</button>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">Todos los pagos</h5>
                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                                    <li>
                                                        <a href="#" data-toggle="modal" class="peref"
                                                           data-target="#add"><i class="fa fa-plus-circle"></i> Añadir
                                                            pago</a>
                                                    </li>
                                                </ul>
                                            </div>
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
                                                <table id="payments-table" class="table table-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th>@lang('app.client')</th>
                                                        <th>Periodo facturado</th>
                                                        <th>Monto pagado</th>
                                                        <th>Nº de Factura</th>
                                                        <th>@lang('app.paymentDate')</th>
                                                        <th>Plan</th>
                                                        <th>Servidor</th>
                                                        <th>@lang('app.operations')</th>
                                                    </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
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
                                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>
                                                Añadir @lang('app.new') pago</h4>
                                        </div>
                                        <div class="modal-body">
                                            <form class="form-horizontal" method="post"
                                                  action="{{URL::to('bill/print')}}" target="_blank" id="formaddpay"
                                                  autocomplete="off">

                                                <div class="form-group">
                                                    <label for="name" class="col-sm-3 control-label">Buscar por</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="filter" name="filter">
                                                            <option value="name" selected>@lang('app.name')</option>
                                                            <option value="dni">Número de CI/DNI</option>
                                                            <option value="phone">@lang('app.telephone')</option>
                                                            <option value="ip">Dirección IP</option>
                                                            <option value="mac">Dirección MAC</option>
                                                            <option value="email">Email</option>

                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="name" class="col-sm-3 control-label"
                                                           id="lbsearch"></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" autocomplete="off"
                                                               id="name" maxlength="40">
                                                    </div>
                                                </div>

                                                <div class="form-group" id="swexpi">
                                                    <label for="expiring"
                                                           class="col-sm-3 control-label">Vencimiento</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" name="expiring_date"
                                                               id="expiring" maxlength="20" readonly>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="swamount">
                                                    <label for="amount" class="col-sm-3 control-label">monto
                                                        pagado</label>
                                                    <div class="col-sm-9">
                                                        <input type="email" class="form-control" id="amount"
                                                               maxlength="30" disabled>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="swnbill">
                                                    <label for="nbill"
                                                           class="col-sm-3 control-label">@lang('app.billNumber')</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" name="nbill" id="nbill"
                                                               maxlength="12" readonly>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="swtotalpay">
                                                    <label for="numpays" class="col-sm-3 control-label">Meses
                                                        pagados</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" name="numpays" class="input-mini"
                                                               id="numpays" readonly/>
                                                        <div class="space-6"></div>
                                                    </div>
                                                </div>

                                                <div class="form-group" id="swplan">
                                                    <label for="plan" class="col-sm-3 control-label">Plan</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="plan" maxlength="25"
                                                               readonly>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="client_id" id="client_id">
                                                <div class="form-group" id="swrouter">
                                                    <label for="router" class="col-sm-3 control-label">Router</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control" id="router"
                                                               maxlength="25" readonly>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="modal-footer" id="swfoot">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">@lang('app.close')</button>
                                            <button type="submit" class="btn btn-success" id="addbtnpritn"
                                                    data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                                        class="fa fa-print"></i>
                                                Guardar e imprimir
                                            </button>
                                            <button type="button" class="btn btn-primary" id="addbtnpay"
                                                    data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                                        class="fa fa-floppy-o"></i>
                                                @lang('app.save')</button>
                                        </div>
                                        </form>
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
    <input id="val" type="hidden" name="client" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/fuelux/fuelux.spinner.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-typeahead.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/bills-core.js')}}"></script>
@endsection
