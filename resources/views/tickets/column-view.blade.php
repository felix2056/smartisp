<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel"><i
                class="fa fa-user-plus"></i>
        {{ __('app.showHideColumn') }}
    </h4>
</div>

<div class="modal-body" id="winnew_2">
    <div class="row">
        <div class="form-group">
            <form class="form-horizontal" id="columnVisibleForm">
                <div class="col-xs-8 col-sm-5">

                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="subject" type="checkbox"
                                   class="ace" {{ ($columnViews->subject) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.affair') }}</span>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="type" type="checkbox"
                                   class="ace" {{ ($columnViews->type) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.type') }}</span>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="priority" type="checkbox"
                                   class="ace" {{ ($columnViews->priority) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.priority') }}</span>
                        </label>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="status" type="checkbox"
                                   class="ace" {{ ($columnViews->status) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.status') }}</span>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="client_id" type="checkbox"
                                   class="ace" {{ ($columnViews->client_id) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.client') }}</span>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="section" type="checkbox"
                                   class="ace" {{ ($columnViews->section) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.section') }}</span>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="user_id" type="checkbox"
                                   class="ace" {{ ($columnViews->user_id) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.chooseAssignee') }}</span>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input name="campos_acc[]" value="created_at" type="checkbox"
                                   class="ace" {{ ($columnViews->created_at_view) ? 'checked' : '' }} />
                            <span class="lbl"> {{ __('app.creationDate') }}</span>
                        </label>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
    <button id="changeAssignee" type="button" class="btn btn-primary" onclick="updateColumnVisible(); return false;">Save</button>
</div>
<style>
    select.form-control {
         max-width: 100% !important;
    }
</style>
<script>
    function updateColumnVisible(id) {
        var url = '{{ route('tickets.column-visible-update') }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#columnVisibleForm').serialize(),
            container: '#addEditModal',
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    window.location.reload();
                }
            }
        });
    }

</script>
