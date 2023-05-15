<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus"></i>
        @lang('app.assignToCustomer')</h4>
</div>
<form class="form-horizontal" method="post" id="set_assign_customer" autocomplete="off">
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
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label"> @lang('app.notes')</label>
            <div class="col-sm-9">
                <textarea class="form-control" name="notes" rows="3"></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer" id="swfoot">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
        <button type="button" class="btn btn-primary" onclick="itemAssignCustomerSave('{{ $item->id }}'); return false;" ><i class="fa fa-floppy-o"></i>
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

</script>