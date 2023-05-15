<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Invoice No. {{ $invoice_data->num_bill }}</h4>
</div>
<div class="modal-body">
    <div class="splynx-dialog-content">
        <div class="col-lg-6 col-md-9">
            <div class="row">
                <div class="col-lg-6 col-md-9">
                    Factura de:
                </div>
                <div class="col-lg-6 col-md-9">
                    <b>{{ $invoice_data->client->name }}</b>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-9">
                    @lang('app.number'):
                </div>
                <div class="col-lg-6 col-md-9">
                    <b>{{ $invoice_data->num_bill }}</b>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-9">
            <div class="row">
                <div class="col-lg-6 col-md-9">
                    Fecha de la factura:
                </div>

                <div class="col-lg-6 col-md-9">
                    {{ $invoice_data->start_date }}
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 col-md-9">
                    Fecha de vencimiento:
                </div>

                <div class="col-lg-6 col-md-9">
                    {{ $invoice_data->expiration_date }}
                </div>
            </div>
        </div>
        <br><br>
        <hr>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-responsive" style="width: 99%;border-bottom: none;border-left: none" id="admin_customers_billing_invoice_items">
                    <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="33%">@lang('app.description')</th>
                        <th width="5%">@lang('app.quantity')</th>
                        <th width="5%">Unidad</th>
                        <th width="10%">Precio</th>
                        <th width="10%">IVA (%)</th>
                        <th width="10%">Con IVA</th>
                        <th width="10%">@lang('app.total')</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if ($invoice_data->invoice_items->count() === 0)
                        <tr class="rows">
                            <td>
                                <span class="pos">1</span>
                            </td>
                            <td>
                                {{ $invoice_data->client->plan->name }}
                                <br>
                                {{ $invoice_data->start_date.'-'.$invoice_data->expiration_date }}
                            </td>
                            <td>
                                <span class="quantity">1</span>
                            </td>
                            <td>--</td>
                            <td>
                                <span class="price">{{ $invoice_data->cost }}</span>
                            </td>
                            <td>
                                <span class="tax_percent">{{ $invoice_data->iva }}</span>
                            </td>
                            <td>
                                <span class="with_tax">{{ $invoice_data->cost * $invoice_data->iva / 100 }}</span>
                            </td>
                            <td>
                                <span class="total">{{ $invoice_data->total_pay }}</span>
                            </td>
                        </tr>
                        @else
                        <?php $id = 0 ?>
                        @foreach ($invoice_data->invoice_items as $invoice_item)
                            <tr class="rows">
                                <td>
                                    <span class="pos">{{ ++$id }}</span>
                                </td>
                                <td>
                                    {{ $invoice_item->description }}
                                    @if ($invoice_item->period_from)
                                    <br>
                                    {{ $invoice_item->period_from.'-'.$invoice_item->period_to }}
                                    @endif
                                </td>
                                <td>
                                    <span class="quantity">{{ $invoice_item->quantity}}</span>
                                </td>
                                <td>{{ $invoice_item->unit ?? '--' }}</td>
                                <td>
                                    <span class="price">{{ $invoice_item->price }}</span>
                                </td>
                                <td>
                                    <span class="tax_percent">{{ $invoice_item->iva }}</span>
                                </td>
                                <td>
                                    <span class="with_tax">{{ round(($invoice_item->price * $invoice_item->iva / 100), 2) }}</span>
                                </td>
                                <td>
                                    <span class="total">{{ $invoice_item->total }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                        <tr>
                            <td colspan="5" rowspan="3" style="border-width: 0;"></td>
                            <td colspan="3" align="right" style="border-bottom: none;">@lang('app.total') sin IVA:
                                <span id="total_without_tax">{{ $extra_data['total_without_iva'] }}</span></td>
                        </tr>
                        <tr>
                            <td colspan="4" align="right" style="border-top: none;border-bottom: none;">IVA: <span id="total_tax">{{ $extra_data['total_iva'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" align="right" style="border-top: none;">@lang('app.total'): <span id="total" style="font-weight: bold;">{{ $invoice_data->total_pay }}</span></td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- <div class="row">
            <div class="col-lg-3">
                Xero ID
            </div>

            <div class="col-lg-9 col-md-9">
                <input name="Invoices[additional_attributes][xero_id]" id="invoices-additional_attributes-xero_id-id-15368" style="width: 100%;" autocomplete="nope" original-value="" force-send="0" readonly="" class="form-control input-sm" value="" type="text">
            </div>
        </div> --}}
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
