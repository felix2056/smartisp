<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.add') Barcode</h4>
</div>
<form class="form-horizontal" method="post" id="store_barcode" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="modal-body">
            <h5>Add barcodes for invoice #{{ $invoice->id }}, Invoice number: {{ $invoice->invoice_number }} </h5>
            <br>
            @foreach($invoice->product_items as $item)
                <div class="form-group">
                    <label for="subject" class="col-sm-3 control-label">Barcode for item ID #{{ $item->id }},
                        Product: {{ $item->product->name }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="bar_code_{{$item->id}}" id="bar_code_{{$item->id}}" value="{{$item->bar_code}}">
                    </div>
                </div>
            @endforeach
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="storeBarCodeSupplierInvoice('{{ $invoice->id }}'); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>

</script>