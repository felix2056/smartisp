<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">@lang('app.changeAssignee')</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="change_assignee" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="invoice_billing_type" class="control-label col-lg-3 col-md-3">
                            @lang('app.chooseAssignee') :
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <select class="form-control" name="user_id" id="user_id">
                                <option value="">Select Assignee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @if($ticket->user_id == $user->id) selected @endif>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="changeAssignee" type="button" class="btn btn-primary" onclick="updateAssignee({{ $ticket->id }}); return false;">Add</button>
</div>
<style>
    select.form-control {
         max-width: 100% !important;
    }
</style>
<script>
    function updateAssignee(id) {
        var url = '{{ route('tickets.assignee.update', ':id') }}';
        url = url.replace(':id', id);

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#change_assignee').serialize(),
            container: '#change_assignee',
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    window.LaravelDataTables["ticket-table"].draw()
                }
            }
        });
    }

</script>
