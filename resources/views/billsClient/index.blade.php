@extends('layouts.cliente.master')

@section('title', __('app.bills'))

@section('styles')
    <style>
        .DirectoPagoTitle + div {
            margin-left: 0;
        }
        .DirectoPagoField {
            padding: 3px !important;
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
                        <a href="<?php echo URL::to('portal/bills'); ?>">@lang('app.bills')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.bills')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                    </h1>
                </div>
                <!--start row-->
                <div class="row">
                    <div class="col-sm-12">
                        @if ($message = \Session::get('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                <i class="fa fa-check"></i> {!! $message !!}
                            </div>
                            <?php \Session::forget('success');?>
                        @endif

                        @if ($message = \Session::get('error'))
                            <div class="custom-alerts alert alert-danger fade in">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                                {!! $message !!}
                            </div>
                            <?php \Session::forget('error');?>
                        @endif
                    </div>
                    <div class="col-sm-12">

                        <!--Inicio tabla planes simple queues-->
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">@lang('app.MyBills')</h5>
                                <div class="widget-toolbar">

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
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/rocket/clientNotifications2-core.js')}}"></script>

    {!! $dataTable->scripts() !!}
    <script>

        // fin de tabla planes
        function payWithStripe(id, name, total, email) {

            var handler = StripeCheckout.configure({
                key: '{{ config('services.stripe.key') }}',
                image: '{{ asset('assets/img/logo.png') }}',
                locale: 'auto',
                token: function (token) {
                    // You can access the token ID with `token.id`.
                    // Get the token ID to your server-side code for use.

                    var url = '{{route('stripe', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        url: url,
                        container: '#invoice_container',
                        type: "POST",
                        redirect: true,
                        data: {token: token, "_token": "{{ csrf_token() }}"}
                    })
                }
            });

            handler.open({
                name: name,
                amount: parseFloat(total) * 100,
                currency: 'USD',
                email: email

            });

            // Close Checkout on page navigation:
            window.addEventListener('popstate', function () {
                handler.close();
            });
        }

        // fin de tabla planes
    </script>
@endsection

