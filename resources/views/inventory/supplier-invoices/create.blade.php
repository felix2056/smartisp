<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.add') @lang('app.new') @lang('app.supplierInvoice')</h4>
</div>
<form class="form-horizontal" method="post" id="add_edit_supplier_invoices" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="modal-body">
            <div class="form-group">
                <label for="address" class="col-sm-3 control-label"> @lang('app.supplier')</label>
                <div class="col-sm-9">
                    <select name="supplier_id" class="form-control" id="supplier_id">
                        @forelse($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @empty
                            <option value="">No Supplier exists.</option>
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="subject" class="col-sm-3 control-label">@lang('app.file')</label>
                <div class="col-sm-9">
                    <input type="file" class="form-control" name="file" id="file">
                </div>
            </div>
            <div class="form-group">
                <label for="address" class="col-sm-3 control-label"> @lang('app.supplierInvoiceNumber')</label>
                <div class="col-sm-9">
                    <input id="invoice_number" class="form-control" type="text" name="invoice_number">
                </div>
            </div>
            <div class="form-group">
                <label for="invoice_date" class="control-label col-sm-3">
                    @lang('app.date')
                </label>

                <div class="col-lg-9 col-md-9">
                    <input
                            id="invoice_date"
                            class="form-control input-sm datepicker"
                            type="text"
                            autocomplete="false"
                            name="invoice_date">
                </div>
            </div>

            <div class="content-table-overflow-auto">
                <table class="table" id="admin_customers_billing_invoice_items">
                    <thead>
                    <tr>
                        <th width="5%">Pos.</th>
                        <th width="27%">@lang('app.products')</th>
                        <th width="5%">@lang('app.quantity')</th>
                        <th width="9%">@lang('app.price')</th>
                        <th width="9%">IVA (%)</th>
                        <th width="9%">With IVA</th>
                        <th width="5%">@lang('app.total')</th>
                        <th width="5%">---</th>
                    </tr>
                    </thead>


                    <tbody>
                    <tr class="rows">
                        <td>
                            <input type="text" name="pos[]" value="0" class="pos form-control input-sm" style="width:40px">
                        </td>
                        <td>
                            <select id="products" name="products[]" class="form-controls input-sm">
                                @forelse($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @empty
                                    <option value="">No product found</option>
                                @endforelse
                            </select>
                        </td>
                        <td>
                            <input type="text" name="quantity[]" value="1" class="quantity form-control input-sm" style="width:50px">
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
        <button type="button" class="btn btn-primary" onclick="addUpdateSupplierInvoice(); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>
    $('.datepicker').datepicker({
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true,
        minDate: 0,
        format: 'dd/mm/yyyy',
        orientation: "bottom auto"
    });
    //fin de obtener respuestas de tickets
    $('#file,#efile').ace_file_input({
        no_file:Lang.app.Selectafileonlyimages,
        btn_choose:Lang.app.select,
        btn_change:Lang.app.change,
        droppable:false,
        onchange:null,
        thumbnail:false, //| true | large
        whitelist:'gif|png|jpg|jpeg|pdf',
        blacklist:'exe|php|js'
        //onchange:''
        //
    });



    $('#admin_customers_billing_invoice_items').on('click', '.add_row', function() {
        var tr = $(this).closest('.rows');
        var clone = tr.clone();
        clone.find('.quantity').val('1');
        clone.find('.unit').val('0');
        clone.find('.price').val('0');
        clone.find('.tax_percent').val(0);
        clone.find('.with_tax').val('0');
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