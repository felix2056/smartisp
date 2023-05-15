@extends('layouts.master')

@section('title',__('app.statistics'))

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="{{URL::to('admin')}}">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="{{URL::to('reports')}}">@lang('app.reports')</a>
                    </li>
                    <li class="active">@lang('app.statistics')</li>
                </ul>
            </div>


            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.statistics')

                    </h1>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                                <li class="active">
                                    <a data-toggle="tab" href="#faq-tab-1">
                                        <i class="blue ace-icon fa fa-bar-chart bigger-120"></i>
                                        @lang('app.economic')
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content no-border padding-24">
                                <div id="faq-tab-1" class="tab-pane fade in active">

                                    <div id="container">
                                    </div>

                                    <br><br><br>
                                    <div id="lasttwoyeras">
                                    </div>
                                    <br><br><br>
                                    <div id="years">
                                    </div>
                                </div>
                                <div id="faq-tab-2" class="tab-pane fade">
                                    <div id="plans" class="col-xs-10">
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
    <script src="{{asset('assets/js/highchart/code/highcharts.js')}}"></script>
    <script src="{{asset('assets/js/highchart/code/modules/exporting.js')}}"></script>

    <script src="{{asset('assets/js/rocket/stats-core.js')}}"></script>
@endsection
