<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
            class="fa fa-globe"></i>
        {{ __('app.Onu Detail') }}</h4>
</div>
<div class="modal-body modal_smartolt_detail" id="winnew">
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12">
                        <div class="row form-group">
                            <label for="cliente" class="col-sm-3 control-label">@lang('app.Client')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="cliente" class="form-control" id="cliente" value="{{ $onu->name }}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="board" class="col-sm-3 control-label">@lang('app.Board')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="board" class="form-control" id="board" value="{{$onu->board}}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="port" class="col-sm-3 control-label">@lang('app.Port')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="port" class="form-control" id="port" value="{{$onu->port}}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="sn" class="col-sm-3 control-label">@lang('app.SN')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="sn" class="form-control" id="sn" value="{{ $onu->sn }}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="type_id" class="col-sm-3 control-label">@lang('app.onus type')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="type_id" class="form-control" id="type_id" value="{{ $onu->onu_type_name }}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="pon_type" class="col-sm-3 control-label">@lang('app.pon type')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="pon_type" class="form-control" id="pon_type" value="{{ $onu->pon_type }}" >
                            </div>
                        </div>
<!--                        <div class="row form-group">
                            <label for="vlan_id" class="col-sm-3 control-label">@lang('app.vlans')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="vlan_id" class="form-control" id="vlan_id" value="{{ $onu->pon_type }}" >
                            </div>
                        </div>-->
                        <div class="row form-group">
                            <label for="zone_id" class="col-sm-3 control-label">@lang('app.zonas')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="zone_id" class="form-control" id="zone_id" value="{{ $onu->zone_name }}" >
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">

    <button type="btn" class="btn btn-danger pull-right" id ="btn_eliminar" onclick="eliminar({{$id_service}},{{$onu->olt_id}})" rel="nofollow">
        @lang('app.remove')</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('app.close') }}</button>
</div>
<script>
    function eliminar(id,olt_id){
        bootbox.confirm( "@lang('app.¿Está seguro de eliminar el servicio?')" , function (result) {
        if (result) {

            $('#btn_eliminar').attr('disabled', true);
            $('#btn_eliminar').html("<i class='fa fa-spinner fa-spin fa-fw'></i>"+"@lang('app.eliminando onu')");

            $.ajax({
                type: "POST",
                url: "/smartolt/delete",
                data: {
                    "id": id,
                    "olt_id": olt_id
                },
                dataType: "json",
            }).done(function (data) {
                $('#addEditModal').modal('toggle');

                if(data.msg=='error')
                    msg(data.text, 'error');
                if(data.msg=='success')
                    msg(data.text, 'success');

            });
        }
    });
    }

    function msg(msg,type)
    {
        if(type=='success'){
            var clase = 'gritter-success';
            var tit = Lang.app.registered;
            var stincky = false;
        }
        if(type=='error'){
            var clase = 'gritter-error';
            var tit = Lang.app.error;
            var stincky = false;
        }

        $.gritter.add({
            // (string | mandatory) the heading of the notification
            title: tit,
            // (string | mandatory) the text inside the notification
            text: msg,
            sticky: stincky,
            class_name: clase
        });
    }

</script>
