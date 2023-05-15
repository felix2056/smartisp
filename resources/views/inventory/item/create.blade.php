<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.add') @lang('app.new') @lang('app.item')</h4>
</div>
<form class="form-horizontal" method="post" id="add_edit_item" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.product')</label>
            <div class="col-sm-9">
                <select id="product_id" name="product_id" class="form-control">
                    @forelse($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @empty
                        <option value="">No product found</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.barCode')</label>
            <div class="col-sm-9">
                <input id="bar_code" class="form-control" type="text" name="bar_code">
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.serialNumber')</label>
            <div class="col-sm-9">
                <input id="serial_number" class="form-control" type="text" name="serial_number">
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.costPrice')</label>
            <div class="col-sm-9">
                <input id="amount_with_tax" class="form-control" type="number" name="amount_with_tax">
            </div>
        </div>
        <div class="form-group">
            <label for="subject" class="col-sm-3 control-label">@lang('app.photo')</label>
            <div class="col-sm-9">
                <input type="file" class="form-control" name="file" id="file">
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.notes')</label>
            <div class="col-sm-9">
                <textarea class="form-control" name="notes" rows="3"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="addUpdateItem(); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>
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
</script>