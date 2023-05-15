<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.add') @lang('app.new') @lang('app.product')</h4>
</div>
<form class="form-horizontal" method="post" id="add_edit_product" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="modal-body">
            <div class="form-group">
                <label for="name" class="col-sm-3 control-label"> @lang('app.name')</label>
                <div class="col-sm-9">
                    <input id="name" class="form-control" type="text" name="name">
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="col-sm-3 control-label"> @lang('app.vendor')</label>
                <div class="col-sm-9">
                    <select name="vendor_id" class="form-control" id="vendor">
                        @forelse($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @empty
                            <option value="">No Vendor exists.</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="contact_name" class="col-sm-3 control-label"> @lang('app.sellPrice')</label>
                <div class="col-sm-9">
                    <input id="sell_price" class="form-control" type="number" name="sell_price">
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="col-sm-3 control-label"> @lang('app.rentPrice')</label>
                <div class="col-sm-9">
                    <input id="rent_price" class="form-control" type="number" name="rent_price">
                </div>
            </div>

            <div class="form-group">
                <label for="subject" class="col-sm-3 control-label">@lang('app.photo')</label>
                <div class="col-sm-9">
                    <input type="file" class="form-control" name="file" id="file">
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="addUpdateProduct(); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>
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
</script>