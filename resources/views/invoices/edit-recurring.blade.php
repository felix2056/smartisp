<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Edit Invoice</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="admin_customers_invoice_one_time_form" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="invoice_date" class="control-label col-lg-3 col-md-3">
                            @lang('app.period')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_date"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_date"
                                value="{{ $invoice->start_date.' - '.$invoice->expiration_date }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_bill_num" class="control-label col-lg-3 col-md-3">
                            @lang('app.billNumber')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_bill_num"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_bill_num"
                                value="{{ $invoice->num_bill }}"
                                readonly>
                        </div>
                    </div>
                    {{-- <div class="form-group">
                        <label for="invoice_iva" class="control-label col-lg-3 col-md-3">
                            IVA
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_iva"
                                class="form-control input-sm"
                                type="text"
                                autocomplete="false"
                                name="invoice_iva" value="{{ $invoice->iva }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_cost" class="control-label col-lg-3 col-md-3">
                            Costo
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_cost"
                                class="price form-control input-sm"
                                type="text"
                                autocomplete="false"
                                name="invoice_cost" value="{{ $invoice->cost }}">
                        </div>
                    </div> --}}
                    <div class="form-group">
                        <label for="invoice_note" class="control-label col-lg-3 col-md-3">
                            @lang('app.note')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_note"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_note" value="{{ $invoice->note }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                            @lang('app.memo')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_memo"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_memo" value="{{ $invoice->memo }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                           @lang('app.finalAmount')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="actual_total_pay"
                                class="form-control input-sm datepicker"
                                type="number"
                                min="0"
                                autocomplete="false"
                                name="total_pay" value="{{ $invoice->total_pay }}">
                        </div>
                    </div>

                    <hr>
                    <p align="right">
                        @lang('app.total') sin IVA: <span id="total_without_tax">{{ $invoice->cost }}</span>
                        <br>
                        IVA: <span id="total_tax">{{ round(($invoice->cost * $invoice->iva) / 100, 2) }}</span>
                        <br>
                        @lang('app.total'): <span id="total" style="font-weight: bold;">{{ round($invoice->cost + ($invoice->cost * $invoice->iva) / 100, 2) }}</span>
                    </p>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="add_invoice" type="button" class="btn btn-primary" onclick="editInvoiceItems('{{ $invoice->id }}'); return false;">@lang('app.update')</button>
</div>

<script>
    moment.locale('es');

    date_format = 'YYYY-MM-DD';

    daterangepicker_locale_config = {
        direction: 'ltr',
        format: date_format,
        separator: ' - ',
        applyLabel: 'Aplicar',
        cancelLabel: 'Cancelar',
        weekLabel: 'W',
        customRangeLabel: 'Rango Personalizado',
        daysOfWeek: moment.weekdaysMin(),
        monthNames: moment.monthsShort(),
        firstDay: moment.localeData().firstDayOfWeek()
    };

    $('#invoice_date').daterangepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        locale: daterangepicker_locale_config,
        opens: 'center',
        alwaysShowCalendars: false
    },
    function (start, end, label) {
        const no_of_days = end.diff(start, 'days') + 1
        let cost_without_tax = no_of_days/start.daysInMonth() * {{ $invoice->cost }}
        cost_without_tax = Number.parseFloat(cost_without_tax.toFixed(2))

        let vat_cost = {{ $invoice->iva }} * cost_without_tax / 100
        vat_cost = vat_cost.toFixed(2)
        const total = (Number.parseFloat(cost_without_tax)+Number.parseFloat(vat_cost)).toFixed(2)

        $('#total_without_tax').text(cost_without_tax)
        $('#total_tax').text(vat_cost)
        $('#total').text(total)
    })

    function editInvoiceItems(id) {
        var url = '{{ route('invoice.updateRecurring', ':id') }}';
        url = url.replace(':id', id);

        data = $('#admin_customers_invoice_one_time_form').serialize()+'&actual_total_pay='+$('#total').text()

        $.easyAjax({
            type: 'POST',
            url: url,
            data: data,
            container: '#admin_customers_invoice_one_time_form',
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }

</script>
