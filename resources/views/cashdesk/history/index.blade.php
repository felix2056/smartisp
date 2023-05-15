@extends('layouts.cashdesk.master')

@section('title',$company)

@section('styles')

@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="#">{{__('app.history')}}</a>
                    </li>
                </ul>
            </div>
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        {{ __('app.history') }}
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            {{ __('app.list') }}
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="widget-box widget-color-blue2">
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
                </div>

                <hr>

                <div class="row">
                    <div class="col-lg-6 col-md-9">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><font style="vertical-align: inherit;"><font
                                                style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
                            </div>
                            <div class="panel-body" id="totals">
                                <table class="display supertable table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.type')</font></font></th>
                                        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
                                        <th><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.total')</font></font></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $key => $total)
                                        <tr>
                                            <td><label class="label label-primary"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> {{ $key }}</font></font></label></td>
                                            <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total['quantity'] }}</td>
                                            <td id="admin_customers_view_billing_transactions_totals_debit_total">{{ $total['total'] }} {{ $global->nmoney }}</td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-9">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <strong><font style="vertical-align: inherit;"><font
                                                style="vertical-align: inherit;">@lang('app.filter')</font></font></strong>
                            </div>

                            <div class="panel-body">
                                <table class="display supertable table table-striped table-bordered">
                                    <tbody>
                                    @foreach($datas as $key => $total)
                                        <tr>
                                            <td><label class="label label-primary"><font
                                                            style="vertical-align: inherit;"><font
                                                                style="vertical-align: inherit;"> {{ $key }}</font></font></label>
                                            </td>
                                            <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $key }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
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
@endsection


