@extends('layouts.master')

@section('title',__('app.clients'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tokenfield-typeahead.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.structure.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.theme.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/ace-corrections.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.min.css') }}">
    <script src="{{ asset('assets/js/lib_firma_sri/js/fiddle.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/uft8.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/forge.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib_firma_sri/js/buffer.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>

    <style type="text/css">
        select:disabled {
            cursor:not-allowed !important;
        }
        input[type=checkbox].ace:disabled+.lbl::before{
            cursor:not-allowed !important;
        }
     .load_t{
        position: absolute;
        left: 0;
        bottom: 0;
        top: 0;
        right: 0;
        background: #0000009e;
        z-index: 9999;
        display: none;
    }
    #idrefre{
        position: absolute;
        top: 15px;
        right: 295px;
        background: #399811;
        padding: 4px 12px;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }
    #idrefre:hover{
        background: #000;
    }
    </style>

    <style>
        .pac-container {
            z-index: 99999;
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

        .alert {
            margin: 0 0 15px 0 !important;
        }
        .tab-content {
            background: #fff !important;
        }
        .btn-smm {
            padding: 1px 7px !important;
        }
         table.table-condensed>tbody>tr>td {
             border-top: 0 solid #ddd !important;
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
                        <a href="{{ URL::to('admin') }}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="{{ URL::to('clients') }}">@lang('app.clients')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <a href="{{ URL::to('clients') }}">@lang('app.clients')</a>
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ $clients->name }}
                        </small>
                        <div id="updateButtons" class="col-lg-2 pull-right">
                            <a class="btn btn-sm btn-smm btn-purple editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="{{ $clients->id }}" title="Editar" type="button">
                                <i class="fa fa-edit" aria-hidden="true"></i> Editar Cliente</a>
                        </div>
                    </h1>



                </div>

                <!--cuerpo Modal-->
                <div class="tabbable">
                    <ul class="nav nav-tabs" id="ro">
                        <li class="active"><a data-toggle="tab" href="#billingView" onclick="loadView('billing'); return false;"><i
                            class="fa fa-info-circle"></i> @lang('app.invoiceView')</a>
                        </li>
                        <li id="internet"><a href="#services" role="tab"
                                             data-toggle="tab" onclick="loadView('services'); return false;"><i
                                    class="fa fa-credit-card"></i> @lang('app.service')</a></li>
                        <li id="redes"><a href="#connection" id="transaction_click" role="tab"
                          data-toggle="tab" onclick="loadView('transactions'); return false;"><i
                          class="fa fa-sitemap"></i> @lang('app.transactions')</a></li>
                          <li id="internet"><a href="#bill" role="tab"
                           data-toggle="tab" onclick="loadView('invoices'); return false;"><i
                           class="fa fa-list-alt"></i> @lang('app.bills')</a></li>
                           <li id="internet"><a href="#payments" role="tab"
                               data-toggle="tab" onclick="loadView('payments'); return false;"><i
                               class="fa fa-credit-card"></i> @lang('app.payments')</a></li>
                           <li id="statistics"><a href="#statistics" role="tab"
                               data-toggle="tab" onclick="loadView('statistics'); return false;"><i
                               class="fa fa-credit-card"></i> @lang('app.statistics')</a></li>
                           <li id="documents"><a href="#documents" role="tab"
                               data-toggle="tab" onclick="loadView('documents'); return false;"><i
                               class="fa fa-credit-card"></i> @lang('app.documents')</a></li>
                           </ul>
                           <div class="tab-content" id="mytabs">
                            <div id="billingView" class="tab-pane fade in active">

                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="openssl" value="{{ $openssl ?? "" }}">



                </div>
            </div>
        </div>

    <input id="val" type="hidden" name="client" value="">

    <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-2" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span
                            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
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
                            <label for="name" class="col-sm-3 control-label">{{ __('app.fullName') }} <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="edit_name"
                                       class="form-control" autocomplete="off"
                                       id="edit_name" maxlength="100">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="col-sm-3 control-label">{{ __('app.telephone') }} <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="edit_phone"
                                       class="form-control" autocomplete="off"
                                       id="edit_phone" maxlength="25">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-3 control-label">{{ __('app.email') }} <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="email" name="edit_email"
                                       class="form-control" autocomplete="off"
                                       id="edit_email" maxlength="60">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dni" class="col-sm-3 control-label">{{ __('app.number') }}
                                DNI/CI <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="edit_dni"
                                       class="form-control" autocomplete="off"
                                       id="edit_dni" maxlength="20">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">{{ __('app.direction') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="edit_dir"
                                       class="form-control" autocomplete="off"
                                       id="edit_dir" maxlength="100">
                            </div>
                        </div>

                         {{-- nuevos campos --}}

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

                         {{-- nuevos campos --}}
                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">NAPs</label>
                            <div class="col-sm-9">
                                <select class="form-control onchange_caja" id="edit_odb_id" name="edit_odb_id">
                                    <option value="">Seleccione NAPs</option>
                                    @foreach($OdbSplitter as $zo)
                                        <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group content_port_s" >
                            <label for="dir" class="col-sm-3 control-label">Puertos Disponibles</label>
                            <div class="col-sm-9">
                                <select class="form-control select_port" id="select_port_edit" name="edit_port">

                                </select>
                            </div>
                        </div>

                        <div class="form-group content_port_s">
                            <label for="dir" class="col-sm-3 control-label">Zonas</label>
                            <div class="col-sm-9" >
                                <span class="zone_info"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dir" class="col-sm-3 control-label">ONUs/CPE</label>
                            <div class="col-sm-9">
                                <select class="form-control change_unus" id="edit_onu_id" name="edit_onu_id">
                                    <option value="">Seleccione ONUs/CPE</option>
                                    @foreach($OnuType as $on)
                                        <option value="{{ $on->id }}" data-type="{{ $on->pontype }}">{{ $on->onutype }} -- ({{ $on->pontype }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <input type="hidden" class="zona_value" name="edit_zona_id" id="zona_id_edit">
                        <div class="form-group content_zona_select">
                            <label for="dir" class="col-sm-3 control-label">Zonas</label>
                            <div class="col-sm-9" >

                                <select class="form-control zona_id_input" name="zona_ident" id="edit_zona_id">
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
                                   class="col-sm-3 control-label">{{ __('app.password') }} <span class="text-danger">*</span></label>
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

    <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmapedit" role="dialog"
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
@endsection

@section('scripts')
    @if($map!='0')
        <script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
    @endif

    <script src="{{asset('assets/js/highchart/code/highcharts.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/modules/exporting.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/themes/grid.js')}}"></script>
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/moment/moment.min.js')}}"></script>
    <script src="{{asset('assets/js/moment/moment-with-locales.min.js')}}"></script>

    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    <script src="{{asset('assets/js/pGenerator.jquery.js')}}"></script>
    <script src="{{asset('assets/js/bootstrap-typeahead.min.js')}}"></script>

    <script src="{{asset('assets/js/fuelux/fuelux.spinner.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/daterangepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/date-time/locales/bootstrap-datepicker.es.js')}}"></script>

    <script type="text/javascript">
        var caja_load=false;
        function multi_select(ident) {
            caja_load=false;
            if (ident != "") {
                $.ajax({
                    "url": baseUrl+"/client/getclient/caja",
                    "type": "POST",
                    "data": { "id": ident },
                    "dataType": "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {
                    if (data.success == true) {
                        $('.content_zona_select').hide();
                        caja_load=true;
                        $('.content_port_s').show();
                        $('.zone_info').text(data.zone);
                        $('#zona_id_input').val(data.zone_id);
                        $('#zona_id_edit').val(data.zone_id);
                        $(".select_port").html('');
                        var add2 = "<option value=''>Seleccione puerto</option>";
                        $(".select_port").append(add2);
                        var cant = data.detail.port;
                        if (cant > 0) {
                            cant = cant + 1;
                        }
                        for (i = 1; i < cant; i++) {
                            var status_sw = true;
                            $.each(data.port, function(key, value) {
                                if (value == i) {
                                    status_sw = false;
                                }
                            });
                            if (status_sw) {
                                var add = "<option value='" + i + "'>" + i + "</option>";
                                $(".select_port").append(add);
                            }

                        }
                    }
                });
            }
        }

        $("#edit_zona_id").change(function() {
            var ident = $(this).val();
            $('#zona_id_edit').val(ident);

        });

        $("#zona_id").change(function() {
            var ident = $(this).val();
            $('#zona_id_input').val(ident);

        });

        $(".change_unus").change(function() {

            var ident = $(this).val();
            $('.content_zona_select').hide();

            var type = $('option:selected', this).data("type");
            //cargada
            if(!caja_load){
                //Buscamos el tipo
                if(type=="CPE"){
                    $('.content_zona_select').show();
                }


            }

        });

        $(".onchange_caja").change(function() {
            var ident = $(this).val();
            $('.zone_info').text('');
            $('.content_port_s').hide();
            multi_select(ident);
        });

        (function($) {
            $.toggleShowPassword = function(options) {
                var settings = $.extend({
                    field: "#password",
                    control: "#toggle_show_password",
                }, options);

                var control = $(settings.control);
                var field = $(settings.field)

                control.bind('click', function() {
                    if (control.is(':checked')) {
                        field.attr('type', 'text');
                    } else {
                        field.attr('type', 'password');
                    }
                })
            };
        }(jQuery));

        //Here how to call above plugin from everywhere in your application document body
        $.toggleShowPassword({
            field: '#edit_pass2',
            control: '#edshowp'
        });

        //generar password para portal cliente
        $('#edgenporpass').pGenerator({

            'bind': 'click',
            'passwordElement': '#edit_pass2',
            'passwordLength': 6,
            'uppercase': false,
            'lowercase': true,
            'numbers': true,
            'specialChars': false,
        });

        $(document).on("click", '.editar', function(event) {
            $('#winedit').waiting({ fixed: true });
            var idu = $(this).attr('id');
            $('[name=client]').val(idu);
            var fdata = $('#val').serialize();

            $('#load').show();
            $.ajax({
                type: "POST",
                url: "{{ route('admin.client.getclient') }}",
                data: fdata,
                dataType: "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {
                if (data.success) {
                    //obtenemos todos los router y seleccionamos el router que tiene el usuario
                    var router = data.router;
                    var type_auth = data.type_auth;
                    //get routers
                    $('.zone_info').text('');
                    $('.content_port_s').hide();

                    $('#ClientformEdit2 input[name="edit_user"]').val(data.user);
                    $('#ClientformEdit input[name="client_id"]').val(data.id);
                    $('#ClientformEdit input[name="edit_name"]').val(data.name);
                    $('#ClientformEdit input[name="edit_phone"]').val(data.phone);
                    $('#ClientformEdit input[name="edit_email"]').val(data.email);
                    $('#ClientformEdit input[name="edit_dni"]').val(data.dni);
                    $('#ClientformEdit input[name="edit_dir"]').val(data.dir);
                    $('#edit_onusn').val(data.onusn);
                    $('#ClientformEdit input[name="location_edit"]').val(data.coordinates);
                    $('#edit_odb_id').prop('selected', false).find('option:first').prop('selected', true);
                    $('#edit_onu_id').prop('selected', false).find('option:first').prop('selected', true);
                    $('.content_zona_select').hide();
                    if (data.odb_id != null) {
                        $('#edit_odb_id').prop('selected', true).find("option[value=" + data.odb_id + "]").prop('selected', true);
                        var ok = multi_select(data.odb_id);
                        setTimeout(function () {

                            if (data.port != null) {
                                var id_s = data.port;
                                $("#select_port_edit").append("<option value='" + id_s + "'>" + id_s + "</option>");
                                $('#select_port_edit').prop('selected', true).find("option[value=" + id_s + "]").prop('selected', true);
                            }
                        }, 900);


                    } else {
                        $('#edit_odb_id').prop('selected', false).find('option:first').prop('selected', true);
                    }


                    if (data.onu_id != null) {
                        $('#edit_onu_id').prop('selected', true).find("option[value=" + data.onu_id + "]").prop('selected', true);

                        if (data.type_onu == "CPE") {
                            $('#edit_zona_id').prop('selected', true).find("option[value=" + data.zona_id + "]").prop('selected', true);
                            $('.content_zona_select').show();
                        }


                    } else {
                        $('#edit_onu_id').prop('selected', false).find('option:first').prop('selected', true);
                    }

                }
            });
        });

        //validate coordinates
        function validateNewPlantsForm(latlng) {
            var latlngArray = latlng.split(",");
            for (var i = 0; i < latlngArray.length; i++) {
                if (isNaN(latlngArray[i]) || latlngArray[i] < -127 || latlngArray[i] > 75) {
                    msg('Coordenadas no validas.', 'error');
                    return false;
                }
            }

            return latlngArray;
        }

        function openmap(lat, lon, windowSelector, searchBox, locatioBox) {
            $(windowSelector).locationpicker({

                location: {
                    latitude: lat,
                    longitude: lon
                },

                radius: 0,
                inputBinding: {
                    locationNameInput: $(searchBox)
                },
                mapOptions: { mapTypeControl: true, streetViewControl: true },
                enableAutocomplete: true,
                //markerIcon: 'http://www.iconsdb.com/icons/preview/tropical-blue/map-marker-2-xl.png'
                onchanged: function(currentLocation, radius, isMarkerDropped) {
                    $(locatioBox).val(currentLocation.latitude + "," + currentLocation.longitude);
                }
            });
        }

        $('#modalmapedit').on('shown.bs.modal', function() {

            var map = validateNewPlantsForm($('#edilocation').val());

            if (map != false) {

                var lat_e = map[0];
                var lon_e = map[1];

                openmap(lat_e, lon_e, '#us4', '#us4-address', '#edilocation');

            } else {

                //intentamos recuperar la información de ubicacion del router
                $.ajax({
                    "url": baseUrl+"/config/getconfig/defaultlocation",
                    "type": "GET",
                    "data": {},
                    "dataType": "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {


                    if (data.coordinates == '0') {
                        var lat_e = '-34.60368440000001';
                        var lon_e = '-58.381559100000004';
                    } else {

                        var cor = validateNewPlantsForm(data.coordinates);

                        if (cor != false) {
                            var lat_e = cor[0];
                            var lon_e = cor[1];

                        } else {
                            var lat_e = '-34.60368440000001';
                            var lon_e = '-58.381559100000004';
                        }
                    }

                    openmap(lat_e, lon_e, '#us4', '#us4-address', '#edilocation');

                }); //end ajax
            }
        });


        //guardar editar cliente
        $(document).on("click",".editbtnclient",function(event){

            var clientdata = $('#ClientformEdit').serialize();
            var $btn = $(this).button('loading');

            $.easyAjax({
                type: 'POST',
                url: "{{ route('admin.client-update') }}",
                data: $('#ClientformEdit').serialize(),
                container: "#ClientformEdit",
                messagePosition:"inline",
                success: function(response) {
                    if(response.status == 'success') {
                        $('#edit').modal('hide')
                    }
                }
            });
            $btn.button('reset');
        });


      function loadView (page) {
        var url = '{{ route('billing.pages', $clients->id) }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {
                page: page,
            },
            container: "#ro",
            success: function(res) {
                if(res.status == 'success') {
                    $('#billingView').html(res.view);
                }
            }
        });
    }


    $( document ).ready(function() {



          // loadView('billing');
 // store the currently selected tab in the hash value
 $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
  var id = $(e.target).attr("href").substr(1);

  window.location.hash = id;

});

 var hash = window.location.hash;
 $('#ro a[href="' + hash + '"]').tab('show');

 if(hash=='#connection'){
    loadView('transactions');
}else if(hash=='#bill'){
    loadView('invoices');
}else if(hash=='#payments'){
    loadView('payments');
}else if(hash=='#services'){
    loadView('services');
}else if(hash=='#statistics'){
    loadView('statistics');
}else{
   loadView('billing');
}

});

</script>
@endsection
