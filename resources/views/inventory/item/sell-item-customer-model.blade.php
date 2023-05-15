<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.sellItem')</h4>
</div>
<form class="form-horizontal" method="post" id="sell_item" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.customer')</label>
            <div class="col-sm-9">
                {{--<select id="user_id" name="user_id" class="form-control">
                    @forelse($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @empty
                        <option value="">No administrator found</option>
                    @endforelse
                </select>--}}
                <select class="name-select2 form-control" id="client_id" name="client_id">
                    {{--<option></option>--}}
                </select>
            </div>
        </div>
        <div class="content-table-overflow-auto">
            <table class="table" id="admin_customers_billing_invoice_items">
                <thead>
                <tr>
                    <th width="5%" style="display: none;">Pos.</th>
                    <th width="27%">@lang('app.products')</th>
                    <th width="5%">@lang('app.quantity')</th>
                    <th width="9%">@lang('app.price')</th>
                    <th width="9%">IVA (%)</th>
                    <th width="9%">With IVA</th>
                    <th width="5%">@lang('app.total')</th>
                </tr>
                </thead>


                <tbody>

                    <tr class="rows">
                        <td style="display: none;">
                            <input type="text" name="id[]" class="id" value="{{ $item->id }}" style="width:40px; display: none;">
                            <input type="text" name="pos[]" value="0" class="pos form-control input-sm" style="width:40px; display: none;">
                        </td>
                        <td>
                            <input type="text" name="description[]" value="{{ $item->product->name }}" class="product form-control input-sm" readonly>
                            {{--<input type="hidden" name="productId[]" value="{{ $item->product->id }}" class="product form-control input-sm">--}}
                        </td>
                        <td>
                            <input type="text" name="quantity[]" value="1" class="quantity form-control input-sm" disabled style="width:50px">
                        </td>
                        <td>
                            <input type="text" name="price[]" value="{{ $item->product->sell_price }}" class="price decimal form-control input-sm" style="width:80px">
                        </td>
                        <td>
                            <input type="text" name="iva[]" value="{{ $item->tax }}" class="tax_percent tax_input form-control input-sm" style="width:60px">
                        </td>
                        <td>
                            <input type="text" name="with_tax[]" disabled class="with_tax decimal form-control input-sm" value="{{ $item->amount_with_tax }}" style="width:80px">
                        </td>
                        <td>
                            <input type="text" name="total[]" readonly class="total decimal form-control input-sm" value="{{ $item->amount_with_tax }}" style="width:80px">
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <hr>

        <p align="right">
            @lang('app.total') sin IVA: <span id="total_without_tax">0.00</span>
            <br>
            IVA: <span id="total_tax">0.00</span>
            <br>
            @lang('app.total'): <span id="total" style="font-weight: bold;">0.00</span>
        </p>
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="sellItemSave('{{ $item->id }}'); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>

    $(".name-select2").select2({
        ajax: {
            url: "{{ route('cashdesk.search-by-client-name') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term // search term
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                var resData = [];
                data.forEach(function(value) {
                    if (value.name.indexOf(params.term) != -1)
                        resData.push(value)
                })
                return {
                    results: $.map(resData, function(item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
            cache: false
        },
        minimumInputLength: 1
    })


    function rePos() {
        var pos = 0;
        $('.rows').each(function () {
            pos++;
            $(this).find('.pos').val(pos);
        });
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

    $('#admin_customers_billing_invoice_items').on('input', '.price', calculate_with_tax);
    $('#admin_customers_billing_invoice_items').on('input', '.tax_percent', calculate_with_tax);
    $('#admin_customers_billing_invoice_items').on('input', '.with_tax', calculate_without_tax);

    $('.rows').each(calculate_with_tax);
    rePos();
</script>