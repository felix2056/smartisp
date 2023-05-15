@extends('layouts.master')

@section('title',__('app.inventory'))

@section('styles')
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
                        <a href="{{ URL::to('inventory/dashboard') }}">{{ __('app.inventory') }}</a>
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

                                        <a href="{{ route('inventory.items.index') }}">
                                            <div class="col-xl-4 col-lg-4 col-12">
                                                <div class="card pull-up left-border info_c">
                                                    <div class="card-content">
                                                        <div class="card-body">
                                                            <div class="media d-flex">
                                                                <div class="media-body text-left ">
                                                                    <h3 class="info ajusth3" id="stRouter">{{ $itemCount }}</h3>
                                                                    <h6 class="info_c_text">@lang('app.items')</h6>
                                                                </div>
                                                                <div>
                                                                    <i class="icon-docs icon_i info font-large-2 float-right"></i>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                        <a href="{{ route('inventory.products.index') }}">
                                            <div class="col-xl-4 col-lg-4 col-12">
                                                <div class="card pull-up left-border red_cl">
                                                    <div class="card-content">
                                                        <div class="card-body">
                                                            <div class="media d-flex">
                                                                <div class="media-body text-left ">
                                                                    <h3 class="info ajusth3" id="stRouter">{{ $productsCount }}</h3>
                                                                    <h6 class="red_cl_text">@lang('app.products')</h6>
                                                                </div>
                                                                <div>
                                                                    <i class="icon-note icon_i red_cl_text font-large-2 float-right"></i>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                        <a href="{{ route('inventory.suppliers.index') }}">
                                            <div class="col-xl-4 col-lg-4 col-12">
                                                <div class="card pull-up left-border pending_c">
                                                    <div class="card-content">
                                                        <div class="card-body">
                                                            <div class="media d-flex">
                                                                <div class="media-body text-left ">
                                                                    <h3 class="info ajusth3" id="stRouter">{{ $supplierCount }}</h3>
                                                                    <h6 class="pending_c_text">@lang('app.suppliers')</h6>
                                                                </div>
                                                                <div>
                                                                    <i class="icon-basket-loaded icon_i pending_c_text font-large-2 float-right"></i>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                        <a href="{{ route('inventory.supplier-invoices.index') }}">
                                            <div class="col-xl-4 col-lg-4 col-12">
                                                <div class="card pull-up left-border pending_c">
                                                    <div class="card-content">
                                                        <div class="card-body">
                                                            <div class="media d-flex">
                                                                <div class="media-body text-left ">
                                                                    <h3 class="info ajusth3" id="stRouter">{{ $invoicesCount }}</h3>
                                                                    <h6 class="pending_c_text">@lang('app.invoices')</h6>
                                                                </div>
                                                                <div>
                                                                    <i class="icon-docs icon_i pending_c_text font-large-2 float-right"></i>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <hr>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">

                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <div id="piechart-placeholder"></div>
                                    <div class="hr hr8 hr-double"></div>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                        </div><!-- /.widget-box -->
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
    <script>

        Highcharts.chart('piechart-placeholder', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Status statistics'
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Products items'
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: ''
            },
            series: [{
                name: 'Products',
                data: {!! json_encode($chart) !!},
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.1f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });

    </script>
@endsection
