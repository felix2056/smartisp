@extends('layouts.cashdesk.master')

@section('title',$company)

@section('styles')

    <link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.css')}}" />
    <link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}"/>
    <style>
        .card {
            border: 1px solid #c1c1c1;
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
                    <li class="active">@lang('app.searchClient')</li>
                </ul>
            </div>
            <div class="page-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card pull-up left-border info_c">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3>Add funds to {{ $client->name }} (login: {{ $client->dni }}, id: {{ $client->id }})</h3><hr>
                                        </div>

                                        <div class="col-md-12">
                                            <h5>Customer Balance: <strong>{{ $client->wallet_balance }} {{ $global->nmoney }}</strong></h5>
                                        </div>

                                        <div class="col-md-12">
                                            <h5>Pending Invoices Amount: <strong>{{ $client->balance }} {{ $global->nmoney }}</strong></h5>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card ">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-9 col-sm-12">
                                                                <form class="form-horizontal" method="post" id="formaddpay" autocomplete="off">
                                                                    @csrf
                                                                    <fieldset>
                                                                        <div class="form-group">
                                                                            <label for="name" class="col-sm-3 control-label">@lang('app.waytopay')</label>
                                                                            <div class="col-sm-9">
                                                                                <select class="form-control" id="way_to_pay" name="way_to_pay">
                                                                                    <option value="Cash" selected>@lang('app.cash')</option>
                                                                                    <option value="Bank Transfer">@lang('app.bankTransfer')</option>
                                                                                    <option value="PayPal">PayPal</option>
                                                                                    <option value="Stripe">Stripe</option>
                                                                                    <option value="Other">@lang('app.other')</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="name" class="col-sm-3 control-label">@lang('app.date')</label>
                                                                            <div class="col-sm-9">
                                                                                <div class="input-group" style="padding: 0;font-size: 13px;">
                                                                                    <input id="date" class="form-control datepicker" value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}" style="width: 100%;" type="text" original-value="" force-send="0" autocomplete="false" name="date">

                                                                                    <div class="input-group-addon" id="invoice_request_auto_next_addon" style="cursor: pointer;">
                                                                                        <i class="fa fa-close" aria-hidden="true"></i>
                                                                                    </div>
                                                                                </div>

                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                                                                        <div class="form-group">
                                                                            <label for="name" class="col-sm-3 control-label"> @lang('app.amount')</label>
                                                                            <div class="col-sm-9">
                                                                                <input id="amount" class="form-control" type="number" min="1" name="amount">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="name" class="col-sm-3 control-label">@lang('app.idPago')</label>
                                                                            <div class="col-sm-9">
                                                                                <input type="number" id="id_pago" class="form-control" name="id_pago">
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="name" class="col-sm-3 control-label">@lang('app.commentary')</label>
                                                                            <div class="col-sm-9">
                                                                                <textarea id="commentary" class="form-control" name="commentary"></textarea>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <button class="btn btn-sm btn-primary" onclick="addDeposit();return false;" style="float: right;">Add</button>
                                                                            </div>
                                                                        </div>

                                                                    </fieldset>
                                                                </form>
                                                            </div>
                                                            <div class="col-md-6 col-sm-12"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" class="mt-3">
                                        <div class="col-md-12">
                                            <div class="card ">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-sm-12">
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
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}

    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>

    <script src="{{asset('assets/plugins/select2/select2.min.js')}}"></script>

    <script src="{{asset('assets/js/moment/moment.min.js')}}"></script>
    <script src="{{asset('assets/js/moment/moment-with-locales.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-datepicker.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/date-time/locales/bootstrap-datepicker.es.js')}}"></script>
    <script>

        function msg(msg,type)
        {
            if(type=='success'){
                var clase = 'gritter-success';
                var tit = Lang.app.registered;
                var img = '{{ asset('assets/img/ok.png') }}';
                var stincky = false;

            }
            if(type=='error'){
                var clase = 'gritter-error';
                var tit = Lang.app.error;
                var img = '{{ asset('assets/img/error.png') }}';
                var stincky = false;

            }
            if(type=='debug'){
                var clase = 'gritter-error gritter-center';
                var tit = Lang.app.errorInternoDebugMode;
                var img = '{{ asset('assets/img/error.png') }}';
                var stincky = false;

            }
            if(type=='info'){
                var clase = 'gritter-info';
                var tit = Lang.app.information;
                var img = '{{ asset('assets/img/info.png') }}';
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
        $(document).ready(function() {
            $('#date').datepicker();
        });
        const addDeposit = () => {
            var url = '{{ route('cashdesk.deposit.payments.store') }}';

            $.easyAjax({
                type: 'POST',
                url: url,
                data: $('#formaddpay').serialize(),
                container: "#formaddpay",
                success: function(res) {
                    if(res.status == 'success') {
                        // window.LaravelDataTables["cashdesk-invoice-table"].draw();
                        window.location.reload();
                        // $('#addEditModal').modal('hide');
                        // table.draw();
                    }
                }
            });

            // msg('Deposit added', 'success')
        }

        function sendEmail(id) {

            bootbox.confirm('{{ 'messages.areyousureyousendtheinvoicebymail' }}', function (result) {
                if (result) {
                    var url = '{{route('cashdesk.invoices.sendEmail', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#invoice-table",
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["cashdesk-invoice-table"].draw()
                            }
                        }
                    });
                }
            });

        }
    </script>
@endsection
