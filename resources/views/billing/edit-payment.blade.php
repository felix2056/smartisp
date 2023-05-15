<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-edit"></i>
        @lang('app.edit') @lang('app.payment')</h4>
</div>
<form class="form-horizontal" method="put" id="formEditPay" autocomplete="off">
    {{ csrf_field() }}
<div class="modal-body">

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.waytopay')</label>
            <div class="col-sm-9">
                <select class="form-control" id="way_to_pay" name="way_to_pay">
                    <option value="Cash" @if($payment->way_to_pay == 'Cash') selected @endif>@lang('app.cash')</option>
                    <option value="Bank Transfer" @if($payment->way_to_pay == 'Bank Transfer') selected @endif>@lang('app.bankTransfer')</option>
                    <option value="PayPal" @if($payment->way_to_pay == 'PayPal') selected @endif>PayPal</option>
                    <option value="Stripe" @if($payment->way_to_pay == 'Stripe') selected @endif>Stripe</option>
                    <option value="Other" @if($payment->way_to_pay == 'Other') selected @endif>@lang('app.other')</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.date')</label>
            <div class="col-sm-9">
                <div class="input-group" style="padding: 0;font-size: 13px;">
                    <input id="date" class="form-control datepicker" value="{{ $payment->date ?? \Carbon\Carbon::now()->format('m/d/Y') }}" style="width: 100%;" type="text" autocomplete="false" name="date" readonly>

                    <div class="input-group-addon" id="invoice_request_auto_next_addon" style="cursor: pointer;">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </div>
                </div>

            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.amount')</label>
            <div class="col-sm-9">
                <input id="amount" class="form-control" type="number" min="1" name="amount" value="{{ $payment->amount ?? '00.00' }}" disabled>
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.idPago')</label>
            <div class="col-sm-9">
                <input type="number" id="id_pago" class="form-control" name="id_pago" value="{{ $payment->id_pago ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">@lang('app.commentary')</label>
            <div class="col-sm-9">
                <textarea id="commentary" class="form-control" name="commentary">{{ $payment->commentary ?? '' }}</textarea>
            </div>
        </div>
{{--        <div class="form-group">--}}
{{--            <label for="name" class="col-sm-3 control-label">@lang('app.note')</label>--}}
{{--            <div class="col-sm-9">--}}
{{--                <textarea id="note" class="form-control" name="note">{{ $payment->note ?? '' }}</textarea>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="form-group">--}}
{{--            <label for="name" class="col-sm-3 control-label">@lang('app.sendreceiptafterpayment')</label>--}}
{{--            <div class="col-sm-9">--}}
{{--                <div class="checkbox">--}}
{{--                    <label>--}}
{{--                        <input name="send_notification" value="1" id="send_notification" type="checkbox" class="ace" />--}}
{{--                        <span class="lbl"></span>--}}
{{--                    </label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    <input type="hidden" name="client_id" value="{{ $clientId }}">
</div>
<div class="modal-footer" id="swfoot">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button type="button" class="btn btn-primary" onclick="savePayment('{{ $payment->id ?? '' }}'); return false;" ><i class="fa fa-floppy-o"></i>
        @lang('app.save')</button>
</div>
</form>
</div>

<script>
    // $('#date').datepicker();

    function savePayment(id) {
        var url = '{{ route('payments.update', ':id') }}';
        url = url.replace(':id', id);

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#formEditPay').serialize(),
            container: "#formEditPay",
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }
</script>
