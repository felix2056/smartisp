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
                            @lang('app.date')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_date"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_date"
                                value="{{ $invoice->release_date ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_date" class="control-label col-lg-3 col-md-3">
                            Type of Billing
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <select class="form-control" name="invoice_billing_type" id="invoice_billing_type">
                                <option {{ $invoice->billing_type === 'none' ? 'selected' : '' }} value="none">None</option>
                                <option {{ $invoice->billing_type === 'recurring' ? 'selected' : '' }} value="recurring">Recurring</option>
                                <option {{ $invoice->billing_type === 'prepaid-monthly' ? 'selected' : '' }} value="prepaid-monthly">Prepaid (monthly)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_bill_num" class="control-label col-lg-3 col-md-3">
                            Bill Number
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_bill_num"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_bill_num"
                                value="{{ $invoice->num_bill ?? '' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_pay_date" class="control-label col-lg-3 col-md-3">
                            Pay Till
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_pay_date"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_pay_date"
                                value="{{ \Carbon\Carbon::parse($invoice->expiration_date)->format('Y-m-d') ?? '' }}">
                        </div>
                    </div>
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
                                name="invoice_note" value="{{ $invoice->note ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                            Memo
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="invoice_memo"
                                class="form-control input-sm datepicker"
                                type="text"
                                autocomplete="false"
                                name="invoice_memo" value="{{ $invoice->memo ?? '' }}">
                        </div>
                    </div>

                    <table class="table" id="admin_customers_billing_invoice_items">
                        <thead>
                            <tr>
                                <th width="5%">Pos.</th>
                                <th width="27%">Description</th>
                                <th width="21%">@lang('app.period')</th>
                                <th width="5%">Quantity</th>
                                <th width="5%">Unit</th>
                                <th width="9%">Price</th>
                                <th width="9%">IVA (%)</th>
                                <th width="9%">With IVA</th>
                                <th width="5%">@lang('app.total')</th>
                                <th width="5%">---</th>
                            </tr>
                        </thead>


                        <tbody class="ui-sortable">
                        @forelse($invoice->invoice_items as $key => $item)
                            <tr class="rows ui-sortable-handle">
                                <td>
                                    <input type="text" name="pos[]" value="{{ $key + 1 }}" class="pos form-control input-sm" style="width:40px">
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control input-sm" value="{{ $item->description ?? '' }}">
                                </td>
                                <td>
                                    <input type="hidden" name="period_from[]" class="period_from" value="{{ \Carbon\Carbon::parse($item->period_from)->format('Y-m-d') ?? '' }}">
                                    <input type="hidden" name="period_to[]" class="period_to" value="{{ \Carbon\Carbon::parse($item->period_to)->format('Y-m-d') ?? '' }}">

                                    <input type="text" name="period" class="form-control input-sm" style="width: 100%; background-color: #ffffff;" readonly="readonly" value="{{\Carbon\Carbon::parse($item->period_from)->format('Y-m-d')}} - {{\Carbon\Carbon::parse($item->period_to)->format('Y-m-d')}}">
                                </td>
                                <td>
                                    <input type="text" name="quantity[]" value="{{ $item->quantity ?? '' }}" class="quantity form-control input-sm" style="width:50px">
                                </td>
                                <td>
                                    <input type="text" name="unit[]" value="{{ $item->unit ?? '' }}" class="form-control input-sm" style="width:50px">
                                </td>
                                <td>
                                    <input type="text" name="price[]" value="{{ $item->price ?? '' }}" class="price decimal form-control input-sm" style="width:80px">
                                </td>
                                <td>
                                    <input type="text" name="iva[]" value="{{ $item->iva ?? '' }}" class="tax_percent tax_input form-control input-sm" style="width:60px">
                                </td>
                                <td>
                                    <input type="text" name="with_tax[]" disabled class="with_tax decimal form-control input-sm" value="{{ round($item->price + ($item->price * $item->iva) / 100, 2) }}" style="width:80px">
                                </td>
                                <td>
                                    <input type="text" name="total[]" readonly class="total decimal form-control input-sm" value="0" style="width:80px">
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="add_row" title="Add"><span class="glyphicon glyphicon-plus"></span></a>
                                    &nbsp;
                                    <a href="javascript:void(0)" class="del_row" title="Delete"><span class="glyphicon glyphicon-minus"></span></a>
                                </td>
                            </tr>
                        @empty
                            <tr class="rows ui-sortable-handle">
                                <td>
                                    <input type="hidden" name="id[]" value="" style="width:40px">
                                    <input type="text" name="pos[]" value="0" class="pos form-control input-sm" style="width:40px">
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control input-sm" value="">
                                </td>
                                <td>
                                    <input type="hidden" name="period_from[]" class="period_from" value="">
                                    <input type="hidden" name="period_to[]" class="period_to" value="">

                                    <input type="text" name="period" class="form-control input-sm" style="width: 100%; background-color: #ffffff;" readonly="readonly" value="">
                                </td>
                                <td>
                                    <input type="text" name="quantity[]" value="1" class="quantity form-control input-sm" style="width:50px">
                                </td>
                                <td>
                                    <input type="text" name="unit[]" value="0" class="unit form-control input-sm" style="width:50px">
                                </td>
                                <td>
                                    <input type="text" name="price[]" value="0" class="price decimal form-control input-sm" style="width:80px">
                                </td>
                                <td>
                                    <input type="text" name="iva[]" value="0" class="tax_percent tax_input form-control input-sm" style="width:60px">
                                </td>
                                <td>
                                    <input type="text" name="with_tax[]" disabled class="with_tax decimal form-control input-sm" value="0" style="width:80px">
                                </td>
                                <td>
                                    <input type="text" name="total[]" readonly class="total decimal form-control input-sm" value="0" style="width:80px">
                                </td>
                                <td>
                                    <a href="javascript:void(0)" class="add_row" title="Add"><span class="glyphicon glyphicon-plus"></span></a>
                                    &nbsp;
                                    <a href="javascript:void(0)" class="del_row" title="Delete"><span class="glyphicon glyphicon-minus"></span></a>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <hr>
                    <p align="right">
                        @lang('app.total') sin IVA: <span id="total_without_tax">0.00</span>
                        <br>
                        IVA: <span id="total_tax">0.00</span>
                        <br>
                        @lang('app.total'): <span id="total" style="font-weight: bold;">0.00</span>
                    </p>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="add_invoice" type="button" class="btn btn-primary" onclick="editInvoiceItems('{{ $invoice->id }}'); return false;">Update</button>
</div>

<script>
    moment.locale('es');

    date_format = 'YYYY-MM-DD';
    datetime_format = 'YYYY-MM-DD HH:mm:ss';

    daterangepicker_default_ranges = {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
        'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
        'Este Mes': [moment().startOf('month'), moment().endOf('month')],
        'El Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    };

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

    daterangepicker_default_config = {
        autoApply: false,
        opens: 'center',
        drops: 'up',
        ranges: daterangepicker_default_ranges,
        alwaysShowCalendars: true,
        locale: daterangepicker_locale_config
    };

    daterangepicker_single_default_config = {
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        locale: daterangepicker_locale_config
    };

    daterangepicker_datetime_locale_config = {
        direction: 'ltr',
        format: datetime_format,
        separator: ' - ',
        applyLabel: 'Aplicar',
        cancelLabel: 'Cancelar',
        weekLabel: 'W',
        customRangeLabel: 'Rango Personalizado',
        daysOfWeek: moment.weekdaysMin(),
        monthNames: moment.monthsShort(),
        firstDay: moment.localeData().firstDayOfWeek()
    };

    daterangepicker_single_datetime_default_config = {
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        autoUpdateInput: false,
        timePicker24Hour: $.parseJSON('true'),
        timePickerSeconds: $.parseJSON('true'),
        locale: daterangepicker_datetime_locale_config
    };

    $('#admin_customers_billing_invoice_items').on('click', '.add_row', function() {
        var tr = $(this).closest('.rows');
        var clone = tr.clone();
        clone.find(':text').val('');
        clone.find(':hidden').val('');
        clone.find('.quantity').val('1');
        clone.find('.unit').val('0');
        clone.find('.price').val('0');
        clone.find('.tax_percent').val(0);
        clone.find('.with_tax').val('0');
        clone.find('input[name="period"]').each(function () {
            initInvoiceItemsDateRangePicker($(this));
        });
        clone.hide();
        tr.after(clone.fadeIn());
        $('.rows').each(calculate_with_tax);
        rePos()
    })

    $('#admin_customers_billing_invoice_items').on('click', '.del_row', function() {
        if ($('.rows').length === 1) {
            return false
        }

        var tr = $(this).closest('.rows');

        tr.fadeOut(function() {
            tr.remove()
            rePos()
            calculate_total()
        })
    })

    function calculate_with_tax() {
        var $tr = $(this).closest('.rows');
        var price = $tr.find('.price').val();
        price = parseFloat(price);
        var tax = $tr.find('.tax_percent').val();
        tax = parseFloat(tax);

        var with_tax = price + (price * tax / 100);
        $tr.find('.with_tax').val('0.0000');
        if (!isNaN(with_tax)) {
            $tr.find('.with_tax').val(with_tax.toFixed(4));
        }
        calculate_total();
    }

    function calculate_without_tax() {
        var $tr = $(this).closest('.rows');
        var price = $tr.find('.with_tax').val();
        price = parseFloat(price);
        var tax = $tr.find('.tax_percent').val();
        tax = parseFloat(tax);
        var without_tax = price / (tax / 100 + 1);
        if (!isNaN(without_tax)) {
            $tr.find('.price').val(without_tax.toFixed(4));
        }
        calculate_total();
    }

    function calculate_total() {
        var total_without_tax = 0.0, total_tax = 0.0, with_tax = 0.0;
        $('.rows').each(function () {
            var quantity = $(this).find('.quantity').val();
            quantity = parseFloat(quantity);
            var price = $(this).find('.price').val();
            price = parseFloat(price);
            var tax = $(this).find('.tax_percent').val();
            tax = parseFloat(tax);
            if (isNaN(tax)) tax = 0.0;
            var current_total = isNaN(price * quantity) ? 0.00 : price * quantity;
            var current_tax = (current_total * tax / 100);
            var current_with_tax = current_total + current_tax;
            $(this).find('.total').val(parseFloat(current_with_tax).toFixed(2));
            if (isNaN(current_total) || isNaN(current_tax) || isNaN(current_with_tax)) {
                return true;
            } else {
                total_without_tax = total_without_tax + parseFloat(current_total.toFixed(2));
                total_tax = total_tax + parseFloat(current_tax.toFixed(2));
                with_tax = with_tax + parseFloat(current_with_tax.toFixed(2));
            }
        });
        $('#total_without_tax').html(total_without_tax.toFixed(2));
            var calculated_tax = with_tax.toFixed(2) - total_without_tax.toFixed(2);
        $('#total_tax').html(calculated_tax.toFixed(2));
        $('#total').html(with_tax.toFixed(2));
    }

    $('#admin_customers_billing_invoice_items').on('input', '.price', calculate_with_tax);
    $('#admin_customers_billing_invoice_items').on('input', '.tax_percent', calculate_with_tax);
    $('#admin_customers_billing_invoice_items').on('input', '.with_tax', calculate_without_tax);
    $('#admin_customers_billing_invoice_items').on('input', '.quantity', calculate_total);

    $('.rows').each(calculate_with_tax);
    rePos();
    function rePos() {
        var pos = 0;
        $('.rows').each(function () {
            pos++;
            $(this).find('.pos').val(pos);
        });
    }

    $('#invoice_date, #invoice_pay_date').datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: moment().format('YYYY-MM-DD'),
        changeMonth: true,
        changeYear: true,
    })

    var invoice_items_daterangepicker_locale_config = daterangepicker_locale_config;
    $.extend(invoice_items_daterangepicker_locale_config, {
        'cancelLabel': 'Clear'
    });

    var invoice_items_daterangepicker_config = daterangepicker_default_config;
    $.extend(invoice_items_daterangepicker_config, {
        'autoUpdateInput': false,
        'locale': invoice_items_daterangepicker_locale_config
    });

    function initInvoiceItemsDateRangePicker(el) {
        // Get value of input
        var value = el.val();

        // Init date range picker
        el.daterangepicker(invoice_items_daterangepicker_config);

        // Set events
        el.on('apply.daterangepicker', function (ev, picker) {
            // Update hidden inputs
            $(this).parent('td').find('.period_from').val(picker.startDate.format('YYYY-MM-DD'));
            $(this).parent('td').find('.period_to').val(picker.endDate.format('YYYY-MM-DD'));
            // Update date range picker input
            $(this).val(picker.startDate.format(date_format) + ' - ' + picker.endDate.format(date_format));
        }).on('cancel.daterangepicker', function (ev, picker) {
            // Update hidden inputs
            $(this).parent('td').find('.period_from').val('');
            $(this).parent('td').find('.period_to').val('');
            // Update date range picker input
            $(this).val('');
        });

        // If value was empty - clear it (DateRangePicker always set current date when value is empty)
        if (value.length === 0) {
            el.val('');
        }
    }

    $('input[name="period"]').each(function () {
        initInvoiceItemsDateRangePicker($(this));
    });

    function editInvoiceItems(id) {
        var url = '{{ route('invoice.update', ':id') }}';
        url = url.replace(':id', id);

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#admin_customers_invoice_one_time_form').serialize(),
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
