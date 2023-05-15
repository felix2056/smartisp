<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.setInternalUsage')</h4>
</div>
<form class="form-horizontal" method="post" id="set_internal_usage" autocomplete="off">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.administrator')</label>
            <div class="col-sm-9">
                <select id="user_id" name="user_id" class="form-control">
                    @forelse($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @empty
                        <option value="">No administrator found</option>
                    @endforelse
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
        <button type="button" class="btn btn-primary" onclick="internalUsagesItemSave('{{ $item->id }}'); return false;" ><i class="fa fa-floppy-o"></i>
            @lang('app.save')</button>
    </div>
</form>


<script>

</script>