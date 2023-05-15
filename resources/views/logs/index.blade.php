@extends('layouts.master')

@section('title',__('app.SystemLogs'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" />
    <style>
        .date-range-picker-height {
            height: 37px !important;
        }
        .input-group>.btn.btn-sm {
            line-height: 29px;
        }
        .form-control {
            height: 36px;
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
                        <a href="{{URL::to('admin')}}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="#">@lang('app.system')</a>
                    </li>
                    <li class="active">@lang('app.logs')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.logs')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-12">
                                <div class="card pull-up">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <form class="form-inline center_div" method="get" action="reports">

                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="fa fa-calendar bigger-110"></i>
                                                    </span>
                                                    <input class="form-control date-range-picker-height" type="text" name="date-range" id="date-range-picker" readonly />
                                                </div>
                                                <div class="input-group">
                                                    <select class="form-control" name="user" id="user">
                                                        <option value="all">@lang('app.chooseUser')</option>
                                                        @foreach($loggedUsers as $user)
                                                            <option value="{{$user->user}}">{{ $user->user }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="input-group">
                                                    <button type="button" id="searchall"
                                                            class="btn btn-sm btn-purple pull-right"><i
                                                                class="fa fa-search-plus"></i>
                                                        @lang('app.showAll')
                                                    </button>

                                                    <button type="button" id="search" class="btn cero_margin btn-sm btn-success">
                                                        <i class="fa fa-search"></i>
                                                        @lang('app.filter')
                                                    </button>

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.Alllogs')</h5>
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

                            @include('layouts.modals')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/date-time/moment-with-locales.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/daterangepicker.min.js')}}"></script>
    {!! $dataTable->scripts() !!}
    @parent
    <script src="{{asset('assets/js/rocket/logs-core.js')}}"></script>
    <script>
        $('#log-table').on('preXhr.dt', function (e, settings, data) {

            var user = $('#user').val();
            var extra_search = $('#date-range-picker').val();
            data['user'] = user;
            data['extra_search'] = extra_search;
        });
        $(document).on("click", "#search", function () {
            window.LaravelDataTables["log-table"].draw();
        });

        //funcion para recuperar todos los registros
        $(document).on('click', '#searchall', function (event) {
            $('#user').val('all');
            $('#date-range-picker').val('');
            window.LaravelDataTables["log-table"].draw();
        });


        $('input[name=date-range]').daterangepicker({
            startDate: moment().subtract(1, 'years'),
            endDate: moment(),
            'applyClass': 'btn-sm btn-success',
            'cancelClass': 'btn-sm btn-default',
            'separator': '|',
            locale: {
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Del',
                toLabel: 'Hasta',
                separator: '|',
                format: "DD-MM-YYYY",
                customRangeLabel: "Personalizado",
                daysOfWeek: [
                    "Do",
                    "Lu",
                    "Ma",
                    "Mi",
                    "Ju",
                    "Vi",
                    "Sa"
                ],
                monthNames: [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre"
                ],
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
                'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                'El Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
    </script>
@endsection
