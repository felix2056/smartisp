<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Crear Nota</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="admin_customers_note_one_time_form" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="note_date" class="control-label col-lg-3 col-md-3">
                            @lang('app.date')
                        </label>
                        <input type="hidden" name="invoices_dian_id" value="{{$idinvoice}}">
                        <div class="col-lg-9 col-md-9">
                            <input
                                id="note_date"
                                class="form-control input-sm datepicker"
                                type="date"
                                autocomplete="false"
                                name="note_date"
                                value="{{ date('Y-m-d')}}"
                                readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note_billing_type" class="control-label col-lg-3 col-md-3">
                            Tipo de nota
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <select class="form-control" name="note_type" id="note_type" onchange="fntnote_type();">
                                <option value="1">Nota crédito</option>
                                <option value="2">Nota débito</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note_bill_num" class="control-label col-lg-3 col-md-3">
                            @lang('app.invoiceNumber')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="note_bill_num"
                                class="form-control input-sm"
                                type="text"
                                autocomplete="false"
                                name="note_bill_num"
                                value="{{$number}}"
                                readonly="readonly">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note_pay_date" class="control-label col-lg-3 col-md-3">
                            Concepto
                        </label>

                        <div class="col-lg-9 col-md-9 credit">
                            <select class="form-control" name="note_conceptonota" id="note_conceptonota">
                                @foreach ($cmbconceptonota as $conceptonota)
                                    @if ($conceptonota->type=='1')
                                        <option value="{{$conceptonota->cod}}">{{$conceptonota->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-9 col-md-9 debit">
                            <select class="form-control" name="note_conceptonota" id="note_conceptonota">
                                @foreach ($cmbconceptonota as $conceptonota)
                                    @if ($conceptonota->type=='2')
                                        <option value="{{$conceptonota->cod}}">{{$conceptonota->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note_note" class="control-label col-lg-3 col-md-3">
                           Observaciones
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <textarea id="note_note"
                                class="form-control input-sm"
                                autocomplete="false"
                                name="note_note"></textarea>
                        </div>
                    </div>
                    <table class="table" id="admin_customers_billing_note_items">
                        <thead>
                            <tr>
                                <th width="5%">Pos.</th>
                                <th width="30%">@lang('app.description')</th>
                                <th width="5%">@lang('app.quantity')</th>
                                <th width="10%">@lang('app.price')</th>
                                <th width="10%">IVA (%)</th>
                                <th width="10%">With IVA</th>
                                <th width="10%">@lang('app.total')</th>
                                <th width="5%">---</th>
                            </tr>
                        </thead>


                        <tbody class="ui-sortable">
                            <tr id="" class="rows ui-sortable-handle">
                                <td>
                                    <input class="class_id" type="hidden" name="id[]" value="" style="width:40px">
                                    <input type="text" name="pos[]" value="0" class="pos form-control input-sm" style="width:40px">
                                </td>
                                <td class="form-inline">
                                    <input type="text" name="description[]" class="description form-control input-sm" value="" style="width:80%;" readonly="readonly">
                                    <a id="" class="btn btn-default btn-sm search_Item"><span class="fa fa-search"></span></a>
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
                    <hr>
                    <input type="hidden" id="input_subtotal" name="subtotal" value="">
                    <input type="hidden" id="input_totaltax" name="input_totaltax" value="">
                    <input type="hidden" id="input_total" name="input_total" value="">
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
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="add_note" type="button" class="btn btn-primary" onclick="savenote();return false;">Add</button>
</div>

<script>
    $('#admin_customers_billing_note_items').on('click', '.add_row', function() {
        var tr = $(this).closest('.rows');
        var clone = tr.clone();
        clone.find(':text').val('');
        clone.find(':hidden').val('');
        clone.find('.quantity').val('1');
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

    $('#admin_customers_billing_note_items').on('click', '.del_row', function() {
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
        $("#input_subtotal").val($('#total_without_tax').html());
        $("#input_totaltax").val($('#total_tax').html());
        $("#input_total").val($('#total').html());
    }

    $('#admin_customers_billing_note_items').on('input', '.price', calculate_with_tax);
    $('#admin_customers_billing_note_items').on('input', '.tax_percent', calculate_with_tax);
    $('#admin_customers_billing_note_items').on('input', '.with_tax', calculate_without_tax);
    $('#admin_customers_billing_note_items').on('input', '.quantity', calculate_total);

    $('.rows').each(calculate_with_tax);
    rePos();
    function rePos() {
        var pos = 0;
        $('.rows').each(function () {
            pos++;
            $(this).attr('id','tr_'+pos);
            $(this).find('.search_Item').attr('id',pos);
            $(this).find('.pos').val(pos);
        });
    }

    function addInvoiceItems(id) {
        var url = '{{ route('invoice.addOneTimeInvoiceCreate', ':id') }}';
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
    $('.debit').hide();
    // Muestra los conceptos segun la nota seleccionada ya sea credito o debito
    function fntnote_type()
    {
        if($('#note_type').val()=='1'){
            $('.credit').show();
            $('.debit').hide();
        }else{
            $('.credit').hide();
            $('.debit').show();
        }
    }
    //Guarda la nota
    function savenote() {
        var url = '{{ route('note.createnote', ':id') }}';
        url = url.replace(':id', {{$idinvoice}});
        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#admin_customers_note_one_time_form').serialize(),
            success: function(response) {
                if(response.status == 'success') {
                    //$('#addCreateModal').modal('hide');
                    //table.draw();
                    sendEmail_dian(response.numberFactura,response.dateFactura,response.note_type,response.cude,response.qr,response.typeoperation_cod,response.prefix,response.number,response.date,response.typedocEmisor,response.identificationEmisor,response.nameEmisor,response.tradename,response.typetaxpayerEmisor,response.directionEmisor,response.emailEmisor,response.phoneEmisor,response.typedocAdquiriente,response.identificationAdquiriente,response.nameAdquiriente,response.taxnameAdquiriente,response.typetaxpayerAdquiriente,response.directionAdquiriente,response.emailAdquiriente,response.phoneAdquiriente,response.detalle,response.money,response.subtotal,response.iva,response.total,response.resolution_number,response.resolution_desde,response.resolution_hasta,response.resolution_date,response.filename,response.correo,response.host_email,response.email_origen,response.passEmail,response.port);      
                }
            }
        });
    }
    //Genera el pdf y enviar el xml y el pdf al correo parametrizado
    function sendEmail_dian(numberFactura,dateFactura,note_type,cude,qr,typeoperation_cod,prefix,number,date,typedocEmisor,identificationEmisor,nameEmisor,tradename,typetaxpayerEmisor,directionEmisor,emailEmisor,phoneEmisor,typedocAdquiriente,identificationAdquiriente,nameAdquiriente,taxnameAdquiriente,typetaxpayerAdquiriente,directionAdquiriente,emailAdquiriente,phoneAdquiriente,detalle,money,subtotal,iva,total,resolution_number,resolution_desde,resolution_hasta,resolution_date,filename,correo,host_email,email_origen,passEmail,port) {
        path_host = window.location.origin;
        $.ajax({
            url: path_host+"/js/lib_dian/generarPDF_note.php",
            type: 'POST',
            data: {
                'numberFactura': numberFactura,
                'dateFactura': dateFactura,
                'note_type': note_type,
                'cude': cude,
                'qr': qr,
                'typeoperation_cod': typeoperation_cod,
                'prefix': prefix,
                'number': number,
                'date': date,
                'typedocEmisor': typedocEmisor,
                'identificationEmisor': identificationEmisor,
                'nameEmisor': nameEmisor,
                'tradename': tradename,
                'typetaxpayerEmisor': typetaxpayerEmisor,
                'directionEmisor': directionEmisor,
                'emailEmisor': emailEmisor,
                'phoneEmisor': phoneEmisor,
                'typedocAdquiriente': typedocAdquiriente,
                'identificationAdquiriente': identificationAdquiriente,
                'nameAdquiriente': nameAdquiriente,
                'taxnameAdquiriente': taxnameAdquiriente,
                'typetaxpayerAdquiriente': typetaxpayerAdquiriente,
                'directionAdquiriente': directionAdquiriente,
                'emailAdquiriente': emailAdquiriente,
                'phoneAdquiriente': phoneAdquiriente,
                'detalle': detalle,
                'money': money,
                'subtotal': subtotal,
                'iva': iva,
                'total': total,
                'resolution_number': resolution_number,
                'resolution_desde': resolution_desde,
                'resolution_hasta': resolution_hasta,
                'resolution_date': resolution_date,
                'filename': filename,
                'correo': correo,
                'host_email': host_email,
                'email_origen': email_origen,
                'passEmail': passEmail,
                'port': port
            }
        }).done(function (respuesta) {
            if(respuesta!='')
            {
                alert(respuesta);
            }
        });
    }
    $( "tbody" ).on( "click", ".search_Item",function() {
        var id={{$idinvoice}};
        var url = '{{route('note.additem', ['id' => $idinvoice,'idtr' => ':idtr'])}}';
        url = url.replace(':idtr', 'tr_'+$( this ).attr('id'));
        //$( this ).attr('id')
        $.ajaxModal('#addItemModal', url);
    });
</script>
