@extends('layouts.master')

@section('title', __('app.clients'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" href="assets/css/select2.min.css" />
    @php
        use App\Http\Controllers\PermissionsController;
    @endphp
    <style>
        .border-reset {
            border-radius: 0!important;
        }
        .date-range-picker-height {
            height: 37px !important;
        }
        .form-control {
            height: 36px;
        }
        .pac-container {
            z-index: 99999;
        }

        .modal-dialog {
            width: 70%;
            margin: 30px auto;
        }

        .zone_info {
            font-size: 17px;
            margin-top: 5px;
            font-weight: bold;
        }

        .content_port_s {
            display: none;
        }

        #table_estadis {
            border-spacing: 0;
            border-collapse: collapse;
            width: 100%;

        }


        #table_estadis th {
            text-transform: uppercase;
            color: black;
            font-size: 12px;
            line-height: 24px !important;
            background: #f0f0f0;
            border-bottom: #bfbfbf 1px solid;
            text-align: right;
        }

        #table_estadis th:first-child {
            text-align: left;
        }

        #table_estadis td,
        #table_estadis th {
            line-height: 12px;
            border-top: #e8e8e8 1px solid;
            font-size: 12px;
            padding: 3px;
        }

        #table_estadis td.link {
            width: 10px;
            text-align: center
        }

        #table_estadis td.link a {
            text-decoration: none;
            color: black;
        }

        #table_estadis td:nth-child(2) {
            width: 150px;
            padding-left: 6px;
        }

        #table_estadis td:nth-child(3),
        #table_estadis td:nth-child(4),
        #table_estadis td:nth-child(5),
        #table_estadis td:nth-child(6) {
            width: 70px;
            text-align: right;
        }


        #table_estadis td:nth-child(5) {
            padding-left: 3px;
            border-right: 1px solid #e8e8e8;
        }

        .bars {
            font-size: 0px;
        }

        .bars > div {
            font-size: 12px;
            background: rgba(0, 0, 0, 0.2);
            white-space: nowrap;
            box-sizing: border-box;
        }

        .bars > div.total {
            background: #51cb5f;
            height: 20px;
            display: none;

        }

        .bars > div.down {
            background: #43c453;
            height: 15px;
            display: inline-block;
        }

        .bars > div.up {
            background: #8fdd98;
            height: 15px;
            display: inline-block;
        }

        .selector {
            width: 130px;
            -webkit-touch-callout: none;
            margin-left: auto;
            margin-right: 5px;
            border: 1px solid #b4b4b4;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .selector > div {
            display: inline-block;
            font-size: 14px;
            color: black;
            font-family: monospace;
            line-height: 20px;
        }

        .selector > div.pre,
        .selector > div.next {
            cursor: pointer;
            width: 20px;
            user-select: none;
        }

        .selector > div.pre:hover,
        .selector > div.next:hover {
            background: white;
        }

        .selector > div.pre {
            margin-left: 0px;
            margin-right: auto;
            border-right: #b4b4b4 1px solid;
        }

        .selector > div.next {
            margin-left: auto;
            margin-right: 0px;
            border-left: #b4b4b4 1px solid;
        }

        .supertotal,
        .refresh {

            margin-left: 5px;
            margin-right: 5px;

            line-height: 20px;
            border: 1px solid #b4b4b4;
            width: 170px;
            font-family: monospace;
            text-align: center;
            font-size: 14px;
            color: black;
        }

        .refresh {
            text-transform: uppercase;
            cursor: pointer;
            color: #57a6ff;
            letter-spacing: 1px;
            font-size: 16px;

            background: url(../images/reload.svg) center center no-repeat;
            background-size: 15px;
            font-size: 0px;
            width: 22px;
            box-shadow: inset 0px 0px 2px white;
        }

        .refresh:hover {
            opacity: 0.8;
        }

        .refresh.active {
            pointer-events: none;
            opacity: 0.5;
            color: white;
        }

        .loading table {
            opacity: 0.5;
        }


        .brandbar {
            display: block;
            float: left;
            width: 30px;
            height: calc(100% - 4px);
            background: blue;
            margin: 2px;
            background: #3f3fc2; /* Old browsers */
            background: -moz-linear-gradient(top, #3f3fc2 0%, #000076 100%); /* FF3.6-15 */
            background: -webkit-linear-gradient(top, #3f3fc2 0%, #000076 100%); /* Chrome10-25,Safari5.1-6 */
            background: linear-gradient(to bottom, #3f3fc2 0%, #000076 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#3f3fc2', endColorstr='#000076', GradientType=0); /* IE6-9 */
        }

        .refresh {
            width: 95px !important;
            font-size: 14px;
            background: #000;
            color: #fff;
        }

        .brandbar:before {
            content: 'MikroStats';
            color: #8080b3;
            transform: rotate(270deg);
            display: block;
            font-size: 18px;
            position: absolute;
            bottom: 45px;
            left: -27px;
        }

        .topbar {
            width: 100%;
            line-height: 35px;
            height: 35px;
            border-bottom: #7c7c7c 1px solid;
            background: #f0f0f0;
            display: flex;
            align-items: center;
        }


        .window {
            position: relative;
            top: 35px;
            left: 170px;
            width: calc(100% - 170px);
            background: #f0f0f0;
        }


        .clock {
            border: 1px solid #a3a3a3;
            width: 100px;
            line-height: 18px;
            height: 18px;
            margin-left: auto;
            margin-right: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-shadow: 0px 0px 1px rgba(255, 255, 255, 0.5);
            font-family: monospace;
        }

        .movebar {
            background: #4a4dce;
            color: white;
            font-size: 17px;
            line-height: 24px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            border-top: 1px solid #9b9de9;
            border-left: 1px solid #9b9de9;
            border-right: 1px solid #9b9de9;
            margin-top: 20px;
        }

        .window {
            border: 1px solid black;
            height: calc(100vh - 37px);
            position: absolute;
        }

        .movebar .close {
            margin-left: auto;
            width: 16px;
            height: 16px;
            line-height: 16px;
            font-size: 15px;
            margin-right: 3px;
            border: 1px solid black;
            text-align: center;
            background: #f0f0f0;
            color: black;
            box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .toolbar {
            background: #f0f0f0;
            height: 35px;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .content {
            position: absolute;
            background: white;
            margin: 5px;
            border: 1px solid #7c7c7c;
            bottom: 0px;
            left: 0px;
            right: 0px;
            top: 10px;
            overflow: scroll;
            -webkit-overflow-scrolling: touch;

        }

        .queues {
            color: grey;
            font-size: 11px;
        }

        [data-server="ffk1.skynet-jc.at"] .bars > div.down {
            background: #eaac34;
        }

        [data-server="ffk1.skynet-jc.at"] .bars > div.up {
            background: #facf49;
        }

        /* !iPad CSS */
        @media only screen and (max-width: 600px) {
            .sidebar {
                display: none;
            }

            .window {
                left: 0px;
                width: 100%;
            }

            .close {
                display: none;
            }
        }

        #m_facturacion {
            color: red;
            font-size: 24px;
            display: none;
        }

        .content_zona_select {
            display: none;
        }

        .input-group > .btn.btn-sm {
            line-height: 25px;
        }
       .m-t-15 {
            margin-top: 15px;
        }
        #filterForm{
            display: none;
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
                        <a href="{{ URL::to('plans') }}">{{ __('app.plans') }}</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('clients') }}">{{ __('app.clients') }}</a>
                    </li>
                    <li class="active">{{ __('app.list') }}</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.plans') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.clients') }}
                        </small>
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.list') }}
                        </small>
                        {{--<button type="button" class="btn btn-sm btn-success newcl" data-toggle="modal"--}}
                                {{--data-target="#add">--}}
                            {{--<i class="icon-plus"></i> {{ __('app.new') }} {{ __('app.client') }}--}}
                        {{--</button>--}}

                    </h1>
                </div>
                <div class="row">

                    <div class="col-xl-12 col-lg-12 col-12">
                        <div class="card pull-up">
                            <div class="card-body">
                                <button type="button" class="btn btn-sm btn-purple border-reset m-b-10 toggle">
                                     Filters <i class="icon-equalizer"></i>
                                </button>

                                <form class="center_div" id="filterForm">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="online" id="online">
                                                    <option value="all">@lang('app.state')</option>
                                                    <option value="on">@lang('app.Online')</option>
                                                    <option value="off">@lang('app.disconnected')</option>
                                                    <option value="ver">@lang('app.verifying')</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{--<div class="col-md-4">--}}
                                            {{--<div class="form-group">--}}
                                                {{--<select class="form-control" name="plans" id="plans">--}}
                                                    {{--<option value="all">@lang('app.allPlan')</option>--}}
                                                    {{--@foreach($allPlans as $pl)--}}
                                                        {{--<option value="{{ $pl->id }}">{{ $pl->name }}</option>--}}
                                                    {{--@endforeach--}}
                                                {{--</select>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="routers_details" id="routers_details">
                                                    <option value="all">@lang('app.allRouter')</option>
                                                    @foreach($allRouters as $rout)
                                                        <option value="{{ $rout->id }}">{{ $rout->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="serviceStatus" id="serviceStatus">
                                                    <option value="all">@lang('app.service')</option>
                                                    <option value="ac">@lang('app.active')</option>
                                                    <option value="de">@lang('app.blocked')</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input class="form-control date-picker"
                                                       maxlength="8"
                                                       type="text"
                                                       placeholder="{{ __('app.serviceCut') }}"
                                                       data-date-format="dd-mm-yyyy"
                                                       id="cut" name="cut"
                                                       required/>
                                                <span class="input-group-addon">
            <i class="fa fa-calendar bigger-110"></i>
        </span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="control"
                                                        id="typecontrol">
                                                    <option
                                                            value="all" selected>All Controls
                                                    </option>
                                                    <option
                                                            value="no">@lang('app.none')</option>
                                                    <option value="sq">Simple Queues
                                                    </option>
                                                    <option value="st">Simple Queues (with Tree)</option>
                                                    <option value="ho">Hotspot - User Profiles
                                                    </option>
                                                    <option value="ha">Hotspot - PCQ Address
                                                        List
                                                    </option>
                                                    <option value="dl">DHCP Leases</option>
                                                    <option value="ps">PPPoE - Simple Queues</option>
                                                    <option value="pt">PPPoE - Simple Queues (with Tree)</option>
                                                    <option value="pp">PPPoE - Secrets</option>
                                                    <option value="pa">PPPoE - Secrets - PCQ
                                                        Address List
                                                    </option>
                                                    <option value="pc">PCQ Address List</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <input class="form-control date-picker"
                                                       maxlength="8"
                                                       type="text"
                                                       placeholder="{{ __('app.payday') }}"
                                                       data-date-format="dd-mm-yyyy"
                                                       id="date_in" name="date_in"
                                                       required/>
                                                <span class="input-group-addon">
            <i class="fa fa-calendar bigger-110"></i>
        </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control"
                                                   type="text"
                                                   placeholder="Filter by client name"
                                                   id="client_name" name="client_name"
                                                   required/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <input class="form-control"
                                                   type="text"
                                                   placeholder="Filter by ip address"
                                                   id="ip_filter" name="ip_filter"
                                                   required/>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="client_status"
                                                        id="client_status">
                                                    <option
                                                        value="all" selected>All Users
                                                    </option>
                                                    <option value="active">
                                                        {{ __('app.active') }}
                                                    </option>
                                                    <option value="inactive">
                                                        {{ __('app.inActive') }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control" name="client_type"
                                                        id="client_type">
                                                    <option
                                                            value="all" selected>All Types
                                                    </option>
                                                    <option value="prepay">
                                                        {{ __('app.prepay') }}
                                                    </option>
                                                    <option value="postpay">
                                                        {{ __('app.postpay') }}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group m-t-15">
                                        <button type="button" id="search"
                                                class="btn cero_margin btn-sm btn-success"><i
                                                    class="fa fa-search"></i>
                                            @lang('app.filter')</button>
                                        <button type="button" id="searchall" class="btn btn-sm btn-purple"><i
                                                    class="fa fa-search-plus"></i>
                                            @lang('app.showAll')
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12">
                        {{-- <div id="m_facturacion">Debe completar el proceso de facturación. <STRONG>Redireccionando ...</STRONG></div> --}}
                        {{-- <div class="hr hr-18 dotted hr-double"></div>      --}}
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">{{ __('app.allCustomers') }}</h5>
                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown"
                                                   class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                                    <li>
                                                        <a href="#" data-toggle="modal" class="newcl"
                                                           data-target="#add"><i class="fa fa-plus-circle"></i>
                                                            {{__('app.add')}} {{__('app.client')}}</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            {{--<button type="button" class="btn btn-sm btn-primary" style="margin-right: 15px;--}}
                                {{--float: left;--}}
                                {{--padding: 0px 9px !important;--}}
                                {{--font-size: 9px;" data-toggle="modal" data-target="#campos_visibles">--}}
                                                {{--<i class="ace-icon fa fa-pencil bigger-130"></i>{{ __('app.column') }}--}}
                                            {{--</button>--}}
                                            <a href="#" data-action="fullscreen" class="white">
                                                <i class="ace-icon fa fa-expand"></i>
                                            </a>
                                            <a href="#" data-action="reload" class="recargar white">
                                                <i class="ace-icon fa fa-refresh"></i>
                                            </a>
                                            <a href="#" data-action="collapse" class="white">
                                                <i class="ace-icon fa fa-chevron-up"></i>
                                            </a>

                                            {{--<label title="Search Enable/Disable">
                                                <input id="presms"
                                                       class="ace ace-switch ace-switch-6"
                                                       value="1"
                                                       @if($global->search_show == 1) checked @endif
                                                       type="checkbox" onchange="changeHandler()"/>
                                                <span class="lbl"></span>
                                            </label>--}}
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


                            <!--start modal add-->
                            <div class="modal fade" id="campos_visibles" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                    aria-hidden="true">&times;</span><span
                                                    class="sr-only">{{ __('app.close') }}</span>
                                            </button>
                                            <h4 class="modal-title" id="myModalLabel"><i
                                                    class="fa fa-user-plus"></i>
                                                {{ __('app.showHideColumn') }}</h4>
                                        </div>
                                        <div class="modal-body" id="winnew_2">
                                            <div class="row">
                                                <div class="form-group">
                                                    <form class="form-horizontal" id="formaddvistas">
                                                        <div class="col-xs-8 col-sm-5">

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="name"
                                                                           id="edit_acc_name" type="checkbox"
                                                                           class="ace" {{ ($campos_v->name)?'checked':'' }} />
                                                                    <span class="lbl"> {{ __('app.name') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="ip"
                                                                           id="edit_acc_op" type="checkbox"
                                                                           class="ace" {{ ($campos_v->ip)?'checked':'' }}/>
                                                                    <span class="lbl"> Ip</span>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="router"
                                                                           id="edit_acc_router" type="checkbox"
                                                                           class="ace" {{ ($campos_v->router)?'checked':'' }} />
                                                                    <span class="lbl"> {{ __('app.router') }}</span>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="estado"
                                                                           id="edit_acc_estado" type="checkbox"
                                                                           class="ace" {{ ($campos_v->estado)?'checked':'' }}/>
                                                                    <span class="lbl"> {{ __('app.state') }}</span>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="control"
                                                                           id="edit_acc_control" type="checkbox"
                                                                           class="ace" {{ ($campos_v->control)?'checked':'' }}/>
                                                                    <span
                                                                        class="lbl"> {{ __('app.control') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="plan"
                                                                           id="edit_acc_plan" type="checkbox"
                                                                           class="ace" {{ ($campos_v->plan)?'checked':'' }} />
                                                                    <span class="lbl"> {{ __('app.plan') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="servicio"
                                                                           id="edit_acc_servicio" type="checkbox"
                                                                           class="ace" {{ ($campos_v->servicio)?'checked':'' }}/>
                                                                    <span
                                                                        class="lbl"> {{ __('app.service') }}</span>
                                                                </label>
                                                            </div>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="balance"
                                                                           id="edit_acc_balance" type="checkbox"
                                                                           class="ace" {{ ($campos_v->balance)?'checked':'' }}/>
                                                                    <span
                                                                        class="lbl"> {{ __('app.balance') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="day_payment"
                                                                           id="edit_acc_day_payment" type="checkbox"
                                                                           class="ace" {{ ($campos_v->day_payment)?'checked':'' }}/>
                                                                    <span class="lbl">{{ __('app.payday') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="cut"
                                                                           id="edit_acc_cut" type="checkbox"
                                                                           class="ace" {{ ($campos_v->cut)?'checked':'' }}/>
                                                                    <span
                                                                        class="lbl"> {{ __('app.serviceCut') }}</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="mac"
                                                                           id="edit_acc_mac" type="checkbox"
                                                                           class="ace" {{ ($campos_v->mac)?'checked':'' }}/>
                                                                    <span class="lbl"> Mac</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="zone"
                                                                           id="edit_zone_c" type="checkbox"
                                                                           class="ace" {{ ($campos_v->zone)?'checked':'' }}/>
                                                                    <span class="lbl"> Zona</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="odb_id"
                                                                           id="edit_odb_id_c" type="checkbox"
                                                                           class="ace" {{ ($campos_v->odb_id)?'checked':'' }}/>
                                                                    <span class="lbl"> Caja</span>
                                                                </label>
                                                            </div>

                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="campos_acc[]" value="onu_id"
                                                                           id="edit_onu_id_d" type="checkbox"
                                                                           class="ace" {{ ($campos_v->onu_id)?'checked':'' }}/>
                                                                    <span class="lbl"> ONUs/CPE</span>
                                                                </label>
                                                            </div>

                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">{{ __('app.close') }}
                                            </button>
                                            <button type="button" id="addbtnviewClient"
                                                    class="btn btn-primary"
                                                    data-loading-text="@lang('app.saving')..."><i
                                                    class="fa fa-floppy-o"></i>
                                                {{ __('app.save') }}
                                            </button>
                                        </div>


                                        </form>
                                    </div>

                                </div>

                            </div>
                            <!--end modal add-->


                        </div>
                        <!--end modal add-->


                        <!--modal edit-->
                        <div class="modal fade bs-edit-modal-lg" id="edit" role="dialog"
                             aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"><span
                                                aria-hidden="true">&times;</span><span
                                                class="sr-only">{{ __('app.close') }}</span>
                                        </button>
                                        <h4 class="modal-title" id="myModalLabel"><i
                                                class="fa fa-pencil-square-o"></i> <span id="load"><i
                                                    class="fa fa-cog fa-spin"></i> {{__('app.loading')}}</span> {{__('app.edit')}}
                                            {{__('app.client')}}</h4>
                                    </div>
                                    <div class="modal-body" id="winnew">
                                        <form class="form-horizontal" id="ClientformEdit">
                                            <div id="alert" class="text-center">

                                            </div>
                                            <div class="form-group">
                                                <label for="name"
                                                       class="col-sm-3 control-label">{{ __('app.fullName') }} <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="edit_name"
                                                           class="form-control" autocomplete="off"
                                                           id="edit_name" maxlength="100">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="phone"
                                                       class="col-sm-3 control-label">{{ __('app.telephone') }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="edit_phone"
                                                           class="form-control" autocomplete="off"
                                                           id="edit_phone" maxlength="25">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="email"
                                                       class="col-sm-3 control-label">{{ __('app.email') }} <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="email" name="edit_email"
                                                           class="form-control" autocomplete="off"
                                                           id="edit_email" maxlength="60">
                                                </div>
                                            </div>
                                            @if($falctelStatus=='2')
                                                <div class="form-group">
                                                    <label for="edit_typedoc_cod" class="col-sm-3 control-label">Tipo de documento<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="edit_typedoc_cod" name="edit_typedoc_cod"
                                                            class="form-control" autocomplete="off">
                                                            @foreach ($cmbtypedoc as $typedoc)
                                                                <option value='{{$typedoc->cod}}' >{{$typedoc->Description}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <label for="dni"
                                                       class="col-sm-3 control-label">{{ ($falctelStatus=='2')? 'N° de Identificación' : __('app.number').' DNI/CI'}} <span class="text-danger">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="edit_dni"
                                                           class="form-control" autocomplete="off"
                                                           id="edit_dni" maxlength="20">
                                                </div>
                                            </div>
                                            @if($falctelStatus=='2')
                                                <div class="form-group">
                                                    <label for="edit_economicactivity_cod" class="col-sm-3 control-label">Actividad económica<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9">
                                                        <select style="width:100%;" class="form-control select2" id="edit_economicactivity_cod" name="edit_economicactivity_cod">
                                                            @foreach ($cmbeconomicactivity as $economicactivity)
                                                                <option value='{{$economicactivity->cod}}' >{{$economicactivity->cod.' - '.$economicactivity->Description}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_typeresponsibility_cod" class="col-sm-3 control-label">Tipo de responsabilidad<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="edit_typeresponsibility_cod" name="edit_typeresponsibility_cod">
                                                            @foreach ($cmbtyperesponsibility as $typeresponsibility)
                                                                <option value='{{$typeresponsibility->cod}}' >{{$typeresponsibility->Description}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_typetaxpayer_cod" class="col-sm-3 control-label">Tipo de contribuyente<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control" id="edit_typetaxpayer_cod" name="edit_typetaxpayer_cod">
                                                            @foreach ($cmbtypetaxpayer as $typetaxpayer)
                                                                <option value='{{$typetaxpayer->cod}}' >{{$typetaxpayer->Description}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_municipio_cod" class="col-sm-3 control-label">Departamento/Municipio<span class="text-danger">*</span></label>
                                                    <div class="col-sm-9">
                                                        <select style='width:100%;' class="form-control select2" id="edit_municipio_cod" name="edit_municipio_cod">
                                                            @foreach ($cmbmunicipio as $municipio)
                                                                <option value='{{$municipio->cod}}' >{{$municipio->Departamento.'/'.$municipio->Municipio}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="form-group">
                                                <label for="dir"
                                                       class="col-sm-3 control-label">{{ __('app.direction') }}</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="edit_dir"
                                                           class="form-control" autocomplete="off"
                                                           id="edit_dir" maxlength="100">
                                                </div>
                                            </div>

                                            {{-- nuevos campos --}}
                                            @if($falctelStatus=='0')
                                                <div class="form-group">
                                                    <label for="punto_emision" class="col-sm-3 control-label">Punto de Emision</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control onchange_punto" name="edit_punto_emision">

                                                                <option value="" >Ninguno</option>
                                                            @foreach($punto_emision as $key=>$pto_emision)
                                                                <option value="{{ $pto_emision->id }}">{{ $pto_emision->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif
                                            {{-- nuevos campos --}}


                                            <div class="form-group">
                                                <label for="dir" class="col-sm-3 control-label">Caja</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control onchange_caja" id="edit_odb_id"
                                                            name="edit_odb_id">
                                                        <option value="">Seleccione Caja</option>
                                                        @foreach($OdbSplitter as $zo)
                                                            <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group content_port_s">
                                                <label for="dir" class="col-sm-3 control-label">Puertos
                                                    Disponibles</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control select_port" id="select_port_edit"
                                                            name="edit_port">

                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group content_port_s">
                                                <label for="dir" class="col-sm-3 control-label">Zonas</label>
                                                <div class="col-sm-9">
                                                    <span class="zone_info"></span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="dir" class="col-sm-3 control-label">ONUs/CPE</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control change_unus" id="edit_onu_id"
                                                            name="edit_onu_id">
                                                        <option value="">Seleccione ONUs/CPE</option>
                                                        @foreach($OnuType as $on)
                                                            <option value="{{ $on->id }}"
                                                                    data-type="{{ $on->pontype }}">{{ $on->onutype }}
                                                                -- ({{ $on->pontype }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <input type="hidden" class="zona_value" name="edit_zona_id"
                                                   id="zona_id_edit">
                                            <div class="form-group content_zona_select">
                                                <label for="dir" class="col-sm-3 control-label">Zonas</label>
                                                <div class="col-sm-9">

                                                    <select class="form-control zona_id_input" name="zona_ident"
                                                            id="edit_zona_id">
                                                        <option value="">Seleccione Zona</option>
                                                        @foreach($Zone as $zo)
                                                            <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- final nuevos campos --}}

                                            <div class="form-group">
                                                <label for="edilocation"
                                                       class="col-sm-3 control-label">{{ __('app.location') }}</label>
                                                <div class="col-sm-5">
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
                                            <div class="form-group">
                                                <input type="hidden" name="client_id">
                                                <label for="edit_pass2"
                                                       class="col-sm-3 control-label">{{ __('app.password') }} <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-5">
                                                    <input type="password" name="edit_pass2"
                                                           class="form-control" id="edit_pass2"
                                                           maxlength="50">
                                                </div>
                                                <div class="col-sm-1">

                                                    <button type="button"
                                                            class="btn btn-sm btn-success"
                                                            id="edgenporpass" title="Generar"><i
                                                            class="fa fa-bolt"></i></button>
                                                </div>
                                                <div class="col-sm-3">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" id="edshowp"
                                                                   class="ace">
                                                            <span class="lbl"> {{ __('app.seePassword') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <!--cuerpo Modal-->

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">{{ __('app.close') }}
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary editbtnclient"
                                                    data-loading-text="@lang('app.saving')..."><i
                                                    class="fa fa-floppy-o"></i>
                                                {{ __('app.save') }}
                                            </button>
                                        </div>


                                        <!--end modal body-->
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 m-t-15">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Totals</font></font></font></font></strong>
                            </div>
                            <div class="panel-body">
                                <table class="display supertable table table-striped table-bordered">
                                    <thead>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Active</td>
                                        <td id="activeClients">

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Inactive</td>
                                        <td id="inActiveClients">

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Blocked</td>
                                        <td id="blockedClients">

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>All</td>
                                        <td id="allClients">

                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!--Modal info client-->
            <div class="modal fade bs-example-modal-lg"  id="modalinfo" role="dialog"
                 aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                    class="sr-only">{{ __('app.close') }}</span></button>
                            <h4 class="modal-title"><i class="fa fa-info-circle" aria-hidden="true"></i>
                                <span id="infotitle"></span></h4>
                        </div>

                        <div class="modal-body">


                            <div class="box-body">

                                <!--inicio info-->
                                <div class="profile-user-info profile-user-info-striped">

                                    <div class="profile-info-row">
                                        <div class="profile-info-name"> {{ __('app.payday') }}</div>
                                        <div class="profile-info-value">
                                            <span class="editable" id="infopaydate"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.plan') }}</div>
                                        <div class="profile-info-value">
                                            <span class="editable" id="infoplan"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.notice') }} email</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoemail"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.notice') }} SMS</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infosms"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.serviceCut') }}</div>
                                        <div class="profile-info-value">
                                            <span class="editable" id="infocut"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.service') }}</div>
                                        <div class="profile-info-value">
                                            <span class="editable" id="infoservice"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.onRouter') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="inforouter"></span>
                                        </div>
                                    </div>
                                    <div class="profile-info-row">
                                        <div class="profile-info-name">IP</div>
                                        <div class="profile-info-value">
                                            <span class="editable" id="infoip"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">Mac</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infomac"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.control') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infocontrol"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">Portal {{ __('app.client') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoportal"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.email') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoEmail"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.telephone') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoTelephone"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">{{ __('app.direction') }}</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoDirection"></span>
                                        </div>
                                    </div>

                                    <div class="profile-info-row">
                                        <div class="profile-info-name">RUC/Ci</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoRuci"></span>
                                        </div>
                                    </div>


                                    <div class="profile-info-row">
                                        <div class="profile-info-name">@lang('app.coordinates')</div>

                                        <div class="profile-info-value">
                                            <span class="editable" id="infoCoordinates"></span>
                                        </div>
                                    </div>

                                </div>

                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal">{{ __('app.close') }}</button>

                        </div>

                    </div>
                </div>
            </div>


            <div class="modal fade bs-example-modal-lg"  id="modalmapedit" role="dialog"
                 aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                    class="sr-only">{{ __('app.close') }}</span></button>
                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> Google Maps</h4>
                        </div>

                        <div class="modal-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-1 control-label">{{ __('app.lookFor') }}:</label>

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
                                    class="fa fa-crosshairs"></i> {{ __('app.toAccept') }}
                            </button>

                        </div>

                    </div>
                </div>
            </div>


            <!--start modal tools-->
            <div class="modal fade" id="tools"  role="dialog" aria-labelledby="myModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                    class="sr-only">{{ __('app.close') }}</span></button>
                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-wrench"></i>
                                {{ __('app.tools') }}</h4>
                        </div>
                        <div class="modal-body" id="winnew">

                            <!--cuerpo Modal-->
                            <div class="tabbable">
                                <ul class="nav nav-tabs" id="rtool">
                                    <li class="active"><a id="pin" data-toggle="tab" href="#pingclient"><i
                                                class="fa fa-exchange"></i> Ping - Mikrotik</a></li>
                                    <li><a id="torc" href="#torchmk" role="tab" data-toggle="tab"><i
                                                class="fa fa-random"></i> Torch - Mikrotik</a></li>
                                    <li><a id="traf" href="#trafic" role="tab" data-toggle="tab"><i
                                                class="fa fa-bar-chart"></i> {{ __('app.traffic') }}</a></li>
                                    {{--    <li id="iniciar_stadisticas"><a id="traf" href="#estadisticas"  role="tab" data-toggle="tab"><i class="fa fa-bar-chart"></i> Estadísticas</a></li>  --}}
                                </ul>
                                <div class="tab-content" id="mytabs">
                                    <div id="pingclient" class="tab-pane fade in active">

                                        <form class="form-horizontal" id="formapingclient">
                                            <div class="form-group">
                                                <label for="ipt" class="col-sm-2 control-label">Ping a</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="ipt" class="form-control"
                                                           autocomplete="off" id="ipt" maxlength="40">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="interface"
                                                       class="col-sm-2 control-label">{{ __('app.interface') }}</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control" name="interface"
                                                            id="interface"></select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="packages"
                                                       class="col-sm-2 control-label">{{ __('app.packages') }}</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="packages" value="2" min="1"
                                                           class="form-control" autocomplete="off" id="packages"
                                                           maxlength="20">
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="arps" class="col-sm-2 control-label">ARP
                                                    Ping</label>
                                                <div class="col-sm-10">
                                                    <label>
                                                        <input name="arp" id="arps" value="1"
                                                               class="ace ace-switch ace-switch-6"
                                                               type="checkbox"/>
                                                        <span class="lbl"></span>
                                                    </label>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="btnping" class="col-sm-2 control-label"></label>
                                                <div class="col-sm-5">
                                                    <button type="button" class="btn btn-sm btn-success"
                                                            id="btnping">{{ __('app.start') }}
                                                    </button>
                                                </div>
                                                <input type="hidden" name="router" id="rtid">
                                                <input type="hidden" name="service_id" id="service_id">
                                            </div>
                                        </form>

                                        <div class="table-responsive">
                                            <table class="table" id="table-ping" width="100%">

                                                <thead>
                                                <tr>
                                                    <th>Host</th>
                                                    <th>{{ __('app.size') }}</th>
                                                    <th>TTL</th>
                                                    <th>{{ __('app.weather') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>

                                            </table>
                                        </div>
                                        <br>

                                        <!--cuerpo Modal-->


                                    </div>
                                    <div id="torchmk" class="tab-pane fade">
                                        <form class="form-horizontal" role="form" id="formtorch">


                                            <div class="form-group">
                                                <label for="slcrouter"
                                                       class="col-sm-2 control-label">{{ __('app.interface') }}</label>
                                                <div class="col-sm-10">
                                                    <select class="form-control" name="interface"
                                                            id="slinterface"></select>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="srca" class="col-sm-2 control-label">Src.
                                                    Address</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="srcaddress" id="srca"
                                                           class="form-control">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="dst" class="col-sm-2 control-label">Dst.
                                                    Address</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="dstaddress" value="0.0.0.0/0"
                                                           class="form-control">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="duration"
                                                       class="col-sm-2 control-label">{{ __('app.duration') }}
                                                    Seg.</label>
                                                <div class="col-sm-10">
                                                    <input type="number" min="1" name="duration" value="3"
                                                           class="form-control">
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <label for="btntorch" class="col-sm-2 control-label"></label>
                                                <div class="col-sm-10">
                                                    <button class="btn btn-sm btn-success" id="btntorch">
                                                        {{ __('app.start') }}
                                                    </button>
                                                </div>
                                            </div>


                                        </form>
                                        <div class="table-responsive">
                                            <table class="table" id="table-torch" width="100%">

                                                <thead>
                                                <tr>
                                                    <th>Src.</th>
                                                    <th>Dst.</th>
                                                    <th>Src port.</th>
                                                    <th>Dst port</th>
                                                    <th>Tx</th>
                                                    <th>Rx</th>
                                                    <th>Tx Packet</th>
                                                    <th>Rx Packet</th>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>

                                            </table>
                                        </div>
                                        <br>


                                        <!--end tab-->
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="trafic">
                                        <input type="hidden" id="clid">
                                        <input type="hidden" id="namecl">


                                        <div id="tlan"
                                             style="min-width: 541px; height: 400px; margin: 0 auto"></div>

                                        <center><strong>
                                                <div id="trafico"></div>
                                            </strong></center>


                                    </div>


                                    <!--end tab-->
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <!--end modal tools-->


            @include('layouts.modals')

        </div>
    </div>

    <input id="val" type="hidden" name="client" value="">
    <!--start modal add-->
    <div class="modal fade" id="add"  role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span
                            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel"><i
                            class="fa fa-user-plus"></i>
                        {{ __('app.add') }} {{ __('app.new') }} {{__('app.client')}}</h4>
                </div>
                <div class="modal-body" id="winnew">
                    <form class="form-horizontal" id="formaddclient1">
                        <div id="alert" class="text-center">

                        </div>
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">{{ __('app.fullName') }} <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="name"
                                       class="form-control" autocomplete="off"
                                       id="name" maxlength="100">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="col-sm-3 control-label">{{ __('app.telephone') }} <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="phone"
                                       class="form-control" autocomplete="off"
                                       id="phone" maxlength="25">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-3 control-label">{{ __('app.email') }} <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="email" name="email"
                                       class="form-control" autocomplete="off"
                                       id="email" maxlength="60">
                            </div>
                        </div>
                        @if($falctelStatus=='2')
                            <div class="form-group">
                                <label for="typedoc_cod" class="col-sm-3 control-label">Tipo de documento<span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="typedoc_cod" name="typedoc_cod"
                                        class="form-control" autocomplete="off">
                                        @foreach ($cmbtypedoc as $typedoc)
                                            <option value='{{$typedoc->cod}}' >{{$typedoc->Description}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="dni" class="col-sm-3 control-label">{{ ($falctelStatus=='2')? 'N° de Identificación' : __('app.number').' DNI/CI'}}
                                <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="dni"
                                       class="form-control" autocomplete="off"
                                       id="dni" maxlength="20">
                            </div>
                        </div>
                        @if($falctelStatus=='2')
                            <div class="form-group">
                                <label for="economicactivity_cod" class="col-sm-3 control-label">Actividad económica<span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select style="width:100%;" class="form-control select2" id="economicactivity_cod" name="economicactivity_cod">
                                        @foreach ($cmbeconomicactivity as $economicactivity)
                                            <option value='{{$economicactivity->cod}}' >{{$economicactivity->cod.' - '.$economicactivity->Description}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typeresponsibility_cod" class="col-sm-3 control-label">Tipo de responsabilidad<span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="typeresponsibility_cod" name="typeresponsibility_cod">
                                        @foreach ($cmbtyperesponsibility as $typeresponsibility)
                                            <option value='{{$typeresponsibility->cod}}' {{($typeresponsibility->cod=='ZZ')?"selected='selected'":""}}>{{$typeresponsibility->Description}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="typetaxpayer_cod" class="col-sm-3 control-label">Tipo de contribuyente<span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="typetaxpayer_cod" name="typetaxpayer_cod">
                                        @foreach ($cmbtypetaxpayer as $typetaxpayer)
                                            <option value='{{$typetaxpayer->cod}}' >{{$typetaxpayer->Description}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                @lang('app.billingDue') <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-9">
                                <select type="select" id="customers_billing_due" style="width: 100%;"
                                        original-value="15" force-send="0" class="select2 select2-hidden-accessible"
                                        name="billing_due_date"  aria-hidden="true">
                                    <option value="0">@lang('app.disabled')</option>
                                    @for ($i = 1; $i < 32; $i++)
                                        <option value="{{ $i }}" @if($i == 15) selected @endif>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        @if($falctelStatus=='2')
                            <div class="form-group">
                                <label for="municipio_cod" class="col-sm-3 control-label">Departamento/Municipio<span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select style='width:100%;' class="form-control select2" id="municipio_cod" name="municipio_cod">
                                        @foreach ($cmbmunicipio as $municipio)
                                            <option value='{{$municipio->cod}}' >{{$municipio->Departamento.'/'.$municipio->Municipio}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">{{ __('app.direction') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="dir"
                                       class="form-control" autocomplete="off"
                                       id="dir" maxlength="100">
                            </div>
                        </div>


                            {{-- nuevos campos --}}
                        @if($falctelStatus=='0')
                            <div class="form-group">
                                <label for="pto_emision" class="col-sm-3 control-label">Punto de Emision</label>
                                <div class="col-sm-9">
                                    <select class="form-control onchange_punto" name="punto_emision">
                                        <option value="">Ninguno</option>

                                        @foreach($punto_emision as $key=>$pto_emision)
                                            <option value="{{ $pto_emision->id }}">{{ $pto_emision->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        {{-- nuevos campos --}}


                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">Caja</label>
                            <div class="col-sm-9">
                                <select class="form-control onchange_caja" name="odb_id">
                                    <option value="">Seleccione Caja</option>
                                    @foreach($OdbSplitter as $zo)
                                        <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group content_port_s">
                            <label for="dir" class="col-sm-3 control-label">Puertos Disponibles</label>
                            <div class="col-sm-9">
                                <select class="form-control select_port" name="port">
                                    <option value="">Seleccione puerto</option>

                                </select>
                            </div>
                        </div>

                        <div class="form-group content_port_s">
                            <label for="dir" class="col-sm-3 control-label">Zonas</label>
                            <div class="col-sm-9">
                                <span class="zone_info"></span>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">ONUs/CPE</label>
                            <div class="col-sm-9">
                                <select class="form-control change_unus" name="onu_id">
                                    <option value="">Seleccione ONUs/CPE</option>
                                    @foreach($OnuType as $on)
                                        <option value="{{ $on->id }}" data-type="{{ $on->pontype }}">{{ $on->onutype }}
                                            -- ({{ $on->pontype }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <input type="hidden" class="zona_value" name="zona_id" id="zona_id_input">
                        <div class="form-group content_zona_select">
                            <label for="dir" class="col-sm-3 control-label">Zonas</label>
                            <div class="col-sm-9">

                                <select class="form-control zona_id_input" name="zona_ident" id="zona_id">
                                    <option value="">Seleccione Zona</option>
                                    @foreach($Zone as $zo)
                                        <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        {{-- final nuevos campos --}}

                        <div class="form-group">
                            <label for="location"
                                   class="col-sm-3 control-label">{{ __('app.location') }}</label>
                            <div class="col-sm-5">
                                <input type="text" name="location"
                                       class="form-control" id="location">
                            </div>

                            <div class="col-sm-1">

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        id="openmap" data-toggle="modal"
                                        data-target="#modalmap"
                                        title="@lang('app.open') Mapa"><i
                                        class="fa fa-map"></i></button>
                            </div>

                        </div>


                        <div class="form-group">
                            <label for="pass" class="col-sm-3 control-label">{{ __('app.password') }}
                                (portal) <span class="text-danger">*</span></label>
                            <div class="col-sm-5">
                                <input type="password" name="pass"
                                       class="form-control" autocomplete="off"
                                       id="pass" maxlength="40">
                            </div>

                            <div class="col-sm-1">

                                <button type="button"
                                        class="btn btn-sm btn-success"
                                        id="genporpass" title="Generar"><i
                                        class="fa fa-bolt"></i></button>
                            </div>
                            <div class="col-sm-3">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="showp"
                                               class="ace">
                                        <span class="lbl"> {{ __('app.seePassword') }}</span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </form>
                    <!--cuerpo Modal-->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">{{ __('app.close') }}
                        </button>
                        <button type="button"
                                class="btn btn-primary addbtnclient"
                                data-loading-text="@lang('app.saving')..."><i
                                class="fa fa-floppy-o"></i>
                            {{ __('app.save') }}
                        </button>
                    </div>
                </div>

            </div>


        </div>
    </div>
    <!--Modal Google Maps-->
    <div class="modal fade bs-example-modal-lg"  id="modalmap" role="dialog"
         aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span
                            aria-hidden="true">&times;</span><span
                            class="sr-only">{{ __('app.close') }}</span></button>
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> Google Maps</h4>
                </div>

                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-1 control-label">{{ __('app.lookFor') }}:</label>

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
                            class="fa fa-crosshairs"></i> {{ __('app.toAccept') }}
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($map!='0')
        <script
            src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
    @endif

    <script src="{{asset('assets/js/highchart/code/highcharts.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/modules/exporting.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/themes/grid.js')}}"></script>
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/date-time/locales/bootstrap-datepicker.es.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    <script src="{{asset('assets/js/pGenerator.jquery.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-typeahead.min.js')}}"></script>
    <script src="{{asset('assets/js/rocket/clients-core.js')}}"></script>
    <script src="{{asset('assets/js/rocket/tcl.js')}}"></script>
    <script src="{{asset('assets/js/select2.full.min.js')}}"></script>


    {!! $dataTable->scripts() !!}

    <script>
        function changeHandler() {
            var checkBox = document.getElementById("presms");
            var value = 0;

            if (checkBox.checked == true){
                value = 1;
            }

            $.easyAjax({
                type: 'POST',
                url: "config/search-disable",
                data: {
                    search_show: value
                },
                container: "#plan-plan-client-table",
                success: function (response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        }

        // Open cortado history model
        function banHistory(serviceId) {
            var url = '{{ route('billing.services.ban-history', ':id') }}';
            url = url.replace(':id', serviceId);

            $.ajaxModal('#addEditModal', url);
        }

        $(document).ready(function () {

            {{--$('#exportExcel').on('click', (evt) => {--}}
            {{--    let url = '{{ route('client.excel.export') }}';--}}
            {{--    exportClient('pdf', url);--}}
            {{--});--}}

            $('.toggle').click(function(e) {
                e.stopImmediatePropagation();
                $('#filterForm').toggle('slow');
            });

            $('.select2').select2();

            function msg(msg, type) {
                if (type == 'success') {
                    var clase = 'gritter-success';
                    var tit = '{{__('app.registered')}}';
                    var img = 'assets/img/ok.png';
                    var stincky = false;
                }
                if (type == 'error') {
                    var clase = 'gritter-error';
                    var tit = '{{ __('app.error') }}';
                    var img = 'assets/img/error.png';
                    var stincky = false;
                }
                if (type == 'debug') {
                    var clase = 'gritter-error gritter-center';
                    var tit = '{{__('app.internalError')}} (Debug - mode)';
                    var img = '';
                    var stincky = false;
                }
                if (type == 'info') {
                    var clase = 'gritter-info';
                    var tit = '{{ __('app.information') }}';
                    var img = 'assets/img/info.png';
                    var stincky = false;
                }
                if (type == 'mkerror') {
                    var clase = 'gritter-error';
                    var tit = '{{ __('app.errorFromMikrotik') }}';
                    var img = '';
                    var stincky = false;
                }

                if (type == 'system') {
                    var clase = 'gritter-light gritter-center';
                    var tit = '{{ __('app.systemInformation') }}';
                    var img = '';
                    var stincky = false;
                }

                $.gritter.add({
                    // (string | mandatory) the heading of the notification
                    title: tit,
                    // (string | mandatory) the text inside the notification
                    text: msg,
                    image: img, //in Ace demo dist will be replaced by correct assets path
                    sticky: stincky,
                    class_name: clase
                });
            }

            //agregar usuario
            $(document).on('click', '#addbtnviewClient', function (event) {
                event.stopImmediatePropagation();
                var data = $('#formaddvistas').serialize();
                var $btn = $(this).button('loading');
                $.post("{{ route('viewClient.create') }}", data, function (data) {
                    if (data.msg == 'success') {
                        $('#campos_visibles').modal('toggle');
                        var clase = 'gritter-success';
                        var tit = '{{__('app.registered')}}';
                        var img = 'assets/img/ok.png';
                        var stincky = false;
                        $.gritter.add({
                            title: tit,
                            text: '{{ __('messages.dataUpdatedSuccessfully') }}.',
                            image: img,
                            sticky: stincky,
                            class_name: clase
                        });
                        location.reload();
                    }


                    //Mesajes personalizados
                    if (data.msg == 'error') {
                        var arr = data.errors;
                        $.each(arr, function (index, value) {
                            if (value.length != 0) {
                                msg(value, 'error');
                            }
                        });
                    }

                    //fin de mensajes personalizados
                    $btn.button('reset');
                });
            });

            getClientStats();

            $('#plan-client-table').on('preXhr.dt', function (e, settings, data) {

                var control = $('#typecontrol').val();
                var status = $('#serviceStatus').val();
                var online = $('#online').val();
                var router = $('#routers_details').val();
                var plan = $('#plans').val();
                var expiration = $('#date_in').val();
                var cut = $('#cut').val();
                var client_name = $('#client_name').val();
                var ip_filter = $('#ip_filter').val();
                var client_status = $('#client_status').val();
                var client_type = $('#client_type').val();

                data['control'] = control;
                data['status'] = status;
                data['online'] = online;
                data['router'] = router;
                data['plan'] = plan;
                data['expiration'] = expiration;
                data['cut'] = cut;
                data['client_name'] = client_name;
                data['ip_filter'] = ip_filter;
                data['client_status'] = client_status;
                data['client_type'] = client_type;
            });

            $(document).on("click", "#search", function () {
                window.LaravelDataTables["plan-client-table"].draw();
                getClientStats();
            });

            //funcion para recuperar todos los registros
            $(document).on('click', '#searchall', function (event) {
                $('#filterForm').trigger("reset");
                window.LaravelDataTables["plan-client-table"].draw();

                // window.LaravelDataTables["plan-client-table"].draw();
                getClientStats();
            });

            // recargar tabla
            $(document).on("click", ".recargar", function (event) {
                window.LaravelDataTables["plan-client-table"].draw();
                check_is_online();
                getClientStats();
            });

            //function para verificar clientes en linea
            function check_is_online() {

                //obtenemos el tipo de control
                $.ajax({
                    "url": "crnc31hy55t",
                    "type": "GET",
                    "data": {},
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.result) {
                        window.LaravelDataTables["plan-client-table"].draw();
                    }

                });
            }

            function getClientStats() {
                let data = {};
                data.control = $('#typecontrol').val();
                data.status = $('#serviceStatus').val();
                data.online = $('#online').val();
                data.router = $('#routers_details').val();
                data.plan = $('#plans').val();
                data.expiration = $('#date_in').val();
                data.cut = $('#cut').val();
                data.client_name = $('#client_name').val();
                data.ip_filter = $('#ip_filter').val();
                data.client_status = $('#client_status').val();

                //obtenemos el tipo de control
                $.ajax({
                    "url": "{{ route('plan.client.filter-totals', $planId) }}",
                    "type": "POST",
                    "data": data,
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.data) {
                        $('#activeClients').text(data.data.active);
                        $('#inActiveClients').text(data.data.inactive);
                        $('#blockedClients').text(data.data.blocked);
                        $('#allClients').text(data.data.all);
                    }

                });
            }

            //bloquear cliente
            $(document).on("click", ".ban-client", function (event) {
                var idc = $(this).attr("id");

                bootbox.confirm('{{ __('messages.activateCustomerService') }}', function (result) {
                    if (result) {
                        $.ajax({
                            "type": "POST",
                            "url": "clients/ban",
                            "data": {"id": idc},
                            "dataType": "json",
                            'error': function (xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function (data) {
                            console.log(data)

                            if (data[0].msg == 'error') {
                                msg('{{ __('messages.CouldNotCut') }}', 'error');
                            }
                            if (data[0].msg == 'banned') {
                                msg('{{ __('messages.serviceWasCut') }}', 'info');
                                window.LaravelDataTables["plan-client-table"].draw();
                            }
                            if (data[0].msg == 'unbanned') {
                                msg('{{ __('messages.serviceIsActivated') }}', 'info');
                                window.LaravelDataTables["plan-client-table"].draw();
                                check_is_online();
                            }
                            if (data[0].msg == 'errorConnect')
                                msg('{{ __('messages.verifyThatonline') }}', 'error');
                            if (data[0].msg == 'errorConnectLogin')
                                msg('{{ __('messages.verifyTheAccessData') }}', 'error');

                            //mikrotik errors
                            if (data[0].msg == 'mkerror') {

                                $.each(data, function (index, value) {
                                    msg(value.message, 'mkerror');
                                });
                            }
                        });
                    }
                });


            });
            //fin de bloquear cliente

            //agregar cliente
            $(document).on('click', '.addbtnclient', function () {
                var data = $('#formaddclient1').serialize();
                var $btn = $(this).button('loading');

                $.easyAjax({
                    type: 'POST',
                    url: "clients/create-client",
                    data: $('#formaddclient1').serialize(),
                    container: "#formaddclient1",
                    messagePosition: "inline",
                });
                $btn.button('reset');
            });
            //fin de agregar cliente
            //funcion para hacer ping
            $(document).on('click', '#btnping', function (event) {
                event.preventDefault();
                /* Act on the event */
                var data = $('#formapingclient').serialize();

                $('#btnping').html('<i class="fa fa-cog fa-spin"></i> Haciendo ping...');

                $("#table-ping tbody tr").remove();

                $.ajax({
                    "url": baseUrl + "/tools/ping",
                    "type": "POST",
                    "data": data,
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {


                    $.each(data, function (i, val) {

                        var cl = '';

                        if (val['size'] === undefined)
                            var size = '-----'
                        else
                            var size = val['size'];

                        if (val['ttl'] === undefined) {
                            var ttl = '-----'
                        } else {
                            var ttl = val['ttl'];
                            var cl = 'green';
                        }


                        if (val['time'] === undefined) {
                            var time = val['status'];
                            var cl = 'red';
                        } else {
                            var time = val['time'];
                            var cl = 'green';
                        }


                        $('#table-ping').append('<tr><td>' + val['host'] + '</td><td>' + size + '</td><td>' + ttl + '</td><td class="' + cl + '">' + time + '</td></tr>');
                    });

                    $('#btnping').html('Iniciar');

                    if (typeof treload != "undefined" && typeof treload != undefined) {
                        console.log(typeof treload, 'Hello from typeof');
                        window.LaravelDataTables["plan-client-table"].draw();
                    }

                });


            });

            // Reset modal when it hides
            $('#add, #edit').on('hidden.bs.modal', function () {
                $('#formaddclient1, #ClientformEdit').find(".has-error").each(function () {
                    $(this).find(".help-block").text("");
                    $(this).removeClass("has-error");
                });
                $('#ClientformEdit #typedoc_cod').val('0');
                $('#ClientformEdit #economicactivity_cod').val('0').trigger('change');
                $('#ClientformEdit #municipio_cod').val('0').trigger('change');
                $('#ClientformEdit #typeresponsibility_cod').val('0');
                $('#ClientformEdit #typetaxpayer_cod').val('0');
                $('#ClientformEdit #edit_typedoc_cod').val('0');
                $('#ClientformEdit #edit_economicactivity_cod').val('0').trigger('change');
                $('#ClientformEdit #edit_municipio_cod').val('0').trigger('change');
                $('#ClientformEdit #edit_typeresponsibility_cod').val('ZZ');
                $('#ClientformEdit #edit_typetaxpayer_cod').val('0');
                $('#formaddclient1, #ClientformEdit').find("#alert").html("");
            });


            //eliminar cliente
            $(document).on("click", '.del', function (event) {
                var idp = $(this).attr("id");
                bootbox.confirm("{{__('messages.permanentlyEliminateThe')}}", function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            url: "clients/delete",
                            data: {"id": idp},
                            dataType: "json",
                            'error': function (xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function (data) {
                            window.LaravelDataTables["plan-client-table"].draw();
                            if (data[0].msg == 'success')
                                msg('{{ __('messages.clientWasDeleted') }}', 'success');
                            if (data[0].msg == 'errorConnect')
                                msg('{{ __('messages.removedFromTheRouterSince') }}', 'info');

                            if (data[0].msg == 'mkerror') {
                                $.each(data, function (index, value) {
                                    msg(value.message, 'mkerror');
                                });
                            }


                        });
                    }
                });
            });

            //funcion para obtener info del cliente
            $(document).on('click', '.infos', function (event) {
                event.preventDefault();
                /* Act on the event */

                var id = $(this).attr("id");

                //obtenemos el tipo de control
                $.ajax({
                    "url": '{{ route('client/getservice/info') }}',
                    "type": "POST",
                    "data": {'id': id},
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    $('#infotitle').text('Información - ' + data.name);
                    $('#infopaydate').text(data.paydate);
                    $('#infoplan').text(data.plan);

                    //reset class
                    $('#infoemail').removeClass('label-danger');
                    $('#infoemail').removeClass('label-success');
                    $('#infosms').removeClass('label-danger');
                    $('#infosms').removeClass('label-success');
                    $('#infoservice').removeClass('label-success');
                    $('#infoservice').removeClass('label-danger');

                    if (data.email == 'Desactivado') {
                        $('#infoemail').addClass('label-danger').text(data.email);
                    } else {
                        $('#infoemail').addClass('label-success').text(data.email);
                    }

                    if (data.sms == 'Desactivado') {
                        $('#infosms').addClass('label-danger').text(data.sms);
                    } else {
                        $('#infosms').addClass('label-success').text(data.sms);
                    }

                    $('#infocut').text(data.cut);

                    if (data.status == 'ac') {
                        $('#infoservice').text('Activo').addClass('label-success');
                    } else {
                        $('#infoservice').text('Cortado').addClass('label-danger');
                    }

                    $('#inforouter').text(data.router);
                    $('#infoip').text(data.ip);
                    $('#infomac').text(data.mac);

                    switch (data.control) {
                        case 'sq':
                            $('#infocontrol').text('Simple Queues');
                            break;
                        case 'st':
                            $('#infocontrol').text('Simple Queues (with Tree)');
                            break;
                        case 'ho':
                            $('#infocontrol').text('Hotspot - User Profiles');
                            break;
                        case 'ha':
                            $('#infocontrol').text('Hotspot - PCQ Address List');
                            break;
                        case 'dl':
                            $('#infocontrol').text('DHCP Leases');
                            break;
                        case 'pp':
                            $('#infocontrol').text('PPPoE - Secrets');
                            break;
                        case 'ps':
                            $('#infocontrol').text('PPPoE - Simple Queue');
                            break;
                        case 'pt':
                            $('#infocontrol').text('PPPoE - Secrets Simple Queues (with Tree)');
                            break;
                        case 'pa':
                            $('#infocontrol').text('PPPoE - Secrets - PCQ Address List');
                            break;
                        case 'pc':
                            $('#infocontrol').text('PCQ Address List');
                            break;
                        case 'ra':
                            $('#infocontrol').text('PPPoE - Simple Queue with Radius');
                            break;
                        default:
                            $('#infocontrol').text('Ninguno');
                    }

                    if (data.portal == '1') {
                        $('#infoportal').text('Si');
                    } else {
                        $('#infoportal').text('No');
                    }

                    $('#infoEmail').text(data.clientEmail);
                    $('#infoTelephone').text(data.clientPhone);
                    $('#infoDirection').text(data.clientAddress);
                    $('#infoRuci').text(data.clientDni);
                    $('#infoCoordinates').text(data.clientCoordinates);

                    $('#modalinfo').modal('show');

                });
            });

            //guardar editar cliente
            $(document).on("click", ".editbtnclient", function (event) {

                var clientdata = $('#ClientformEdit').serialize();
                var $btn = $(this).button('loading');

                $.easyAjax({
                    type: 'POST',
                    url: "clients/update-client",
                    data: $('#ClientformEdit').serialize(),
                    container: "#ClientformEdit",
                    messagePosition: "inline",
                    success: function (response) {
                        if (response.status == 'success') {
                            $('#edit').modal('hide')
                            window.LaravelDataTables["plan-client-table"].draw();
                        }
                    }
                });
                $btn.button('reset');
            });

            //fin guardar editar cliente
            //bloquear cliente
            $(document).on("click", ".ban-service", function (event) {
                var idc = $(this).attr("id");

                var url = '{{ route('billing.services.ban', ':id') }}';
                url = url.replace(':id', idc);

                bootbox.dialog({
                    message: '<form class="bootbox-form"><input class="bootbox-input bootbox-input-text form-control" name="reason" id="reason" autocomplete="off" type="text"></form>',
                    title: "{{ __('messages.activateCustomerService') }}",
                    buttons: {
                        cancel: {
                            label: "Cancel",
                            className: 'btn-danger',
                            callback: function(result){
                                console.log('Custom cancel clicked');
                            }
                        },
                        confirm: {
                            label: "Confirm",
                            className: 'btn-info',
                            callback: function(){

                                var result =  $('#reason').val();
                                if (result) {
                                    $.ajax({
                                        "type": "POST",
                                        "url": url,
                                        "data": {"id": idc, reason: result},
                                        "dataType": "json",
                                        'error': function (xhr, ajaxOptions, thrownError) {
                                            debug(xhr, thrownError);
                                        }
                                    }).done(function (data) {
                                        if (data[0].msg == 'error') {
                                            msg('{{ __('messages.CouldNotCut') }}', 'error');
                                        }

                                        if (data[0].msg == 'banned') {
                                            msg('{{ __('messages.serviceWasCut') }}', 'info');
                                            window.LaravelDataTables["plan-client-table"].draw();
                                        }

                                        if (data[0].msg == 'unbanned') {
                                            msg('{{ __('messages.serviceIsActivated') }}', 'info');
                                            window.LaravelDataTables["plan-client-table"].draw();
                                            check_is_online();
                                        }

                                        if (data[0].msg == 'errorConnect')
                                            msg('{{ __('messages.verifyThatonline') }}', 'error');

                                        if (data[0].msg == 'errorConnectLogin')
                                            msg('{{ __('messages.verifyTheAccessData') }}', 'error');
                                        //mikrotik errors
                                        if (data[0].msg == 'mkerror') {
                                            $.each(data, function (index, value) {
                                                msg(value.message, 'mkerror');
                                            });
                                        }
                                    });
                                }
                                else {
                                    msg('The reason field is required.', 'error');
                                    return false;
                                }
                            }
                        }
                    }
                });


            });
        });

        function readable(number) {
            return number.toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function exportClient(type, url) {
                let data = {
                    control : $('#typecontrol').val(),
                    status : $('#serviceStatus').val(),
                    online : $('#online').val(),
                    router : $('#routers_details').val(),
                    plan : $('#plans').val(),
                    expiration : $('#date_in').val(),
                    cut : $('#cut').val(),
                    client_name : $('#client_name').val(),
                    ip_filter : $('#ip_filter').val(),
                    client_status : $('#client_status').val()
                };

            let query = 'control='+data.control+'&status='+data.status+'&online='+data.online+'&router='+data.router+'&plan='+data.plan+'&expiration='+data.expiration+'&cut='+data.cut+'&client_name='+data.client_name+'&ip_filter='+data.ip_filter+'&client_status='+data.status+'&type='+type;

            var a = document.createElement('a');
            a.href = url + '?' + query;
            a.setAttribute('target', '_blank');
            a.click();
            a.remove();
        }

        function trafficWindow() {
            initWindow();
            initSelector();
            buildTable();
        }


        function initWindow() {
            $('#estadisticas').append('<div class="window" data-id="traffic"></div>');
            $('#estadisticas').html('<div class="movebar">Estadisticas Mensuales</div><div class="toolbar"></div><div class="content"></div>');
            $('#estadisticas').on('click', '.close', function () {
                $('#estadisticas').remove();
            })
        }

        function buildTable() {
            var supertotal = 0;

            $('.content').html('<table id="table_estadis"><tr data-traffic="999999999999999999"><th></th><th>Name</th><th>Total</th><th>Down</th><th>Up</th><th>Queue</th><th></th></tr></table>');
            $.ajax({
                "url": "client/getclient/consumo",
                "type": "POST",
                "data": {
                    month: $('.display').attr('month'),
                    year: $('.display').attr('year'),
                    user: $('#namecl').val()
                },
                "dataType": "json",
                success: function (data) {
                    $.each(data, function (key, item) {

                        var total = (item.upMB + item.downMB);
                        $('.content table').append('<tr class="queue" data-traffic="' + total + '" data-server="' + item.lastserver + '" data-id="' + key + '"><td class="link"><a target="_blank" href="http://' + item.lastserver + '">🔗</a></td><td class="name">' + key + '</td><td>' + readable(total / 1024) + ' GB</td><td>' + readable(item.downMB / 1024) + ' GB</td><td>' + readable(item.upMB / 1024) + ' GB</td><td class="queues">' + item.queueUp + ' / ' + item.queueDown + '</td><td class="bars"></td></tr>');

                        $bars = $('[data-id="' + key + '"] .bars');
                        $bars.append('<div class="total" data-id="' + total + '">&nbsp;</div>');
                        $bars.append('<div class="down" data-id="' + item.downMB + '">&nbsp;</div>');
                        $bars.append('<div class="up" data-id="' + item.upMB + '">&nbsp;</div>');

                        supertotal = supertotal + total;

                    })

                    $('.supertotal').remove();
                    $('.toolbar').prepend('<div class="supertotal">Total: ' + readable(supertotal / 1024) + ' GB</div>');

                    beautify();
                }
            });
        }


        function initSelector() {

            $('.toolbar').prepend('<div class="selector"><div class="pre"><</div><div class="display"></div><div class="next">></div></div>');

            var year = (new Date()).getFullYear();
            var month = (new Date()).getMonth() + 1;
            $('.display').html(year + '-' + month).attr('year', year).attr('month', month);

            $('body').on('click', '.pre', function () {
                var year = $('.display').attr('year');
                var month = $('.display').attr('month') - 1;
                if (month == 0) {
                    month = 12;
                    year = year - 1;
                }
                $('.display').html(year + '-' + month).attr('year', year).attr('month', month);
                buildTable();
            })

            $('body').on('click', '.next', function () {
                var year = $('.display').attr('year');
                var month = parseInt($('.display').attr('month')) + 1;
                if (month == 13) {
                    month = 1;
                    year = parseInt(year) + 1;
                }
                $('.display').html(year + '-' + month).attr('year', year).attr('month', month);
                buildTable();
            })

            $('.toolbar').prepend('<div class="refresh">Actualizar</div>');
            $('body').on('click', '.refresh', function () {
                $('body').addClass('loading');
                $(this).addClass('active');
                $.ajax({
                    dataType: "html",
                    url: '/guardar_crn_consumo',
                    success: function (data) {
                        buildTable();
                        $('body').removeClass('loading');
                        $('.refresh').removeClass('active');
                    }
                })
            })

        }


        function beautify() {
            var max = 0;
            $('.queue').each(function () {
                var traffic = parseInt($(this).attr('data-traffic'));
                if (traffic >= max) {
                    max = traffic;
                }
            })

            $('.queue .bars > div').each(function () {
                var val = $(this).attr('data-id');
                var percent = 100 / max * val;
                if (max == 0) {
                    percent = 0;
                }
                $(this).css('width', percent + '%');
            })

            // store the li items
            // $("table tr").sort(sort_li).appendTo('#table_estadis'); // append again to the list
            // sort function callback
            function sort_li(a, b) {
                return ($(b).data('traffic')) > ($(a).data('traffic')) ? 1 : -1;
            }

        }

        function initClock() {

            startTime();

            function startTime() {
                var today = new Date();
                var h = today.getHours();
                var m = today.getMinutes();
                var s = today.getSeconds();
                m = checkTime(m);
                s = checkTime(s);
                $('.clock').html(h + ":" + m + ":" + s);
                var t = setTimeout(startTime, 500);
            }

            function checkTime(i) {
                if (i < 10) {
                    i = "0" + i
                }
                ;  // add zero in front of numbers < 10
                return i;
            }
        }

        @if(Auth::user()->id!=1)
        setTimeout(function () {
            $('.del').hide();
        }, 1000);
        @endif

        $('.date-picker').datepicker({
            language: 'es',
            autoclose: true,
            todayHighlight: true
            //startView: 'year',
        });

    </script>

@endsection
