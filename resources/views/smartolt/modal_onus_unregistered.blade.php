<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
            class="fa fa-globe"></i>
        {{ __('app.Onus unregistered') }}</h4>
</div>
<div class="modal-body" id="winnew">
    <div class="row">
        <div class="tabbable">
            <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                <li class="active">
                    <a data-toggle="tab" href="#faq-tab-1">
                        @lang('app.nueva asignacion')
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#faq-tab-2">
                        @lang('app.asociar onu a cliente')
                    </a>
                </li>
            </ul>
        </div>
        <div class="tab-content no-border padding-24">
            <div id="faq-tab-1" class="tab-pane fade in active">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <div class="table-responsive">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-bordered table-hover"  id="onus_unregistered_list" style="width:100%;">
                                            <thead>
                                            <tr>
                                                <th>@lang('app.olt')</th>
                                                <th>@lang('app.Board')</th>
                                                <th>@lang('app.Port')</th>
                                                <th>@lang('app.SN')</th>
                                                <th>@lang('app.Type')</th>
                                                <th>@lang('app.actions')</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($list_onus_unregistered as $onu_un)
                                                <tr>
                                                    <td>{{ $onu_un->olt_name }}</td>
                                                    <td>{{ $onu_un->board }}</td>
                                                    <td>{{ $onu_un->port }}</td>
                                                    <td>{{ $onu_un->sn }}</td>
                                                    <td>{{ $onu_un->onu_type_name }}</td>
                                                    <td>
                                                        <a data-loading-text="@lang('app.saving')..." class="btn btn-primary btn-xs" title=@lang('app.autorizar') href="#"
                                                           id = "btn_autorizar" onclick="modal_info_autorizacion({{ $id_service }}, '{{ $onu_un->sn }}',{{ $onu_un->board }},{{ $onu_un->port }},{{$onu_un->olt_id}},{{ $onu_un->onu_type_id}})">
                                                            <i class="ace-icon fa fa-globe bigger-130"></i>
                                                            @lang('app.autorizar')
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="faq-tab-2" class="tab-pane fade in">
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-bordered table-hover"  id="onus_enables_to_assignment" style="width:100%;">
                            <thead>
                            <tr>
                                <th>@lang('app.SN')</th>
                                <th>@lang('app.actions')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($onus_enables_to_assignment as $onu_enable)
                                <tr>
                                    <td>{{ $onu_enable }}</td>
                                    <td>
                                        <a data-loading-text="@lang('app.saving')..." class="btn btn-primary btn-xs" title=@lang('app.Asociar') href="#"
                                           id = "btn_asociar" onclick="asociar_onu({{ $id_service }}, '{{ $onu_enable }}')">
                                            <i class="ace-icon fa fa-globe bigger-130"></i>
                                            @lang('app.Asociar')
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default"
            data-dismiss="modal">{{ __('app.close') }}
    </button>
</div>

<script>
    $('#onus_enables_to_assignment').dataTable({
        "oLanguage": {
            "sUrl": '{{ asset(__('app.datatable')) }}'
        },
        processing: true,
        responsive:true,
        pageLength: '10',
        destroy: true,
        order: [
            '0', 'desc'
        ],
    });

    $('#onus_unregistered_list').dataTable({
        "oLanguage": {
            "sUrl": '{{ asset(__('app.datatable')) }}'
        },
        processing: true,
        responsive:true,
        pageLength: '10',
        destroy: true,
        order: [
            '0', 'desc'
        ],
    });

    function modal_info_autorizacion(serviceId,sn,board,port,olt_id,type_id) {
        $('#btn_autorizar').attr("disabled", true);
        var url = '{{ route('smartolt.authorize', [':id',':sn',':board',':port',':olt_id',':type_id']) }}';
        url = url.replace(':id', serviceId);
        url = url.replace(':sn', sn);
        url = url.replace(':board', board);
        url = url.replace(':port', port);
        url = url.replace(':olt_id', olt_id);
        url = url.replace(':type_id', type_id);
        $.ajaxModal('#addEditModal', url);
    }
    function asociar_onu(serviceId,sn){
        bootbox.confirm( "@lang('app.¿Está seguro que desea asociar esta ONU?')" , function (result) {
            if (result) {

                $('#btn_asociar').attr('disabled', true);

                $.ajax({
                    type: "POST",
                    url: "/smartolt/asociar",
                    data: {
                        "serviceId": serviceId,
                        "sn": sn
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
