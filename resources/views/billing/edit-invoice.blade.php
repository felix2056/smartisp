<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">@lang('app.close')</span>
    </button>
    <h4 class="modal-title">
        <i class="fa fa-edit" aria-hidden="true"></i>
        <span id="infotitle">@lang('app.edit') @lang('app.transaction')</span>
    </h4>
</div>

<div class="modal-body">
    <div class="box-body">
        <form id="admin_customers_billing_transaction_form" class="form-horizontal" role="form" method="POST">
            {{ csrf_field() }}

            <div class="alert alert-warning" role="alert">
                @lang('app.transactionIsRead')!<br>
                @lang('app.Butyoucan')
                <a href="javascript:;" onclick="">
                    @lang('app.edit') @lang('app.bill')
                </a>
            </div>

            <fieldset>
                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.type')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <select class="form-control" disabled>
                            <option @if($transaction->category == "service") selected @endif>+@lang('app.debit')</option>
                            <option @if($transaction->category == "payment") selected @endif>+@lang('app.credit')</option>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.description')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input disabled="" type="text" style="width: 100%;" class="form-control input-sm" autocomplete="nope" name="Transactions[description]" id="transactions-description-id-14" value="{{ $transaction->description ?? '' }}">
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.quantity')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="customer_billing_transaction_quantity" disabled="" type="text" class="form-control input-sm" autocomplete="nope" name="Transactions[quantity]" value="{{ $transaction->quantity ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.unit')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input disabled="" type="text" class="form-control input-sm" autocomplete="nope" name="Transactions[unit]" id="transactions-unit-id-14" value="{{ $transaction->unity ?? '' }}">
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.price'):
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="customer_billing_transaction_price" class="decimal form-control input-sm" disabled="" type="text" name="amount" value="{{ $transaction->amount ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        IVA %
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="customer_billing_transaction_tax_percent" class="tax_input form-control input-sm" disabled="" type="text" name="Transactions[tax_percent]" value="{{ $transaction->tax_percent ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        Con IVA:
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="customer_billing_transaction_total_with_tax" class="decimal form-control input-sm" disabled="" type="text" name="Transactions[total_with_tax]" value="{{ $transaction->amount ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.total'):
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="customer_billing_transaction_total" disabled="disabled" type="text" class="form-control input-sm" autocomplete="nope" name="Transactions[total]" value="{{ $transaction->amount ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.category')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <select class="form-control" name="transaction">
                            <option value="1" @if($transaction->category == 'service') selected @endif>Service</option>
                            <option value="2" @if($transaction->category == 'discount') selected @endif>Discount</option>
                            <option value="3" @if($transaction->category == 'payment') selected @endif>Payment</option>
                            <option value="4" @if($transaction->category == 'refund') selected @endif>Refund</option>
                            <option value="5" @if($transaction->category == 'correction') selected @endif>Correction</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.date')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <div class="input-group" style="padding: 0;font-size: 13px;">

                            <input id="invoice_request_auto_next" data-provide="datepicker" data-date-start-date="0d" data-date-autoclose="true" class="form-control" value="{{ $transaction->date }}" style="width: 100%;" type="text" original-value="" force-send="0" autocomplete="nope" name="CustomerInvoice[request_auto_next]">

                            <div class="input-group-addon" id="invoice_request_auto_next_addon" style="cursor: pointer;">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.commentary')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input type="text" style="width: 100%;" original-value="" force-send="0" class="form-control input-sm" autocomplete="nope" name="Transactions[comment]" id="transactions-comment-id-14" value="">
                    </div>
                </div>


                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.period')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <input id="admin_customers_billing_transaction_form_period_from" type="hidden" style="width: 100%;" original-value="2016-05-01" force-send="0" name="Transactions[period_from]" value="2016-05-01">
                        <input id="admin_customers_billing_transaction_form_period_to" type="hidden" style="width: 100%;" original-value="2016-05-31" force-send="0" name="Transactions[period_to]" value="2016-05-31">

                        <input id="admin_customers_billing_transaction_form_period" readonly="readonly" style="" disabled="" value="2016-05-01 - 2016-05-31" type="text" original-value="2016-05-01 - 2016-05-31" force-send="0" class="form-control input-sm" autocomplete="nope" name="">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3 col-md-3">
                        @lang('app.checkIn')
                    </label>

                    <div class="col-lg-9 col-md-9">
                        <div class="checkbox">
                            <label>
                                <input name="CustomerBilling[status]" value="1" id="billing_status" type="checkbox" class="ace" {{ $transaction->billing_status === '1' ? 'checked' : '' }} />
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>
                </div>


            </fieldset>

            <nobr><input type="submit" value="@lang('app.submit')" style="width:-10px; height:-10px; visibility: hidden;"></nobr>
        </form>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>

</div>
