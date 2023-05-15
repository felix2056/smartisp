@extends('layouts.master')

@section('title',__('app.cronJobs'))

@section('styles')

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
                        <a href="<?php echo URL::to('cron-jobs'); ?>">@lang('app.cronJobs')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">

                <div class="alert bg-info alert-icon-right alert-arrow-right alert-dismissible mb-2"
                     role="alert" style="color: #fff;
            background: #ff4961;">
                    <span class="alert-icon"><i class="la la-info-circle"></i></span>
                    {{ __('app.thisActionMustBeCarriedOutOnlyOnce') }}
                </div>
                <div class="page-header">
                    <h1>
                        @lang('app.cronJobs')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-body">
                                        <div class="widget-main">

                                            <div class="table-responsive">
                                                {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
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
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        function fireCron(cronId) {
            var url = '{{ route('cron-fire', ':id') }}';
            url = url.replace(':id', cronId);

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#cron-job-table",
            });
        }
    </script>
@endsection
