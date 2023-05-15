<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>
        @lang('app.add') @lang('app.payment') ( @lang('app.walletBalance') - {{ $walletBalance }} {{ $global->nmoney }})</h4>
</div>
<form class="form-horizontal" method="post" id="formaddpay" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.waytopay')</label>
            <div class="col-md-8">
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
            <label for="name" class="col-md-3 control-label">@lang('app.date')</label>
            <div class="col-md-8">
                <div class="input-group" style="padding: 0;font-size: 13px;">
                    <input id="date" class="form-control datepicker"
                           value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}" style="width: 100%;" type="text"
                           original-value="" force-send="0" autocomplete="false" name="date" readonly>

                    <div class="input-group-addon" id="invoice_request_auto_next_addon" style="cursor: pointer;">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.amount')</label>
            <div class="col-md-8">
                <input id="amount" value="{{ $invoice->total_pay }}" class="form-control" type="number" min="1"
                       name="amount" readonly>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.payByWallet')</label>
            <div class="col-md-8">
                <div class="checkbox">
                    <label>
                        <input name="payByWallet" value="1" id="payByWallet" type="checkbox" class="ace">
                        <span class="lbl"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group" id="payRestCash" style="display: none;">
            <label for="name" class="col-md-3 control-label">@lang('app.payRestCash')</label>
            <div class="col-md-8">
                <input id="restCash" value="" class="form-control" type="number" min="1"
                       name="restCash" readonly>
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.idPago')</label>
            <div class="col-sm-8">
                <input type="number" id="id_pago" class="form-control" name="id_pago">
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.commentary')</label>
            <div class="col-md-8">
                <textarea id="commentary" class="form-control" name="commentary"></textarea>
            </div>
        </div>

        <input type="hidden" name="invoice_id" value="{{ $invoiceId }}">
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="savePayment(); return false;"><i
                    class="fa fa-floppy-o"></i>
            @lang('app.save')
        </button>
    </div>
</form>

<script>

    walletBalance = '{{$walletBalance}}';
    invoicePayment = '{{ $invoice->total_pay }}';

    $('#payByWallet').on('click', function () {
        if ($(this).is(":checked")) {
            // alert('Personal');
            var restAmount = parseFloat(walletBalance) - parseFloat(invoicePayment);
            if(restAmount < 0) {
                $('#payRestCash').css('display', 'block');
                $('#restCash').val(Math.round(Math.abs(restAmount) * 100)/100);
            }
        } else {
            $('#payRestCash').css('display', 'none');
            $('#restCash').val(0);
        }
    });

            
    function send_sri (id) {
            
                
                    var url = '{{route('invoice.payment.send', ':id')}}';
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        container: "#transaction-table",
                        success: function(response) {

                            if (response.status == "success") {
                                 obtenerComprobanteFirmado_sri(response.ruta_certificado,
                                                        response.contrasena,
                                                        response.ruta_respuesta,
                                                        response.ruta_factura,
                                                        response.host_email,
                                                        response.email,
                                                        response.passEmail,
                                                        response.port,
                                                        response.host_bd,
                                                        response.pass_bd,
                                                        response.user_bd,
                                                        response.database,
                                                        response.port_bd,
                                                        response.id_factura,
                                                )
                            }

                        }
                    });
                
            
        }

    function savePayment() {
        var url = '{{ route('invoice.payInvoice') }}';
        
        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#formaddpay').serialize(),
            container: "#formaddpay",
            success: function (res) {

                if (res.status == 'success') {
                    console.log("responde")
                    console.log(res)
                    console.log(res.sendInvoice)
                    if(res.sendInvoice == true){
                        send_sri(res.invoice_id)
                    }
                    
                    $('#addEditModal').modal('hide');
                    if(typeof table != "undefined") {
                        table.draw();
                    }

                    if(typeof window.LaravelDataTables['invoice-table'] != "undefined") {
                        window.LaravelDataTables['invoice-table'].draw();
                    }
                }
            }
        });
    }
</script>
