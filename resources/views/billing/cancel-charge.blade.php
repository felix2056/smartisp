<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">@lang('app.cancelLastCharge')</h4>
</div>
<div class="modal-body">
    @lang('app.areyousureyouwanttoCancelLastCharge')
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="cancel-last-charge" type="button" class="btn btn-danger">@lang('app.submit')</button>
</div>

<script>
    $(function() {
        $('#cancel-last-charge').click(function(e) {
            var url = '{{ route('cancelCharge', $id) }}'

            $.easyAjax({
                type: 'GET',
                url: url,
                // container: "#preview",
                success: function(res) {
                    if(res.status == 'success') {
                        $('#addEditModal').modal('hide');
                        $('#client_balance').text(res.wallet_balance+' USD');
                        $('#client_pending_invoice_balance').text(res.account_balance+' USD');
                    }
                }
            });
        })
    })
</script>
