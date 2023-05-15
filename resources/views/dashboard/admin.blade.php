@extends('layouts.master')

@section('title',__('app.desk'))

@section('styles')
    <style type="text/css" media="screen">
        .comprar_text {
            font-weight: bold;
            color: #fff !important;
            background: #537e93 !important;
            padding: 5px 10px;
            border-radius: 5px;

        }

        .comprar_text:hover {
            font-weight: bold;
            color: #fff;
            background: #537e93;
            padding: 5px 10px;
            border-radius: 5px;

        }

        .left-border {
            border-left: 4px solid #28d09b;
            border-radius: 6px;
        }

        .text-left h6 {
            font-size: 12px;
            font-weight: bold;
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

        .icon_i {
            color: #dddfeb !important;
        }

        h4.bigger {
            margin-top: 3px !important;
            margin-right: 15px;
        }

        .grid3 {
            width: 38% !important;
        }

        .highcharts-figure, .highcharts-data-table table {
            min-width: 320px;
            max-width: 800px;
            margin: 1em auto;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #EBEBEB;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
            text-align: center;
        }

        .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
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
                        <a href="#">@lang('app.desk')</a>
                    </li>
                    <li class="active">@lang('app.generalSettings')</li>
                </ul>
            </div>

            <div class="page-content">

                @if (session('status_rol'))
                    <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2"
                         role="alert" style="color: #fff;
            background: #ff4961;">
                        <span class="alert-icon"><i class="la la-info-circle"></i></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true" style="color: #000">×</span>
                        </button>
                        {{ session('status_rol') }}
                    </div>
                @endif


                <?php
                if($status_licen['status'] != '2000'){
                ?>
                <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2" role="alert"
                     style="color: #fff;
            background: #ff4961;">
                    <span class="alert-icon"><i class="la la-info-circle"></i></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true" style="color: #000">×</span>
                    </button>
                    <?php
                    echo $status_licen['mensaje'];
                    ?>
                </div>
                <?php
                }
                ?>

                <div class="page-header">
                    <h1>
                        @lang('app.desk')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.generalSettings')
                        </small>
                    </h1>
                </div>
                <div class="row">

                    <div class="col-xs-12">


                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-12">
                                <div class="card pull-up left-border">
                                    <div class="card-content">
                                        <div class="card-body" style="padding: 9px;">
                                            <div class="media d-flex">

                                                @if($app_servers_incidents==0)
                                                    <div>
                                                        <i class="la la-check verde_color font-large-2 float-right"></i>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h4 class="ajusth3"
                                                            style="margin-left: 20px;margin-top: 10px;"
                                                            id="">@lang('app.serverOnline')</h4>
                                                    </div>
                                                @else

                                                    <div>
                                                        <i class="la la-close color_red  font-large-2 float-right"></i>
                                                    </div>

                                                    <div class="media-body text-left">
                                                        <h4 class="ajusth3 color_red "
                                                            style="margin-left: 20px;margin-top: 10px;"
                                                            id="">@lang('app.oneOfTheServerIsDesconnected')
                                                            <a href="/monitorizacion?route=dashboard">
                                                                <button style="padding: 0px 3px;
                                            font-size: 12px;
                                            margin-top: 6px;" type="button" class="btn btn-danger">
                                                                    @lang('app.seeServerDisconnected')
                                                                </button>
                                                            </a>
                                                        </h4>
                                                    </div>

                                                @endif

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-lg-6 col-12">
                                <div class="card pull-up left-border">
                                    <div class="card-content">
                                        <div class="card-body" style="padding: 9px;">
                                            <div class="media d-flex">
                                                @if($app_checks_incidents==0)
                                                    <div>
                                                        <i class="la la-check verde_color font-large-2 float-right"></i>
                                                    </div>
                                                    <div class="media-body text-left">
                                                        <h4 class="ajusth3"
                                                            style="margin-left: 20px;margin-top: 10px;"
                                                            id="">@lang('app.controlOnline')</h4>
                                                    </div>
                                                @else
                                                    <div>
                                                        <i class="la la-close color_red  font-large-2 float-right"></i>
                                                    </div>

                                                    <div class="media-body text-left">
                                                        <h4 class="ajusth3 color_red "
                                                            style="margin-left: 20px;margin-top: 10px;"
                                                            id="">@lang('app.oneOfTheControlDisconnected')
                                                            <a href="/monitorizacion?route=dashboard">
                                                                <button style="padding: 0px 3px;
                                    font-size: 12px;
                                    margin-top: 6px;" type="button" class="btn btn-danger">
                                                                    @lang('app.seeControlDisconnected')
                                                                </button>
                                                            </a>
                                                        </h4>
                                                    </div>

                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <a href="/routers">
                                <div class="col-xl-3 col-lg-3 col-12">
                                    <div class="card pull-up left-border info_c">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left ">
                                                        <h3 class="info ajusth3" id="stRouter">@lang('app.loading')
                                                            ...</h3>
                                                        <h6 class="info_c_text">@lang('app.routers')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="icon-drawer  icon_i info font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a href="/clients">
                                <div class="col-xl-3 col-lg-3 col-12">

                                    <div class="card pull-up left-border">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left">
                                                        <h3 class="ajusth3" id="stClient">@lang('app.loading')
                                                            ...</h3>
                                                        <h6 class="verde_color">@lang('app.clients')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="icon-users  icon_i verde_color font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </a>

                            <a href="/plans">
                                <div class="col-xl-3 col-lg-3 col-12">
                                    <div class="card pull-up left-border azul_cl">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left">
                                                        <h3 class="verde_color ajusth3"
                                                            id="stPlan">@lang('app.loading') ...</h3>
                                                        <h6 class="azul_cl_text">@lang('app.plans')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="icon-calendar icon_i verde_color font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a href="/users">
                                <div class="col-xl-3 col-lg-3 col-12">
                                    <div class="card pull-up left-border pending_c">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left">
                                                        <h3 class="info ajusth3" id="stUser">@lang('app.loading')
                                                            ...</h3>
                                                        <h6 class="pending_c_text">@lang('app.users')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="icon-user-follow icon_i info font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>


                        <div class="row">
                            <a href="/tickets">
                                <div class="col-xl-4 col-lg-4 col-12">
                                    <div class="card pull-up left-border">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left">
                                                        <h3 class="verde_color ajusth3"
                                                            id="stTicket">@lang('app.loading') ...</h3>
                                                        <h6 class="verde_color">@lang('app.tickets')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="la la-ticket icon_i verde_color font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a href="/clients/locked">
                                <div class="col-xl-4 col-lg-4 col-12">
                                    <div class="card pull-up left-border red_cl">
                                        <div class="card-content">
                                            <div class="card-body">
                                                <div class="media d-flex">
                                                    <div class="media-body text-left">
                                                        <h3 class="color_red ajusth3"
                                                            id="stClientBan">@lang('app.loading') ...</h3>
                                                        <h6 class="red_cl_text">@lang('app.clientCut')</h6>
                                                    </div>
                                                    <div>
                                                        <i class="icon-user-unfollow icon_i color_red font-large-2 float-right"></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>


                            <div class="col-xl-4 col-lg-4 col-12">
                                <div class="card pull-up left-border red_cl">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="color_red ajusth3"
                                                        id="stUserBan">@lang('app.loading') ...</h3>
                                                    <h6 class="red_cl_text">@lang('app.blockedUsers')</h6>
                                                </div>
                                                <div>
                                                    <i class="icon-shield icon_i color_red font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                            <div class="row">
                                @if($estado_financier == 1)
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
                                                <div class="clearfix">
                                                    <div class="grid3">
                                <span class="grey">
                                    <i class="ace-icon fa fa-arrow-circle-up fa-2x red"></i>
                                    &nbsp;
                                </span>
                                                        <h4 class="bigger pull-right" id="income"></h4>
                                                    </div>
                                                    <div class="grid3">
                                <span class="grey">
                                    <i class="ace-icon fa fa-arrow-circle-down fa-2x green"></i>
                                    &nbsp;
                                </span>
                                                        <h4 class="bigger pull-right" id="expense"></h4>
                                                    </div>
                                                </div>
                                            </div><!-- /.widget-main -->
                                        </div><!-- /.widget-body -->
                                    </div><!-- /.widget-box -->
                                </div><!-- /.col -->
                                @endif
                                <div class="col-sm-12">
                                    <div class="widget-box transparent">
                                        <div class="widget-header widget-header-flat">
                                            <h4 class="widget-title lighter">
                                                 <i class="ace-icon fa fa-star orange"></i>
                                                @lang('app.latestLog')
                                            </h4>
                                             <div class="widget-toolbar">
                                                <a href="#" data-action="collapse">
                                                    <i class="ace-icon fa fa-chevron-up"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="widget-body">
                                            <div class="widget-main no-padding table-responsive">
                                                <table class="table table-bordered" id="last-logs" width="100%">
                                                    <thead class="thin-border-bottom">
                                                    <tr>
                                                        <th>
                                                            @lang('app.detail')
                                                        </th>
                                                        <th>
                                                            @lang('app.username')
                                                        </th>
                                                        <th>
                                                            @lang('app.dateAndTime')
                                                        </th>
                                                        <th class="sorting_disabled">
                                                            @lang('app.type')
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                </table>
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
@endsection
@section('scripts')
    <script src="{{asset('assets/js/jquery.easypiechart.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.pie.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.resize.min.js')}}"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="{{asset('assets/js/rocket/dash-board.js')}}"></script>

    <script>

        $('#year').on('change', function () {
            loadChart();
        })

        @if($estado_financier == 1)
        loadChart();
        @endif

        function loadChart() {
            var year = $('#year').val();

            $.easyAjax({
                type: 'POST',
                url: "stat/payed",
                data: {
                    year: year
                },
                container: "#piechart-placeholder",
                success: function (data) {
                    var incomeSum = data.income.reduce((a, b) => a + b, 0);
                    var expenseSum = data.expense.reduce((a, b) => a + b, 0);

                    $('#income').text(data.money + ' ' + incomeSum.toFixed(2));
                    $('#expense').text(data.money + ' ' + expenseSum.toFixed(2));

                    Highcharts.chart('piechart-placeholder', {
                        chart: {
                            type: 'column'
                        },
                        accessibility: {
                            description: 'Chart compares the expenses and incomes of current year. The number of expense and earning is plotted on the Y-axis and the month on the X-axis. '
                        },
                        title: {
                            text: 'Expenses and Income Chart'
                        },
                        xAxis: {
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
                            crosshair: true
                        },
                        yAxis: {
                            title: {
                                text: 'Income Expenses states'
                            },
                            labels: {
                                formatter: function () {
                                    return this.value / 1000 + 'k';
                                }
                            }
                        },
                        tooltip: {
                            pointFormat: '{series.name}  <b>' + data.money + ' {point.y}</b>'
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
                        series: [{
                            name: 'Income',
                            data: data.income
                        }, {
                            name: 'Expense',
                            data: data.expense
                        }]
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

    </script>
@endsection
