<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">@lang('app.editBalance')</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="balance-update" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="balance" class="control-label col-lg-3 col-md-3">
                            Balance
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                id="balance"
                                class="form-control"
                                type="number"
                                autocomplete="false"
                                name="balance"
                                step="0.00"
                                value="{{ $client->balance }}"
                            >
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="update-balance" type="button" class="btn btn-danger">Update</button>
</div>

<script>
    $(function() {
        $('#update-balance').click(function(e) {
            var url = '{{ route('client.update-pending-invoice-submit', $client->id) }}';

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#balance-update",
                data:$('#balance-update').serialize(),
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditBalanceModal').modal('hide');
                        $('#client_pending_invoice_balance').text($('#balance').val()+' USD');
                    }
                }
            });
        })
    })
</script>
