<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>
        Añadir pago</h4>
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
                           original-value="" force-send="0" autocomplete="false" name="date">

                    <div class="input-group-addon" id="invoice_request_auto_next_addon" style="cursor: pointer;">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label"> @lang('app.amount')</label>
            <div class="col-md-8">
                <input id="amount" value="{{ $invoice->total_pay }}" class="form-control" type="number" min="1"
                       name="amount" readonly>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.memo')</label>
            <div class="col-md-8">
                <textarea id="memo" class="form-control" name="memo"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.commentary')</label>
            <div class="col-md-8">
                <textarea id="commentary" class="form-control" name="commentary"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.note')</label>
            <div class="col-md-8">
                <textarea id="note" class="form-control" name="note"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-md-3 control-label">@lang('app.sendreceiptafterpayment')</label>
            <div class="col-md-8">
                <div class="checkbox">
                    <label>
                        <input name="send_notification" value="1" id="send_notification" type="checkbox" class="ace"/>
                        <span class="lbl"></span>
                    </label>
                </div>
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
    $('#date').datepicker();

    function savePayment() {
        var url = '{{ route('invoice.payInvoice') }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#formaddpay').serialize(),
            container: "#formaddpay",
            success: function (res) {
                if (res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }
</script>
