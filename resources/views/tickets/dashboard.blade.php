@extends('layouts.master')

@section('title',__('app.tickets'))

@section('styles')
    {{--<link rel="stylesheet" href="assets/css/chosen.min.css">--}}
    {{--<link rel="stylesheet" href="assets/css/waiting.css">--}}

    <style>
        .input-group > .btn.btn-sm {
            line-height: 25px;
        }

        select.form-control {
            max-width: 150px !important;
        }
        .info_c {
            border-left: 4px solid #4e72e2;
        }

        .info_c_text {
            color: #4e72e2 !important;
        }

        .pending_c {
            border-left: 4px solid #f0c253;
        }

        .pending_c_text {
            color: #f0c253 !important;
        }

        .azul_cl {
            border-left: 4px solid #58b5c7;
        }

        .azul_cl_text {
            color: #58b5c7 !important;
        }

        .red_cl {
            border-left: 4px solid #fd4860;
        }

        .red_cl_text {
            color: #fd4860 !important;
        }
        .grid3 {
            width: 25%;
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
                        <a href="<?php echo URL::to('portal'); ?>">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::to('portal/tickets'); ?>">@lang('app.tickets')</a>
                    </li>
                    <li class="active">@lang('app.dashboard')</li>
                </ul>
            </div>

            <div class="page-content">
                <!--start row-->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-12">
                        <div class="card pull-up">
                            <div class="card-content">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-xl-4 col-lg-4 col-12">
                                            <div class="card pull-up left-border info_c">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="media d-flex">
                                                            <div class="media-body text-left ">
                                                                <h3 class="info ajusth3" id="stRouter">{{ $newCount }}</h3>
                                                                <h6 class="info_c_text">@lang('app.new')</h6>
                                                            </div>
                                                            <div>
                                                                <i class="icon-drawer  icon_i info font-large-2 float-right"></i>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-12">
                                            <div class="card pull-up left-border red_cl">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="media d-flex">
                                                            <div class="media-body text-left ">
                                                                <h3 class="info ajusth3" id="stRouter">{{ $resolvedCount }}</h3>
                                                                <h6 class="red_cl_text">@lang('app.resolved')</h6>
                                                            </div>
                                                            <div>
                                                                <i class="icon-shield icon_i red_cl_text font-large-2 float-right"></i>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-lg-4 col-12">
                                            <div class="card pull-up left-border pending_c">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="media d-flex">
                                                            <div class="media-body text-left ">
                                                                <h3 class="info ajusth3" id="stRouter">{{ $workInProgressCount }}</h3>
                                                                <h6 class="pending_c_text">@lang('app.work_in_progress')</h6>
                                                            </div>
                                                            <div>
                                                                <i class="icon-book-open icon_i pending_c_text font-large-2 float-right"></i>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xl-6 col-lg-6 col-12">
                                            <div class="card pull-up left-border azul_cl">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="media d-flex">
                                                            <div class="media-body text-left ">
                                                                <h3 class="info ajusth3" id="stRouter">{{ $agentCount }}</h3>
                                                                <h6 class="azul_cl_text">@lang('app.waiting_on_agent')</h6>
                                                            </div>
                                                            <div>
                                                                <i class="icon-layers icon_i azul_cl_text font-large-2 float-right"></i>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-12">
                                            <div class="card pull-up left-border info_c">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="media d-flex">
                                                            <div class="media-body text-left ">
                                                                <h3 class="info ajusth3" id="stRouter">{{ $customerCount }}</h3>
                                                                <h6 class="info_c_text">@lang('app.waiting_on_customer')</h6>
                                                            </div>
                                                            <div>
                                                                <i class="icon-user  icon_i info_c_text font-large-2 float-right"></i>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-sm-12">
                        <!--Inicio de tab simple queues-->
                        <div class="row">
                            <div class="col-sm-6">
                                {{--<div class="card pull-up">--}}
                                    {{--<div class="card-content">--}}
                                        {{--<div class="card-body">--}}
                                            {{--<div class="table-responsive">--}}
                                                {{--{!! $assignedMeDataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.assignedToMe')</h5>
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                {!! $assignedMeDataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                {{--<div class="card pull-up">--}}
                                    {{--<div class="card-content">--}}
                                        {{--<div class="card-body">--}}
                                            {{--<div class="table-responsive">--}}
                                                {{--{!! $administratorDataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.assignedToAdministrators')</h5>
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                {!! $administratorDataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="widget-box">
                                    <div class="widget-header widget-header-flat widget-header-small">
                                        <h5 class="widget-title">
                                            {{-- <i class="ace-icon fa fa-signal"></i> --}}
                                            @php
                                                $year = \Carbon\Carbon::now()->format('Y') - 3;
                                            @endphp

                                            @lang('app.generalEconomicStatus')

                                            <select name="year" id="year" class="pull-right">
                                                @for($year; $year <= \Carbon\Carbon::now()->format('Y'); $year++ )
                                                    <option value="{{ $year }}"
                                                            @if($year == \Carbon\Carbon::now()->format('Y')) selected @endif> {{ $year }}</option>
                                                @endfor
                                            </select>
                                        </h5>

                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <div id="piechart-placeholder"></div>
                                            <div class="hr hr8 hr-double"></div>

                                        </div><!-- /.widget-main -->
                                    </div><!-- /.widget-body -->
                                </div><!-- /.widget-box -->
                            </div><!-- /.col -->
                        </div>
                        <!--Fin tabla planes simple queues-->
                    </div><!--end col-->
                </div>
                <!--end row-->
                @include('layouts.modals')
            </div>
        </div>
    </div>
@endsection
@section('scripts')

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    {{--<script src="{{asset('assets/js/bootbox.min.js')}}"></script>--}}
    {{--<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>--}}
    {{--<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>--}}
    {{--<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>--}}
    {{--<script src="{{asset('assets/js/rocket/tickets-core.js')}}"></script>--}}
    {!! $assignedMeDataTable->scripts() !!}
    {!! $administratorDataTable->scripts() !!}
    <script>
        loadChart();

        function loadChart() {
            var year = $('#year').val();

            $.easyAjax({
                type: 'POST',
                url: "{{ route('ticket.chart-data') }}",
                data: {
                    year: year
                },
                container: "#piechart-placeholder",
                success: function (data) {
                    // var incomeSum = data.income.reduce((a, b) => a + b, 0);
                    // var expenseSum = data.expense.reduce((a, b) => a + b, 0);
                    //
                    // $('#income').text(data.money + ' ' + incomeSum.toFixed(2));
                    // $('#expense').text(data.money + ' ' + expenseSum.toFixed(2));

                    Highcharts.chart('piechart-placeholder', {
                        chart: {
                            type: 'column'
                        },
                        accessibility: {
                            description: '{{ __('messages.ticketDashboardChartMessage') }}'
                        },
                        title: {
                            text: 'Tickets Chart'
                        },
                        xAxis: {
                            categories: [
                                'Jan',
                                'Feb',
                                'Mar',
                                'Apr',
                                'May',
                                'Jun',
                                'Jul',
                                'Aug',
                                'Sep',
                                'Oct',
                                'Nov',
                                'Dec'
                            ],
                            crosshair: true,
                            allowDecimals: false,
                            labels: {
                                formatter: function () {
                                    return this.value; // clean, unformatted number for year
                                }
                            },
                            accessibility: {
                                rangeDescription: 'Range: January to  December.'
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Tickets states'
                            },
                            labels: {
                                formatter: function () {
                                    return this.value;
                                }
                            }
                        },
                        tooltip: {
                            pointFormat: '{series.name}  {point.y}</b>'
                        },
                        plotOptions: {
                            area: {
                                pointStart: 'January',
                                marker: {
                                    enabled: false,
                                    symbol: 'circle',
                                    radius: 2,
                                    states: {
                                        hover: {
                                            enabled: true
                                        }
                                    }
                                }
                            }
                        },
                        series: [
                            {
                                name: '{{ __('app.new') }}',
                                data: data.new
                            },
                            {
                                name: '{{ __('app.work_in_progress') }}',
                                data: data.workInProgress
                            },
                            {
                                name: '{{ __('app.resolved') }}',
                                data: data.resolved
                            },
                            {
                                name: '{{ __('app.waiting_on_customer') }}',
                                data: data.waitingOnCustomer
                            },
                            {
                                name: '{{ __('app.waiting_on_agent') }}',
                                data: data.waitingOnAgent
                            }
                        ]
                    });
                }
            });
            // $.ajax({
            //     "url": "stat/payed",
            //     "type":"POST",
            //     "data":{
            //         year: year
            //     },
            //     "dataType":"json"
            // }).done(function(data) {
            //
            //
            // });
        }
        // $(function () {
        //
        //     $('#ticket-table').on('preXhr.dt', function (e, settings, data) {
        //
        //         var status = $('#status').val();
        //
        //         data['status'] = status;
        //     });
        //
        //     //funcion para recuperar todos los registros
        //     $(document).on('click', '#searchall', function (event) {
        //         $('#filterForm').trigger("reset");
        //         window.LaravelDataTables["ticket-table"].draw()
        //     });
        //     $(document).on("click", "#search", function () {
        //         window.LaravelDataTables["ticket-table"].draw()
        //     });
        //
        // });

        {{--function changeAssignee(id) {--}}
            {{--var url = '{{ route('tickets.assignee', ':id') }}';--}}
            {{--url = url.replace(':id', id);--}}

            {{--$.ajaxModal('#addEditModal', url);--}}
        {{--}--}}

    </script>
@endsection
