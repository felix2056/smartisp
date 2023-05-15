@extends('layouts.master')

@section('title',__('app.routers'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
   integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
   crossorigin=""/>
    <style>
        .pac-container {
            z-index: 99999;
        }
        .tab-pane {
            background: white;
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
                        <a href="<?php echo URL::to('admin'); ?>">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::to('routers'); ?>">@lang('app.routers')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.routers')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#add"><i
                                class="icon-plus"></i> @lang('app.new')</button>

    {{--                            <a href="{{ route('admin.router-status') }}" class="btn btn-sm btn-success" style="margin-right:20px;">--}}
    {{--                                <i class="icon-plus"></i>--}}
    {{--                                Check Status--}}
    {{--                            </a>--}}
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.all') @lang('app.routers')</h5>
                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown"
                                                   class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                                    <li>
                                                        <a href="#" data-toggle="modal" class="peref"
                                                           data-target="#add"><i
                                                                class="fa fa-plus-circle"></i> @lang('app.add') @lang('app.router')
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
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
                            <div class="modal fade" id="info-router" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i
                                                    class="fa fa-info-circle"></i>
                                                @lang('app.routerInformation')</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div role="tabpanel">
                                                <!-- Nav tabs -->
                                                <ul class="nav nav-tabs" role="tablist" id="tbs">
                                                    <li role="presentation" class="active"><a href="#inf-ro"
                                                                                              aria-controls="inf-ro"
                                                                                              id="ifrouter"
                                                                                              role="tab"
                                                                                              data-toggle="tab"><i
                                                                class="fa fa-hdd-o"></i> @lang('app.infoRouter')</a>
                                                    </li>
                                                    <li role="presentation"><a href="#logs" id="lgs"
                                                                               aria-controls="logs" role="tab"
                                                                               data-toggle="tab"><i
                                                                class="fa fa-list-ul"></i> @lang('app.logs')</a>
                                                    </li>
                                                    <li role="presentation"><a href="#tlan" aria-controls="tlan"
                                                                               id="iflan" role="tab"
                                                                               data-toggle="tab"><i
                                                                class="fa fa-bar-chart"></i> @lang('app.lan')</a>
                                                    </li>
                                                </ul>
                                                <!-- Tab panes -->
                                                <div class="tab-content" id="mytab">
                                                    <div role="tabpanel" class="tab-pane active" id="inf-ro">
                                                        <!--inicio info-->
                                                        <div class="profile-user-info profile-user-info-striped">
                                                            <div class="profile-info-row">
                                                                <div
                                                                    class="profile-info-name"> @lang('app.router') </div>
                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="hardware"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div class="profile-info-name">RouterOS</div>
                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="os"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div
                                                                    class="profile-info-name"> @lang('app.active') </div>

                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="active"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div class="profile-info-name">@lang('app.load')
                                                                    CPU
                                                                </div>

                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="cpu-load"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div class="profile-info-name">CPU</div>
                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="cpu"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div class="profile-info-name">Ram</div>
                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="ram"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div
                                                                    class="profile-info-name">@lang('app.disco')</div>

                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="disk"></span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-info-row">
                                                                <div
                                                                    class="profile-info-name">@lang('app.blocks')</div>
                                                                <div class="profile-info-value">
                                                                    <span class="editable" id="block"></span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <!--fin info-->
                                                    </div>
                                                    <div role="tabpanel" class="tab-pane" id="logs">
                                                        <!--inicio logs-->
                                                        <table id="registros"
                                                               class="table table-striped table-bordered table-hover">
                                                            <thead>
                                                            <tr>
                                                                <th>@lang('app.hour')</th>
                                                                <th class="center">@lang('app.event')</th>
                                                                <th>@lang('app.message')</th>
                                                            </tr>
                                                            </thead>
                                                        </table>
                                                        <!--fin logs-->
                                                    </div>
                                                    <div role="tabpanel" class="tab-pane" id="tlan"
                                                         style="min-width: 550px; height: 300px; margin: 0 auto">

                                                    </div>
                                                    <center><strong>
                                                            <div id="trafico"></div>
                                                        </strong></center>
                                                </div>
                                            </div>
                                            <!-- End Nav tabs -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="add" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i
                                                    class="fa fa-plug"></i> @lang('app.add') @lang('app.new') @lang('app.router')
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <form class="form-horizontal" role="form" id="formaddrouter">
                                                <div class="form-group">
                                                    <label for="name"
                                                           class="col-sm-2 control-label">@lang('app.name')</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="name" class="form-control"
                                                               id="name" maxlength="30">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="location"
                                                           class="col-sm-2 control-label">@lang('app.connection')</label>
                                                    <div class="col-sm-10">
                                                        <select class="form-control" name="connection" id="vinr">
                                                            <option value="0"
                                                                    selected>@lang('app.conectarConRouterMikrotik')</option>
                                                            <option
                                                                value="1">@lang('app.noConnectionToTheMikrotik')</option>
                                                        </select>
                                                        <br>
                                                        <span id="textv"></span>
                                                    </div>
                                                </div>
                                                <div class="form-group" id="ipapif">
                                                    <label for="ipapi" class="col-sm-2 control-label">IP <u>API</u></label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="ip" class="form-control" id="ipapi"
                                                               placeholder="0.0.0.0">
                                                    </div>
                                                </div>
                                                <div class="form-group" id="loginf">
                                                    <label for="login"
                                                           class="col-sm-2 control-label">@lang('app.login')</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="login" class="form-control"
                                                               id="login" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group" id="loginf">
                                                    <label for="port"
                                                           class="col-sm-2 control-label">@lang('app.port')
                                                        <u>API</u></label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="port" class="form-control"
                                                               id="port" value="8728">
                                                    </div>
                                                </div>
                                                <div class="form-group" id="passwordf">
                                                    <label for="Password"
                                                           class="col-sm-2 control-label">@lang('app.password')</label>
                                                    <div class="col-sm-10">
                                                        <input type="password" name="password" class="form-control"
                                                               id="Password" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="address"
                                                           class="col-sm-2 control-label">@lang('app.direction')</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" name="address" class="form-control"
                                                               id="address" maxlength="50">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="location"
                                                           class="col-sm-2 control-label">@lang('app.location')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="location" class="form-control"
                                                               id="location">
                                                    </div>

                                                    <div class="col-sm-1">

                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                id="openmap" data-toggle="modal"
                                                                data-target="#modalmap"
                                                                title="@lang('app.open') Mapa"><i
                                                                class="fa fa-map"></i></button>
                                                    </div>


                                                </div>


                                                <div class="form-group">
                                                    <label for="regpay" class="col-sm-2 control-label"><p
                                                            class="text-success"><i
                                                                class="fa fa-files-o"></i> @lang('app.copy')</p>
                                                    </label>
                                                    <div class="col-sm-10">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="copy" type="checkbox" class="ace"
                                                                       id="copy"/>
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
                                            <button type="button" class="btn btn-primary" id="addbtnrouter"
                                                    data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                                    class="fa fa-floppy-o"></i>
                                                @lang('app.save')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i
                                                    class="fa fa-pencil-square-o"></i> <span id="load"><i
                                                        class="fa fa-cog fa-spin"></i> @lang('app.loading') </span>@lang('app.edit') @lang('app.router')
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="tabbable">
                                                <ul class="nav nav-tabs" id="ro">
                                                    <li class="active"><a data-toggle="tab" href="#router"><i
                                                                class="fa fa-hdd-o"></i> @lang('app.router')</a>
                                                    </li>
                                                    <li id="redes"><a href="#ip" role="tab" data-toggle="tab"><i
                                                                class="fa fa-sitemap"></i> IP - Redes</a></li>
                                                    <li id="internet"><a href="#inter" role="tab" data-toggle="tab"><i
                                                                class="fa fa-exchange"></i> Control
                                                            - @lang('app.security')</a></li>
                                                </ul>
                                                <div class="tab-content" id="mytabs">
                                                    <div id="router" class="tab-pane fade in active">
                                                        <form class="form-horizontal" id="RouterformEdit">
                                                            <div class="form-group">
                                                                <label for="inputNameEdit"
                                                                       class="col-sm-2 control-label">@lang('app.name')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="name_edit"
                                                                           class="form-control" id="inputNameEdit"
                                                                           maxlength="30">
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="modelfe">
                                                                <label for="inputModelEdit"
                                                                       class="col-sm-2 control-label">@lang('app.model')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="model_edit"
                                                                           class="form-control" id="inputModelEdit"
                                                                           maxlength="30" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="ipapife">
                                                                <label for="inputIpEdit"
                                                                       class="col-sm-2 control-label">IP <u>API</u></label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="ip_edit"
                                                                           class="form-control" id="inputIpEdit"
                                                                           placeholder="0.0.0.0">
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="loginfe">
                                                                <label for="inputLoginEdit"
                                                                       class="col-sm-2 control-label">@lang('app.login')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="login_edit"
                                                                           class="form-control" id="inputLoginEdit"
                                                                           maxlength="50">
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="portapife">
                                                                <label for="inputPortEdit"
                                                                       class="col-sm-2 control-label">@lang('app.port')
                                                                    <u>API</u></label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="port_edit"
                                                                           class="form-control" id="inputPortEdit"
                                                                           placeholder="8728">
                                                                </div>
                                                            </div>
                                                            <div class="form-group" id="passwordfe">
                                                                <label for="inputPassword3"
                                                                       class="col-sm-2 control-label">@lang('app.password')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="password" name="password"
                                                                           class="form-control" id="inputPassword3"
                                                                           maxlength="50"
                                                                           placeholder="@lang('app.new') @lang('app.password')">
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="status" id="stcon">

                                                            <div class="form-group">
                                                                <input type="hidden" name="router_id">
                                                                <label for="inputAddressEdit"
                                                                       class="col-sm-2 control-label">@lang('app.direction')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="address_edit"
                                                                           class="form-control"
                                                                           id="inputAddressEdit" maxlength="50">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="edilocation"
                                                                       class="col-sm-2 control-label">@lang('app.location')</label>
                                                                <div class="col-sm-8">
                                                                    <input type="text" name="location_edit"
                                                                           class="form-control" id="edilocation">
                                                                </div>

                                                                <div class="col-sm-1">

                                                                    <button type="button"
                                                                            class="btn btn-sm btn-danger"
                                                                            id="btnmapedit" data-toggle="modal"
                                                                            data-target="#modalmapedit"
                                                                            title="@lang('app.open') Mapa"><i
                                                                            class="fa fa-map"></i></button>
                                                                </div>


                                                            </div>

                                                        </form>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">@lang('app.close')</button>
                                                            <button type="button"
                                                                    class="btn btn-primary savebtnrouter"
                                                                    data-loading-text="@lang('app.saving')..."
                                                                    autocomplete="off"><i
                                                                    class="fa fa-floppy-o"></i> @lang('app.save')
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="ip" class="tab-pane fade">
                                                        <form class="form-horizontal" role="form"
                                                              id="RouterformEdit2">
                                                            <div class="form-group" id="interfe">
                                                                <label for="inputEmail3"
                                                                       class="col-sm-2 control-label">Interfaz
                                                                    LAN</label>
                                                                <div class="col-sm-5">
                                                                    <select class="form-control" name="lan"
                                                                            id="interf"></select>
                                                                </div>

                                                            </div>

                                                            <div class="form-group">
                                                                <label for="inputEmail3"
                                                                       class="col-sm-2 control-label">@lang('app.add')
                                                                    IP/Red</label>
                                                                <div class="col-sm-5">
                                                                    <select class="" id="net" name="net"
                                                                            style="width: 100%"
                                                                            data-placeholder="Seleccione IP/Red">
                                                                        <option value=""></option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-sm-2">
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-primary"
                                                                            id="savebtnNetwork"><i
                                                                            class="fa fa-floppy-o"></i> @lang('app.add')
                                                                        IP/Red
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                        <div class="table-responsive">
                                                            <table id="addresses"
                                                                   class="table table-bordered table-hover">
                                                                <thead>
                                                                <tr>
                                                                    <th>Red</th>
                                                                    <th>@lang('app.equipment')</th>
                                                                    <th>IP/Gateway</th>
                                                                    <th>@lang('app.Option')</th>
                                                                </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">@lang('app.close')</button>
    {{--                                                                <button type="button"--}}
    {{--                                                                        class="btn btn-primary savebtnrouter"--}}
    {{--                                                                        data-loading-text="@lang('app.saving')..."--}}
    {{--                                                                        autocomplete="off"><i--}}
    {{--                                                                        class="fa fa-floppy-o"></i> @lang('app.save')--}}
    {{--                                                                </button>--}}
                                                        </div>
                                                    </div>
                                                    <div role="tabpanel" class="tab-pane" id="inter">

                                                        <form class="form-horizontal" role="form"
                                                              id="RouterformEdit3">
                                                            <div class="form-group">
                                                                <label for="control"
                                                                       class="col-sm-2 control-label">@lang('app.controlType')</label>
                                                                <div class="col-sm-10">
                                                                    <select class="form-control" name="control"
                                                                            id="typecontrol" onchange="checkTypeControl()">
                                                                        <option
                                                                            value="no">@lang('app.none')</option>
                                                                        <option value="sq" selected>Simple Queues
                                                                        </option>
                                                                        <option value="st" selected>Simple Queues (with Tree)</option>
                                                                        <option value="dl">DHCP Leases</option>
                                                                        <option value="ps">PPPoE - Simple Queues</option>
                                                                        <option value="pt">PPPoE - Simple Queues (with Tree)</option>
                                                                        <option value="pp">PPPoE - Secrets</option>
                                                                        <option value="pa">PPPoE - Secrets - PCQ
                                                                            Address List
                                                                        </option>
                                                                        <option value="pc">PCQ Address List</option>
<!--                                                                        <option value="ra">Radius - PPPoE Simple Queues (with Tree)</option>
                                                                        <option value="rp">Radius - PPPoE Secrets PCQ Address List</option>-->
                                                                        <option value="rr">PPPoE - Radius Accounting</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="arps" class="col-sm-2 control-label">Amarre
                                                                    IP/MAC</label>
                                                                <div class="col-sm-10">
                                                                    <label>
                                                                        <input name="arp" id="arps" value="1"
                                                                               class="ace ace-switch ace-switch-6"
                                                                               type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                    <div
                                                                        style="color: red;font-size: 14px;display: inline-block;vertical-align: top;       margin-left: 9px;">
                                                                        @lang('app.rememberBeforeApplying')
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="dhcp" class="col-sm-2 control-label">DHCP
                                                                    Leases</label>
                                                                <div class="col-sm-10">
                                                                    <label>
                                                                        <input name="dhcp" value="1" id="dhcp"
                                                                               class="ace ace-switch ace-switch-6"
                                                                               type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="adv" class="col-sm-2 control-label">Portal @lang('app.clients')</label>
                                                                <div class="col-sm-10">
                                                                    <label>
                                                                        <input name="adv" value="1" id="adv"
                                                                               class="ace ace-switch ace-switch-6"
                                                                               type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="address_list" class="col-sm-2 control-label">@lang('app.addInAddressList')</label>
                                                                <div class="col-sm-10">
                                                                    <label>
                                                                        <input name="address_list" value="1" id="address_list"
                                                                               class="ace ace-switch ace-switch-6"
                                                                               type="checkbox"/>
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div id="radius_data">
                                                                <hr>
                                                                <h4>Radius</h4>
                                                                <br>

                                                                <div class="form-group">
                                                                    <label for="radius_dbname" class="col-sm-2 control-label">Secret</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" name="radius_secret" class="form-control" id="radius_secret" >
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="radius_server" class="col-sm-2 control-label">IP NAS</label>
                                                                    <div class="col-sm-10">
{{--                                                                        is add here because if you are using VPN, we need change ip server of radius (and this params are into env and not in database)--}}
                                                                        <input type="text" name="radius_server" class="form-control" id="radius_server" value="{{env('DB_HOST_RADIUS')}}">
                                                                    </div>
                                                                </div>

                                                            </div>


                                                        </form>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">@lang('app.close')</button>
                                                            <button type="button"
                                                                    class="btn btn-primary savebtnrouter"
                                                                    data-loading-text="@lang('app.saving')..."
                                                                    autocomplete="off"><i
                                                                    class="fa fa-floppy-o"></i> @lang('app.save')
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmap" role="dialog"
                                 aria-labelledby="myLargeModalLabel" data-map-type="{{ $global->map_type }}">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i>
                                                @lang('app.map_types.' . $global->map_type ?? '')</h4>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-horizontal">
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">@lang('app.lookFor')
                                                        :</label>

                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control" id="us3-address"/>
                                                    </div>
                                                </div>

                                                <div id="us3" style="width: 100%; height: 400px;"></div>
                                                <div class="clearfix">&nbsp;</div>


                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-dismiss="modal"><i
                                                    class="fa fa-crosshairs"></i> @lang('app.toAccept')</button>

                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmapedit"
                                 role="dialog" aria-labelledby="myLargeModalLabel" style="z-index: 1051" data-map-type="{{ $global->map_type }}">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i>
                                            @lang('app.map_types.' . $global->map_type ?? '')</h4>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-horizontal">
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">@lang('app.lookFor')
                                                        :</label>

                                                    <div class="col-sm-11">
                                                        <input type="text" class="form-control" id="us4-address"/>
                                                    </div>
                                                </div>

                                                <div id="us4" style="width: 100%; height: 400px;"></div>
                                                <div class="clearfix">&nbsp;</div>


                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" data-dismiss="modal"><i
                                                    class="fa fa-crosshairs"></i> @lang('app.toAccept')</button>

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
    <input id="val" type="hidden" name="router">
@endsection

@section('scripts')
    @if($map!='0')
        <script
            src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
    @endif
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/select2.full.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/highcharts.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/modules/exporting.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/themes/grid.js')}}"></script>
    @if($map!='0')
    <script src="{{asset('assets/js/typeahead.jquery.min.js')}}"></script>
	<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js" ntegrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ==" crossorigin=""></script>
    @endif
    <script src="{{asset('assets/js/rocket/routers-core.js')}}"></script>
    <script src="{{asset('assets/js/rocket/tlan.js')}}"></script>
    {!! $dataTable->scripts() !!}
    <script>
        $('body').on('click', '.refresh', function() {
            var id = $(this).attr ("id");
            console.log(id, 'router id');
            var url = '{{ route('router.refresh', ':id') }}';
            url = url.replace(':id', id);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#routers-table",
                success:function(response) {
                    if(response.status == 'success') {
                        window.LaravelDataTables["router-table"].draw();
                    }
                }
            });
        });

        function getChangeIp (id) {
            var url = '{{route('router.get-change-ip')}}?router='+id;
            $.ajaxModal('#addEditModal', url);
        }

        function checkTypeControl(){
            let tc = $('#typecontrol').val();
            if(tc == 'ra' || tc == 'rp' || tc == 'rr'){
                    $('#radius_data').removeAttr('hidden');
                }
                else{
                    $('#radius_data').attr("hidden",true);
                }

            return;

        }

        function restart_freeradius(){

                var url = 'routers/restart-freeradius';
                $.easyAjax({
                    type: 'GET',
                    url: url,
                    success:function(response) {
                        if(response.status == 'success') {

                        }
                    }
                });

        }

        checkTypeControl();
    </script>
@endsection
