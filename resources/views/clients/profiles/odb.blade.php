@extends('layouts.master')

@section('title', 'Perfiles')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
   integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
   crossorigin=""/>

    <style type="text/css" media="screen">
        .negro_c {
            color: #000 !important;
        }
    </style>

    <style>
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

        /*	.bs-edit-modal-lg{
                z-index: 999 !important;
            }
            .modal-backdrop {
                z-index: 99 !important;
                }*/

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

        /*	#modalmap{
                z-index: 99999999;
                }*/
        span.ports {
            padding: 2px 6px;
            color: #fff;
            margin-right: 2px;
            background-color: #28a745;
            border-radius: 20%;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}"/>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
            @include('includes.zone-tabs')
            <!--start row-->
                <div class="row">
                    <div class="col-sm-12">
                        <!--Inicio de tab simple queues-->
                        <button class="btn btn-success" data-toggle="modal" data-target="#add"><i class="icon-plus"></i> {{ __('app.add') }} Caja
                        </button>
                        <br>
                        <br>
                        <br>

                        <!--Inicio tabla planes simple queues-->
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">Todas las Cajas</h5>
                                <div class="widget-toolbar">
                                    <div class="widget-menu">
                                        <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                            <i class="ace-icon fa fa-bars"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                            <li>
                                                <a href="#" data-toggle="modal" class="peref" data-target="#add"><i
                                                            class="fa fa-plus-circle"></i> {{ __('app.new') }} Zona</a>
                                            </li>

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
                                    {{ __('app.add') }} {{ __('app.new') }} OBD (Splitter)</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal" role="form" id="formaddplan">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">{{ __('app.name') }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" id="namepl"
                                                   maxlength="30">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Cantidad de puertos</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="port" name="port">
                                                <option value="">Seleccione cantidad de puertos</option>
                                                @for ($i = 1; $i <= 16; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Zona</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="zone_id" name="zone_id">
                                                <option value="">Seleccione Zona</option>
                                                @foreach($zone as $zo)
                                                    <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="location"
                                               class="col-sm-2 control-label">{{ __('app.location') }}</label>
                                        <div class="col-sm-8">
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

                <!--Modal Google Maps-->
                <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmap" role="dialog"
                     aria-labelledby="myLargeModalLabel" data-map-type="{{ $global->map_type }}">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> @lang('app.map_types.' . $global->map_type ?? '')</h4>
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

                <!--Inicio de modal editar plan -->
                <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span
                                            id="load2"><i class="fa fa-cog fa-spin"></i> @lang('app.loading')</span>
                                    Editar Zone</h4>
                            </div>
                            <div class="modal-body" id="winedit">
                                <form class="form-horizontal" role="form" id="PlanformEdit">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">{{ __('app.name') }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="edit_name" class="form-control" id="edit_name"
                                                   maxlength="30">
                                            <input type="hidden" name="plan_id">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Cantidad de puertos</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="edit_port" name="edit_port">
                                                <option value="">Seleccione cantidad de puertos</option>
                                                @for ($i = 1; $i <= 16; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">Zona</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" id="edit_zone_id" name="edit_zone_id">
                                                @foreach($zone as $zo)
                                                    <option value="{{ $zo->id }}">{{ $zo->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">

                                        <label for="location"
                                               class="col-sm-2 control-label">{{ __('app.location') }}</label>
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}</button>
                                <button type="button" class="btn btn-primary" id="editbtnplan"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmapedit" role="dialog"
                     aria-labelledby="myLargeModalLabel" data-map-type="{{ $global->map_type }}">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> @lang('app.map_types.' . $global->map_type ?? '')</h4>
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
    {{-- <script src="{{asset('assets/js/rocket/plans-core.js')}}"></script> --}}
    @if($map!='0')
        <script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
        <script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
        <script src="{{asset('assets/js/typeahead.jquery.min.js')}}"></script>
	    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js" ntegrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ==" crossorigin=""></script>
    @endif

    {!! $dataTable->scripts() !!}

    <script type="text/javascript">
        function msg_alert(msg, type) {
            if (type == 'success') {
                var clase = 'gritter-success';
                var tit = '{{__('app.registered')}}';
                var img = '/assets/img/ok.png';
                var stincky = false;
            }
            if (type == 'error') {
                var clase = 'gritter-error';
                var tit = '{{ __('app.error') }}';
                var img = '/assets/img/error.png';
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
                var img = '/assets/img/info.png';
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
    </script>

    <script type="text/javascript">
        $(document).ready(function (e) {


            ///Google maps
            //validate coordinates
            function validateNewPlantsForm(latlng) {
                var latlngArray = latlng.split(",");
                for (var i = 0; i < latlngArray.length; i++) {
                    if (isNaN(latlngArray[i]) || latlngArray[i] < -127 || latlngArray[i] > 90) {
                        msg('Coordenadas no validas.', 'error');
                        return false;
                    }
                }

                return latlngArray;
            }


            function openmap(lat, lon, windowSelector, searchBox, locatioBox, map_type = 'google_map') {

                switch (map_type) {
                    case 'google_map':
                        load_google_map(lat, lon, windowSelector, searchBox, locatioBox);
                        break;
                    case 'open_street_map':
                        load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox);
                        break;
                }
            }

            function load_google_map(lat, lon, windowSelector, searchBox, locatioBox) {
                $('#' + windowSelector).locationpicker({

                    location: {
                        latitude: lat,
                        longitude: lon
                    },
                    radius: 0,
                    inputBinding: {
                        locationNameInput: $(searchBox)
                    },
                    mapOptions: {mapTypeControl: true, streetViewControl: true},
                    enableAutocomplete: true,
                    //markerIcon: 'http://www.iconsdb.com/icons/preview/tropical-blue/map-marker-2-xl.png'
                    onchanged: function (currentLocation, radius, isMarkerDropped) {
                        $(locatioBox).val(currentLocation.latitude + "," + currentLocation.longitude);
                    }
                });
            }

            function destroy_osm() {
                if (typeof window.osmMap != 'undefined' && window.osMap == null) {
                    window.osmMap.remove();
                    window.osmMap = null;
                }
            }

            function open_street_map(lat, lon, windowSelector, locatioBox) {
                if (typeof window.osmMap == 'undefined' || window.osMap == null) {
                    let osm_layer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    });
                    window.osmMap = L.map(windowSelector, {
                        center: [lat, lon],
                        zoom: 15,
                        layers: [osm_layer]
                    });

                    let esri_satellite_layer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                    });
                    let esri_places_layer = L.tileLayer('https://server.arcgisonline.com/arcgis/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}');
                    var baseMaps = {
                        "OpenStreetMap": osm_layer,
                        "Satellite": esri_satellite_layer
                    };

                    var overlayMaps = {
                        "Boundaries & Places": esri_places_layer
                    };
                    L.control.layers(baseMaps, overlayMaps).addTo(window.osmMap);
                    window.osmMarker = L.marker([lat, lon], {
                        'draggable': true
                    }).addTo(window.osmMap);

                    window.osmMarker.on('moveend', function () {
                        let currentLocation = window.osmMarker.getLatLng();
                        $(locatioBox).val(currentLocation.lat + "," + currentLocation.lng);
                    });
                }
                return {
                    'map': window.osmMap,
                    'marker': window.osmMarker
                };
            }
            function load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox) {

                let osm = open_street_map(lat, lon, windowSelector, locatioBox);

                $(`[data-map-type="open_street_map"] ${searchBox}`).typeahead({
                    highlight: true,
                },
                {
                    name: 'brands',
                    display: 'value',
                    source: function(query, syncResults, asyncResults) {
                        return $.get('https://photon.komoot.io/api/?q=' + query, function(data) {
                            data_set = [];
                            $(data.features).each(function (index, item) {
                                const city = typeof item.properties.city != 'undefined' ? ` - ${item.properties.city}` : '';
                                const state = typeof item.properties.state != 'undefined' ? ` - ${item.properties.state}` : '';
                                const postcode = typeof item.properties.postcode != 'undefined' ? ` - ${item.properties.postcode}` : '';

                                data_set.push({
                                    id: index,
                                    latlng: item.geometry.coordinates, 
                                    value: `${item.properties.name} ${city} ${state} - ${item.properties.country} ${postcode}`
                                });

                            });
                            return asyncResults(data_set);
                        }, 'json');
                    }
                });

                $(`[data-map-type="open_street_map"] ${searchBox}`).on('typeahead:selected', function(evt, item) {
                    osm.map.flyTo([item.latlng[1], item.latlng[0]], 15);
                    osm.marker.setLatLng([item.latlng[1], item.latlng[0]]);
                    $(locatioBox).val(item.latlng[1] + "," + item.latlng[0]);
                })
            }

            $('#modalmap').on('shown.bs.modal', function () {
                destroy_osm();
                //$('#mapshow').locationpicker('autosize');
                let map_type = $(this).data('map-type');
                var cor = validateNewPlantsForm($('#location').val());

                if (cor != false) {

                    var lat = cor[0];
                    var lon = cor[1];

                    openmap(lat, lon, 'us3', '#us3-address', '#location', map_type);

                } else {

                    //intentamos recuperar la informaciÃ³n de ubicacion del router
                    $.ajax({
                        "url": "/config/getconfig/defaultlocation",
                        "type": "GET",
                        "data": {},
                        "dataType": "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {


                        if (data.coordinates == '0') {
                            var lat = '-34.60368440000001';
                            var lon = '-58.381559100000004';
                        } else {

                            var cor = validateNewPlantsForm(data.coordinates);

                            if (cor != false) {
                                var lat = cor[0];
                                var lon = cor[1];

                            } else {
                                var lat = '-34.60368440000001';
                                var lon = '-58.381559100000004';
                            }
                        }

                        openmap(lat, lon, 'us3', '#us3-address', '#location', map_type);

                    }); //end ajax
                }

            });


            //mostrar mapa al editar router

            $('#modalmapedit').on('shown.bs.modal', function (event) {
                destroy_osm();
                event.stopImmediatePropagation();
                let map_type = $(this).data('map-type');
                var map = validateNewPlantsForm($('#edilocation').val());

                if (map != false) {

                    var lat_e = map[0];
                    var lon_e = map[1];

                    openmap(lat_e, lon_e, 'us4', '#us4-address', '#edilocation', map_type);

                } else {

                    //intentamos recuperar la informaciÃ³n de ubicacion del router
                    $.ajax({
                        "url": "/config/getconfig/defaultlocation",
                        "type": "GET",
                        "data": {},
                        "dataType": "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {


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

                        openmap(lat_e, lon_e, 'us4', '#us4-address', '#edilocation', map_type);

                    }); //end ajax
                }
            });

            ///End Google maps

            //fin de ready
        });
    </script>

    <script>
        //añadir plan
        $(document).on("click", "#addbtnplan", function (event) {

            event.stopImmediatePropagation();


            $.easyAjax({
                type: "POST",
                url: "{{ route('profiles.odb.create') }}",
                data: $('#formaddplan').serialize(),
                container: '#formaddplan',
                success: function (data) {
                    if (data.msg == 'errorDownload')
                        msg_alert('EL campo descarga no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');
                    if (data.msg == 'errorUpload')
                        msg_alert('EL campo subida no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');

                    if (data.msg == 'success') {
                        $('#add').modal('toggle');
                        $('#formaddplan')[0].reset();//reseteamos el formulario
                        msg_alert('La Caja fue añadida correctamente.', 'success');
                        window.LaravelDataTables["odb-table"].draw();
                        $('#formaddplan')[0].reset();
                    }
                }
            })
        });

        //get editar plan
        $(document).on("click", '.editar', function (event) {
            event.stopImmediatePropagation();
            $('#PlanformEdit input[name="edit_coordinates"]').val('');
            $('#edilocation').val('');
            $('[name=plan]').val($(this).attr('id'));
            $('#winedit').waiting({fixed: true});
            var fdata = $('#val').serialize();
            $('#load2').show();


            $('#PlanformEdit').find(".has-error").each(function () {
                $(this).find(".help-block").text("");
                $(this).removeClass("has-error");
            });

            $.easyAjax({
                type: "POST",
                url: "{{ route('profiles.odb.data') }}",
                data: $('#val').serialize(),
                container: '#PlanformEdit',
                success: function (data) {
                    if (data.success) {

                        var myText = data.priority;

                        $("#editpriority").children().filter(function () {
                            return $(this).val() == myText;
                        }).prop('selected', true);

                        $('#PlanformEdit input[name="plan_id"]').val(data.id);
                        $('#PlanformEdit input[name="edit_name"]').val(data.name);
                        $("#edit_port option[value=" + data.port + "]").attr("selected", true);
                        $("#edit_zone_id option[value=" + data.zone_id + "]").attr("selected", true);
                        $('#edilocation').val(data.coordinates);

                        $('#winedit').waiting('done');
                        $('#load2').hide();
                    } else {
                        $('#load2').hide();
                        msg_alert('No pudo cargar la información de la base de datos', 'error');
                    }
                }
            });

        }); // fin de editar

        //eliminar plan
        $(document).on("click", '.del', function (event) {
            event.stopImmediatePropagation();
            var idp = $(this).attr("id");
            bootbox.confirm("¿ Esta seguro de eliminar este registro ?", function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('profiles.odb.delete') }}",
                        data: {"id": idp},
                        dataType: "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {
                        if (data.msg == 'errorclient')
                            msg_alert('No se puede eliminar el plan, existen clientes asociados.', 'error');
                        if (data.msg == 'error')
                            msg_alert('No se encontro el plan.', 'error');
                        if (data.msg == 'success') {
                            msg_alert('La Caja fue eliminado.', 'success');
                            window.LaravelDataTables["odb-table"].draw();;
                        }
                    });
                }
            });
        });

        function startloading(selector, text) {

            $(selector).loadingModal({
                position: 'auto',
                text: text,
                color: '#fff',
                opacity: '0.7',
                backgroundColor: 'rgb(0,0,0)',
                animation: 'spinner'
            });
        }

        // guardar editar plan
        $(document).on("click", "#editbtnplan", function (event) {
            event.stopImmediatePropagation();
            var plandata = $('#PlanformEdit').serialize();

            $.easyAjax({
                type: "POST",
                url: "{{ route('profiles.odb.update') }}",
                data: plandata,
                container:'#PlanformEdit',
                success: function(data) {
                    if (data.msg == 'errorConnect') {
                        $('body').loadingModal('destroy');
                        $('#edit').modal('toggle');
                        msg_alert('Se produjo un error no se tiene acceso al router, verifique los datos de autentificación, además si esta encendido y conectado a la red.', 'error');
                    }

                    if (data.msg == 'errorDownload') {
                        $('body').loadingModal('destroy');
                        msg_alert('EL campo descarga no es válido, la velocidad debe estar en kilobytes ejemplos: 512, 1000', 'error');
                    }
                    if (data.msg == 'errorUpload') {
                        $('body').loadingModal('destroy');
                        msg_alert('EL campo subida no es válido, la velocidad debe estar en Kilobytes ejemplos: 512, 1000', 'error');
                    }

                    if (data.msg == 'success') {
                        $('body').loadingModal('destroy');
                        msg_alert('La Caja fue actualizada correctamente.', 'info');
                        $('#edit').modal('toggle');
                        window.LaravelDataTables["odb-table"].draw();;
                    }
                }
            })
        });
        //fin guardar editar plan

        ///// funcion de depuracion
        function debug(xhr, thrownError) {
            $.ajax({
                "url": "/config/getconfig/debug",
                "type": "GET",
                "data": {},
                "dataType": "json"
            }).done(function (deb) {

                if (deb.debug == '1') {
                    msg_alert('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
                } else
                    alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
            });
        }

    </script>
@endsection
