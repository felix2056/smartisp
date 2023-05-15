<style>
    .input-group .date-picker {
        height: 38px;
    }
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">@lang('app.editBalance')</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="cortado-update" class="form-horizontal" role="form" method="POST">
                @csrf
                <fieldset>
                    <div class="form-group">
                        <label for="balance" class="control-label col-lg-3 col-md-3">
                            @lang('app.cutService')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <div class="input-group">
                                <input class="form-control date-picker"
                                       maxlength="8"
                                       value="{{$serviceCutDate->format('d-m-Y')}}" type="text"
                                       data-date-format="dd-mm-yyyy"
                                       id="cortado_date" name="cortado_date"
                                       required/>
                                <span class="input-group-addon">
                <i class="fa fa-calendar bigger-110"></i>
            </span>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="update-cortado" type="button" class="btn btn-danger">Update</button>
</div>

<script>
    $(function() {
        $('.date-picker').datepicker({
            language: 'es',
            autoclose: true,
            todayHighlight: true
            //startView: 'year',
        });
        $('#update-cortado').click(function(e) {
            var url = '{{ route('client.update-cortado-submit', $client->id) }}';

            $.easyAjax({
                type: 'POST',
                url: url,
                container: "#cortado-update",
                data:$('#cortado-update').serialize(),
                success: function(res) {
                    window.location.reload();
                }
            });
        })
    })
</script>
