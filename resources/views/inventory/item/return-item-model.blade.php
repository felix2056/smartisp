<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        Are you sure you want to return the item to stock? </h4>
</div>
<form class="form-horizontal" method="post" id="return_item" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.status')</label>
            <div class="col-sm-9">
                <select id="status" name="status" class="form-control">
                    <option value="In Stock">In Stock</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.mark')</label>
            <div class="col-sm-9">
                <select id="mark" name="mark" class="form-control">
                    <option value="Used">Used</option>
                    <option value="New">New</option>
                    <option value="Broken">Broken</option>
                </select>
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
        <button type="button" class="btn btn-primary" onclick="returnItemSave('{{ $item->id }}'); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>

</script>