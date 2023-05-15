@extends('layouts.master')

@section('title',__('app.financiar'))

@section('styles')

    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" />
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

</style>
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
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="#">@lang('app.financiar')</a>
                    </li>
                    <li class="active">@lang('app.generalSummery')</li>
                </ul>
            </div>

            <div class="page-content">

                <div class="page-header">
                    <h1>
                        @lang('app.financiar')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.generalSummery')
                        </small>
                    </h1>
                </div>
                <div class="row" id="stats">
                    <div class="col-xs-12">

                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="card pull-up left-border info_c">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="info ajusth3" id="stClient">{{ $transactions->where('category', 'service')->count() }} ({{ $transactions->where('category', 'service')->sum('amount') }} {{ $global->nmoney }})</h3>
                                                    <h6 class="info_c_text">@lang('app.debitTransactions')</h6>
                                                </div>
                                                <div>
                                                    <i class="fa fa-dollar info font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="card pull-up left-border red_cl ">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="ajusth3" id="stClient">{{ $transactions->where('category', 'payment')->count() }} ({{ $transactions->where('category', 'payment')->sum('amount') }} {{ $global->nmoney }})</h3>
                                                    <h6 class="red_cl_text">@lang('app.creditTransactions')</h6>
                                                </div>
                                                <div>
                                                    <i class="fa fa-dollar red_cl_text font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="card pull-up left-border azul_cl">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class="verde_color ajusth3" id="stClient">{{ $payments->count() }} ({{ $payments->sum('amount') }} {{ $global->nmoney }})</h3>
                                                    <h6 class="azul_cl_text">@lang('app.payments')</h6>
                                                </div>
                                                <div>
                                                    <i class="fa fa-dollar azul_cl_text font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="card pull-up left-border pending_c">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class=" ajusth3" id="stClient">{{ $invoices->where('status', '!=', 3)->count() }} ({{ $invoices->where('status', '!=', 3)->sum('total_pay') }} {{ $global->nmoney }})</h3>
                                                    <h6 class="pending_c_text">@lang('app.billsPaid')</h6>
                                                </div>
                                                <div>
                                                    <i class="fa fa-dollar pending_c_text font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="card pull-up left-border">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <div class="media d-flex">
                                                <div class="media-body text-left">
                                                    <h3 class=" ajusth3" id="stClient">{{ $invoices->where('status', '=', 3)->count() }} ({{ $invoices->where('status', '=', 3)->sum('total_pay') }} {{ $global->nmoney }})</h3>
                                                    <h6 class="verde_color">@lang('app.unpaidInvoices')</h6>
                                                </div>
                                                <div>
                                                    <i class="fa fa-dollar verde_color font-large-2 float-right"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
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
                                                    <button type="button" id="search" class="btn cero_margin btn-sm btn-success"><i class="fa fa-search"></i>
                                                        @lang('app.filter')</button>
                                                    <button type="button" id="searchall" class="btn btn-sm btn-purple"><i class="fa fa-search-plus"></i>
                                                        @lang('app.showAll')
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="main-div">
                                <div class="col-lg-6 col-md-9">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.currentMonth')</font></font></font></font></strong>
                                        </div>

                                        @php
                                            $status = [1 => 'Paid', 2 => 'Paid (Account balance)', 3 => 'Unpaid', 4 => 'Late', 5 => 'Remove']
                                        @endphp

                                        <div class="panel-body">
                                            <table class="display supertable table table-striped table-bordered">
                                                <tbody>
                                                <tr>
                                                    <td>@lang('app.debitTransactions')</td>
                                                    <td>{{ $currentMonth->where('category', 'service')->count() }} ({{ $currentMonth->where('category', 'service')->sum('amount') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.payments')</td>
                                                    <td>{{ $currentMonth->where('category', 'payment')->count() }} ({{ $currentMonth->where('category', 'payment')->sum('amount') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.billsPaid')</td>
                                                    <td>{{ $currentMonthInvoices->where('status', '!=', 3)->count() }} ({{ $currentMonthInvoices->where('status', '!=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.unpaidBills')</td>
                                                    <td>{{ $currentMonthInvoices->where('status', '=', 3)->count() }} ({{ $currentMonthInvoices->where('status', '=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-md-9">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.lastMonth')</font></font></font></font></strong>
                                        </div>
                                        <div class="panel-body">
                                            <table class="display supertable table table-striped table-bordered">
                                                <tbody>
                                                <tr>
                                                    <td>@lang('app.debitTransactions')</td>
                                                    <td>{{ $lastMonth->where('category', 'service')->count() }} ({{ $lastMonth->where('category', 'service')->sum('amount') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.payments')</td>
                                                    <td>{{ $lastMonth->where('category', 'payment')->count() }} ({{ $lastMonth->where('category', 'payment')->sum('total') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.billsPaid')</td>
                                                    <td>{{ $lastMonthInvoices->where('status', '!=', 3)->count() }} ({{ $lastMonthInvoices->where('status', '!=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                <tr>
                                                    <td>@lang('app.unpaidBills')</td>
                                                    <td>{{ $lastMonthInvoices->where('status', '=', 3)->count() }} ({{ $lastMonthInvoices->where('status', '=', 3)->sum('total_pay') }} {{ $global->nmoney }})</td>
                                                </tr>
                                                </tbody>
                                            </table>
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
@endsection

@section('scripts')
    @parent
    <script src="{{asset('assets/js/jquery.easypiechart.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.sparkline.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.pie.min.js')}}"></script>
    <script src="{{asset('assets/js/flot/jquery.flot.resize.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/moment-with-locales.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/daterangepicker.min.js')}}"></script>

    <script>
        $(document).on("click", "#search", function () {
            let date = $('#date-range-picker').val();
            getStats(date);
        });
        //

        //funcion para recuperar todos los registros
        $(document).on('click', '#searchall', function (event) {
            let date = moment().startOf('month').format('DD-MM-YYYY') + ' | ' +  moment().endOf('month').format('DD-MM-YYYY');
            $('#date-range-picker').val(date);
            getStats(date);
        });


        $('input[name=date-range]').daterangepicker({
            startDate:moment().startOf('month'),
            endDate: moment().endOf('month'),
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

        function getStats(date = null) {
            $.easyAjax({
                type: 'POST',
                url: '{{ route('finance.dashboard.stats') }}',
                data: {date: date},
                container: "#stats",
                success: function(res) {
                    console.log(res, "Hello form res");
                    $('.main-div').html(res.view);
                }
            });
        }

    </script>
@endsection
