<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">@lang('app.toCharge')</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="admin_customers_billing_tobill_form" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="tobill-tobilldate" class="control-label col-lg-3 col-md-3">
                            @lang('app.date')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="tobill-tobilldate"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="ToBill[toBillDate]">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tobill-tobilltransactiondate" class="control-label col-lg-3 col-md-3">
                            @lang('app.transaction')  @lang('app.date')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="tobill-tobilltransactiondate"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="ToBill[toBillTransactionDate]">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tobill-tobillperiod" class="control-label col-lg-3 col-md-3">@lang('app.period')</label>
                        <div class="col-lg-9 col-md-9">
                            <select type="select" original-value="1" force-send="0" class="select2 select2-hidden-accessible form-control" name="ToBill[period]" id="tobill-tobillperiod" tabindex="-1" aria-hidden="true">
                                <option value="-1">Take from services</option>
                                <option value="0">Postpaid</option>
                                <optgroup label="Prepay">
                                    @for ($i = 1; $i < 13; $i++)
                                        <option value="{{ $i }}">Prepay {{ $i }} months</option>
                                    @endfor
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 pull-right">
                        <input id="preview_btn" type="button" class="btn btn-primary btn-xs" value=" Preview ">
                    </div>
                </fieldset>
                <hr>

                <div id="preview">
                    <div class="alert alert-warning" role="alert">@lang('app.alltheservicesofthisPeriodhavebeenCharged')</div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="charge-client" type="button" class="btn btn-primary">@lang('app.toCharge')</button>
</div>

<script>
    $(function() {
        $('#tobill-tobilldate').datepicker({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            changeMonth: true,
            changeYear: true,
        })

        $('#tobill-tobilltransactiondate').datepicker({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            minDate: moment().format('YYYY-MM-DD'),
            changeMonth: true,
            changeYear: true,
        })

        $('#tobill-tobilldate').val(moment().format('YYYY-MM-DD'))
        $('#tobill-tobilltransactiondate').val(moment().format('YYYY-MM-DD'));

        var url = '{{ route('transactionsToBill', $id) }}';

        var data = [];

        $.easyAjax({
            type: 'GET',
            url: url,
            data: $('#admin_customers_billing_tobill_form').serialize(),
            container: "#preview",
            success: function(res) {
                if(res.status == 'success') {
                    $('#preview').html(res.view);
                }
            }
        });

        $('#preview_btn').click(function(e) {
            $.easyAjax({
                type: 'GET',
                url: url,
                data: $('#admin_customers_billing_tobill_form').serialize(),
                container: '#preview',
                success: function(res) {
                    if(res.status == 'success') {
                        $('#preview').html(res.view);
                        data = res.invoice_data;
                    }
                }
            });
        });

        $('#charge-client').click(function(e){
            var charge_url = '{{ route('transactionsToBillCreate') }}';

            $.easyAjax({
                type: 'GET',
                url: url,
                data: $('#admin_customers_billing_tobill_form').serialize(),
                container: '#preview',
                success: function(res) {
                    if(res.status == 'success') {
                        $('#preview').html(res.view);
                        data = res.invoice_data;

                        $.easyAjax({
                            url: charge_url,
                            method: 'GET',
                            data: data,
                            success: function(res) {
                                if (res.status == 'success') {
                                    $('#addEditModal').modal('hide');
                                    $('#client_balance').text(res.wallet_balance+' USD');
                                    $('#client_pending_invoice_balance').text(res.account_balance+' USD');
                                    if (typeof table !== 'undefined') {
                                        table.draw()
                                    }
                                }
                                if (res.status == 'fail') {
                                    $('#addEditModal').modal('hide')
                                }
                            }
                        });
                    }
                }
            });

        });
    })
</script>
