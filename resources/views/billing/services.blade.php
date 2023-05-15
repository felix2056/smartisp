<style type="text/css">
    #idrefre {
        top: 93px !important;
        cursor: pointer;
        z-index: 1;
        padding: 1px 8px;
    }

    #idrefre:hover {
        background: #000;
    }
    #service-table_wrapper .dt-buttons{
        display: none;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 widget-container-col">
                <div class="widget-box widget-color-blue2">
                    <div class="widget-body">
                        <div class="widget-main">
                            <!--Contenido widget-->
                            @php
                                use App\Http\Controllers\PermissionsController;
                            @endphp
                            @if(PermissionsController::hasAnyRole('servicio_new'))
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-success" data-toggle="modal"
                                                onclick="addService();return false;"><i class="icon-plus"></i> New
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table id="service-table" class="table table-bordered table-hover">
                                    <div id="idrefre">
                                        <i class="ace-icon fa fa-refresh"></i>
                                    </div>
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang('app.service')</th>
                                        <th> @lang('app.plan')</th>
                                        <th>@lang('app.cost')</th>
                                        <th>@lang('app.ip')</th>
                                        <th>@lang('app.router')</th>
                                        <th>@lang('app.dateOfAddmission')</th>
                                        <th>@lang('app.actions')</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Modal info client-->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalinfo" role="dialog"
         aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">{{ __('app.close') }}</span></button>
                    <h4 class="modal-title"><i class="fa fa-info-circle" aria-hidden="true"></i>
                        <span id="infotitle"></span></h4>
                </div>

                <div class="modal-body">


                    <div class="box-body">

                        <!--inicio info-->
                        <div class="profile-user-info profile-user-info-striped">

                            <div class="profile-info-row">
                                <div class="profile-info-name"> {{ __('app.payday') }}</div>
                                <div class="profile-info-value">
                                    <span class="editable" id="infopaydate"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.plan') }}</div>
                                <div class="profile-info-value">
                                    <span class="editable" id="infoplan"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.notice') }} email</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoemail"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.notice') }} SMS</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infosms"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.serviceCut') }}</div>
                                <div class="profile-info-value">
                                    <span class="editable" id="infocut"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.service') }}</div>
                                <div class="profile-info-value">
                                    <span class="editable" id="infoservice"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.onRouter') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="inforouter"></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-name">IP</div>
                                <div class="profile-info-value">
                                    <span class="editable" id="infoip"></span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">Mac</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infomac"></span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.control') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infocontrol"></span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">Portal {{ __('app.client') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal"></span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.email') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal">{{ $clients->email }}</span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.telephone') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal">{{ $clients->phone }}</span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">{{ __('app.direction') }}</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal">{{ $clients->address }}</span>
                                </div>
                            </div>

                            <div class="profile-info-row">
                                <div class="profile-info-name">RUC/Ci</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal">{{ $clients->dni }}</span>
                                </div>
                            </div>


                            <div class="profile-info-row">
                                <div class="profile-info-name">@lang('app.coordinates')</div>

                                <div class="profile-info-value">
                                    <span class="editable" id="infoportal">{{ $clients->coordinates }}</span>
                                </div>
                            </div>

                        </div>
                        <!--fin info-->


                        <!--<table class="table" width="100%" border="0">
                          <tbody>
                              <tr>
                              <td style="height: 34px" width="50%" align="right">Día de Pago</td>
                              <td width="50%"><span class="label label-warning" style="font-size: 12px;" id="infopaydate"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">Plan</td>
                              <td><span class="label label-warning" style="font-size: 12px;" id="infoplan"></span></td>
                            </tr>
                            <tr>
                              <td style="height: 34px" align="right">Aviso email</td>
                              <td><span class="label" style="font-size: 12px;" id="infoemail"></span></td>
                            </tr>
                                <tr>
                              <td style="height: 34px" align="right">Aviso SMS</td>
                              <td><span class="label" style="font-size: 12px;" id="infosms"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">Corte servicio</td>
                              <td><span class="label label-danger" style="font-size: 12px;" id="infocut"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">Servicio</td>
                              <td><span class="label" style="font-size: 13px;" id="infoservice"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">En router</td>
                              <td><span class="label label-info" style="font-size: 13px;" id="inforouter"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">IP</td>
                              <td><span class="label label-info" style="font-size: 13px;" id="infoip"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">Mac</td>
                              <td><span class="label label-info" style="font-size: 13px;" id="infomac"></span></td>
                            </tr>

                            <tr>
                              <td style="height: 34px" align="right">Control</td>
                              <td><span class="label label-info" style="font-size: 13px;" id="infocontrol"></span></td>
                            </tr>


                            <tr>
                              <td style="height: 34px" align="right">portal cliente</td>
                              <td><span class="label label-info" style="font-size: 13px;" id="infoportal"></span></td>
                            </tr>

                            </tbody>
                        </table>-->
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('app.close') }}</button>

                </div>

            </div>
        </div>
    </div>

    <!--start modal tools-->
    <div class="modal fade" id="tools" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                            class="sr-only">{{ __('app.close') }}</span></button>
                    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-wrench"></i>
                        {{ __('app.tools') }}</h4>
                </div>
                <div class="modal-body" id="winnew">

                    <!--cuerpo Modal-->
                    <div class="tabbable">
                        <ul class="nav nav-tabs" id="rtool">
                            <li class="active"><a id="pin" data-toggle="tab" href="#pingclient"><i
                                        class="fa fa-exchange"></i> Ping - Mikrotik</a></li>
                            <li><a id="torc" href="#torchmk" role="tab" data-toggle="tab"><i
                                        class="fa fa-random"></i> Torch - Mikrotik</a></li>
                            <li><a id="traf" href="#trafic" role="tab" data-toggle="tab"><i
                                        class="fa fa-bar-chart"></i> {{ __('app.traffic') }}</a></li>
                            {{--    <li id="iniciar_stadisticas"><a id="traf" href="#estadisticas"  role="tab" data-toggle="tab"><i class="fa fa-bar-chart"></i> Estadísticas</a></li>  --}}
                        </ul>
                        <div class="tab-content" id="mytabs">
                            <div id="pingclient" class="tab-pane fade in active">

                                <form class="form-horizontal" id="formapingclient">
                                    <div class="form-group">
                                        <label for="ipt" class="col-sm-2 control-label">Ping a</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="ipt" class="form-control"
                                                   autocomplete="off" id="ipt" maxlength="40">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="interface"
                                               class="col-sm-2 control-label">{{ __('app.interface') }}</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="interface"
                                                    id="interface"></select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="packages"
                                               class="col-sm-2 control-label">{{ __('app.packages') }}</label>
                                        <div class="col-sm-10">
                                            <input type="number" name="packages" value="2" min="1"
                                                   class="form-control" autocomplete="off" id="packages"
                                                   maxlength="20">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="arps" class="col-sm-2 control-label">ARP
                                            Ping</label>
                                        <div class="col-sm-10">
                                            <label>
                                                <input name="arp" id="arps" value="1"
                                                       class="ace ace-switch ace-switch-6"
                                                       type="checkbox"/>
                                                <span class="lbl"></span>
                                            </label>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="btnping" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-sm btn-success"
                                                    id="btnping">{{ __('app.start') }}
                                            </button>
                                        </div>
                                        <input type="hidden" name="router" id="rtid">
                                        <input type="hidden" name="service_id" id="service_id">

                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table" id="table-ping">

                                        <thead>
                                        <tr>
                                            <th>Host</th>
                                            <th>{{ __('app.size') }}</th>
                                            <th>TTL</th>
                                            <th>{{ __('app.weather') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>

                                    </table>
                                </div>
                                <br>

                                <!--cuerpo Modal-->


                            </div>
                            <div id="torchmk" class="tab-pane fade">
                                <form class="form-horizontal" role="form" id="formtorch">


                                    <div class="form-group">
                                        <label for="slcrouter"
                                               class="col-sm-2 control-label">{{ __('app.interface') }}</label>
                                        <div class="col-sm-10">
                                            <select class="form-control" name="interface"
                                                    id="slinterface"></select>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="srca" class="col-sm-2 control-label">Src.
                                            Address</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="srcaddress" id="srca"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="dst" class="col-sm-2 control-label">Dst.
                                            Address</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="dstaddress" value="0.0.0.0/0"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="duration" class="col-sm-2 control-label">{{ __('app.duration') }}
                                            Seg.</label>
                                        <div class="col-sm-10">
                                            <input type="number" min="1" name="duration" value="3"
                                                   class="form-control">
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="btntorch" class="col-sm-2 control-label"></label>
                                        <div class="col-sm-10">
                                            <button class="btn btn-sm btn-success" id="btntorch">
                                                {{ __('app.start') }}
                                            </button>
                                        </div>
                                    </div>


                                </form>
                                <div class="table-responsive">
                                    <table class="table" id="table-torch">

                                        <thead>
                                        <tr>
                                            <th>Src.</th>
                                            <th>Dst.</th>
                                            <th>Src port.</th>
                                            <th>Dst port</th>
                                            <th>Tx</th>
                                            <th>Rx</th>
                                            <th>Tx Packet</th>
                                            <th>Rx Packet</th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>

                                    </table>
                                </div>
                                <br>


                                <!--end tab-->
                            </div>

                            <div role="tabpanel" class="tab-pane" id="trafic">
                                <input type="hidden" id="clid">
                                <input type="hidden" id="namecl">


                                <div id="tlan"
                                     style="min-width: 541px; height: 400px; margin: 0 auto"></div>

                                <center><strong>
                                        <div id="trafico"></div>
                                    </strong></center>


                            </div>


                        {{-- <div role="tabpanel" class="tab-pane" id="estadisticas">


                        </div> --}}


                        <!--end tab-->
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <!--end modal tools-->
</div>
<script>
    $(document).ready(function () {

        $(document).on('click', '#btnping', function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            /* Act on the event */
            var data = $('#formapingclient').serialize();

            $('#btnping').html('<i class="fa fa-cog fa-spin"></i> Haciendo ping...');

            $("#table-ping tbody tr").remove();

            $.ajax({
                "url": baseUrl + "/tools/ping",
                "type": "POST",
                "data": data,
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {


                $.each(data, function (i, val) {

                    var cl = '';

                    if (val['size'] === undefined)
                        var size = '-----'
                    else
                        var size = val['size'];

                    if (val['ttl'] === undefined) {
                        var ttl = '-----'
                    } else {
                        var ttl = val['ttl'];
                        var cl = 'green';
                    }


                    if (val['time'] === undefined) {
                        var time = val['status'];
                        var cl = 'red';
                    } else {
                        var time = val['time'];
                        var cl = 'green';
                    }


                    $('#table-ping').append('<tr><td>' + val['host'] + '</td><td>' + size + '</td><td>' + ttl + '</td><td class="' + cl + '">' + time + '</td></tr>');
                });

                $('#btnping').html('Iniciar');

                if (typeof treload != "undefined" && typeof treload != undefined) {
                    console.log(typeof treload, 'Hello from typeof');
                    treload.ajax.reload();
                }

            });


        });

        //funcion para hacer torch
        $(document).on('click', '#btntorch', function(event) {
            event.preventDefault();
            /* Act on the event */

            var data = $('#formtorch').serializeArray();
            data.push({ name: 'router', value: $('#rtid').val() });

            $('#btntorch').html('<i class="fa fa-cog fa-spin"></i> Haciendo torch...');

            $("#table-torch tbody tr").remove();

            $.ajax({
                "url": baseUrl+"/tools/torch",
                "type": "POST",
                "data": data,
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {



                $.each(data, function(i, val) {


                    if (val['src-address'] === undefined)
                        var src_address = '-----'
                    else
                        var src_address = val['src-address'];

                    if (val['dst-address'] === undefined)
                        var dst_address = '-----';
                    else
                        var dst_address = '<a href="http://' + val['dst-address'] + '" target="_blank">' + val['dst-address'] + '</a>';

                    if (val['src-port'] === undefined)
                        var src_port = '-----';
                    else
                        var src_port = val['src-port'];

                    if (val['dst-port'] === undefined)
                        var dst_port = '-----';
                    else
                        var dst_port = val['dst-port'];


                    $('#table-torch').append('<tr><td>' + src_address + '</td><td>' + dst_address + '</td><td>' + src_port + '</td><td>' + dst_port + '</td><td>' + val['tx'] + '</td><td>' + val['rx'] + '</td><td>' + val['tx-packets'] + '</td><td>' + val['rx-packets'] + '</td></tr>');
                });

                $('#btntorch').html('Iniciar');

            });




        });


        $("#idrefre").click(function () {
            location.reload();
        });

        //bloquear cliente
        $(document).on("click", ".ban-service", function (event) {
            event.stopImmediatePropagation();
            var idc = $(this).attr("id");

            var url = '{{ route('billing.services.ban', ':id') }}';
            url = url.replace(':id', idc);


            bootbox.dialog({
                message: '<form class="bootbox-form"><input class="bootbox-input bootbox-input-text form-control" name="reason" id="reason" autocomplete="off" type="text"></form>',
                title: "{{ __('messages.activateCustomerService') }}",
                buttons: {
                    cancel: {
                        label: "Cancel",
                        className: 'btn-danger',
                        callback: function(result){
                            console.log('Custom cancel clicked');
                        }
                    },
                    confirm: {
                        label: "Confirm",
                        className: 'btn-info',
                        callback: function(){

                            var result =  $('#reason').val();
                            if (result) {
                                $.ajax({
                                    "type": "POST",
                                    "url": url,
                                    "data": {"id": idc, reason: result},
                                    "dataType": "json",
                                    'error': function (xhr, ajaxOptions, thrownError) {
                                        debug(xhr, thrownError);
                                    }
                                }).done(function (data) {
                                    if (data[0].msg == 'error') {
                                        msg('{{ __('messages.CouldNotCut') }}', 'error');
                                    }
                                    if (data[0].msg == 'banned') {
                                        msg('{{ __('messages.serviceWasCut') }}', 'info');
                                        window.LaravelDataTables["client-table"].draw();
                                    }
                                    if (data[0].msg == 'unbanned') {
                                        msg('{{ __('messages.serviceIsActivated') }}', 'info');
                                        window.LaravelDataTables["client-table"].draw();
                                        check_is_online();
                                    }
                                    if (data[0].msg == 'errorConnect')
                                        msg('{{ __('messages.verifyThatonline') }}', 'error');
                                    if (data[0].msg == 'errorConnectLogin')
                                        msg('{{ __('messages.verifyTheAccessData') }}', 'error');
                                    //mikrotik errors
                                    if (data[0].msg == 'mkerror') {
                                        $.each(data, function (index, value) {
                                            msg(value.message, 'mkerror');
                                        });
                                    }
                                });
                            }
                            else {
                                msg('The reason field is required.', 'error');
                                return false;
                            }
                        }
                    }
                }
            });


        });

        //eliminar cliente
        $(document).on("click", '.deletes', function (event) {
            event.stopImmediatePropagation();
            var idp = $(this).attr("id");
            var url = '{{ route('billing.service/delete', ':id') }}';
            url = url.replace(':id', idp);
            bootbox.confirm("{{__('messages.permanentlyEliminateThe')}}", function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {"id": idp},
                        dataType: "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {
                        serviceTable.ajax.reload();
                        if (data[0].msg == 'success')
                            msg('{{ __('messages.clientWasDeleted') }}', 'success');
                        if (data[0].msg == 'errorConnect')
                            msg('{{ __('messages.removedFromTheRouterSince') }}', 'info');

                        if (data[0].msg == 'mkerror') {
                            $.each(data, function (index, value) {
                                msg(value.message, 'mkerror');
                            });
                        }


                    });
                }
            });
        });

        //funcion para obtener info del cliente
        $(document).on('click', '.info', function(event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            /* Act on the event */

            var id = $(this).attr("id");

            //obtenemos el tipo de control
            $.ajax({
                "url": '{{ route('client/getservice/info') }}',
                "type": "POST",
                "data": { 'id': id },
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {

                $('#infotitle').text('Información - ' + data.name);
                $('#infopaydate').text(data.paydate);
                $('#infoplan').text(data.plan);

                //reset class
                $('#infoemail').removeClass('label-danger');
                $('#infoemail').removeClass('label-success');
                $('#infosms').removeClass('label-danger');
                $('#infosms').removeClass('label-success');
                $('#infoservice').removeClass('label-success');
                $('#infoservice').removeClass('label-danger');

                if (data.email == 'Desactivado') {
                    $('#infoemail').addClass('label-danger').text(data.email);
                } else {
                    $('#infoemail').addClass('label-success').text(data.email);
                }

                if (data.sms == 'Desactivado') {
                    $('#infosms').addClass('label-danger').text(data.sms);
                } else {
                    $('#infosms').addClass('label-success').text(data.sms);
                }

                $('#infocut').text(data.cut);

                if (data.status == 'ac') {
                    $('#infoservice').text('Activo').addClass('label-success');
                } else {
                    $('#infoservice').text('Cortado').addClass('label-danger');
                }

                $('#inforouter').text(data.router);
                $('#infoip').text(data.ip);
                $('#infomac').text(data.mac);

                switch (data.control) {
                    case 'sq':
                        $('#infocontrol').text('Simple Queues');
                        break;
                    case 'st':
                        $('#infocontrol').text('Simple Queues (with Tree)');
                        break;
                    case 'ho':
                        $('#infocontrol').text('Hotspot - User Profiles');
                        break;
                    case 'ha':
                        $('#infocontrol').text('Hotspot - PCQ Address List');
                        break;
                    case 'dl':
                        $('#infocontrol').text('DHCP Leases');
                        break;
                    case 'pp':
                        $('#infocontrol').text('PPPoE - Secrets');
                        break;
                    case 'ps':
                        $('#infocontrol').text('PPPoE - Simple Queue');
                        break;
                    case 'pt':
                        $('#infocontrol').text('PPPoE - Secrets Simple Queues (with Tree)');
                        break;
                    case 'pa':
                        $('#infocontrol').text('PPPoE - Secrets - PCQ Address List');
                        break;
                    case 'pc':
                        $('#infocontrol').text('PCQ Address List');
                        break;
                    default:
                        $('#infocontrol').text('Ninguno');
                }

                if (data.portal == '1') {
                    $('#infoportal').text('Si');
                } else {
                    $('#infoportal').text('No');
                }


                $('#modalinfo').modal('show');

            });


        });

        $(document).on('click', '.tool', function(event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            /* Act on the event */

            startloading('body', 'Cargando...');
            //mostramos siempre el primer tab

            $('#rtool a[href="#pingclient"]').tab('show');


            var idc = $(this).attr('id');

            $('#clid').val(idc);

            $.ajax({
                "url": '{{ route('client/getservice/tools') }}',
                "type": "POST",
                "data": { "id": idc },
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {

                if (data.success) {

                    if (data.typecontrol == 'pc' || data.typecontrol == 'ha' || data.typecontrol == 'no') {
                        //ocultamos la pestaña trafico
                        $('#traf').hide();
                    } else {
                        $('#traf').show();
                    }
                    $('#namecl').val(data.name);
                    $('#ipt,#srca').val(data.ip);
                    $('#rtid').val(data.router_id);
                    $('#service_id').val(data.service_id);
                    $('#interface,#slinterface').empty();
                    $('#interface,#slinterface').append($('<option>').text('').attr('value', '').prop('selected', true));
                    var lan = data.lan;

                    $ps = $.each(data.interfaces, function(i, val) {
                        $('#interface,#slinterface').append($('<option>').text(val['name'] + '/' + val['default-name']).attr('value', val.name));
                    });


                    $.when($ps).done(function() {

                        $("#slinterface").children().filter(function() {
                            return $(this).val() == lan;
                        }).prop('selected', true);



                    });

                    $('body').loadingModal('destroy');

                    $('#tools').modal('show');


                } else {

                    $('body').loadingModal('destroy');

                    alert('Error al obtener datos');

                }

            });



        });

        var table;
        renderDataTable();
    });

    function startloading(selector, text) {

        $(selector).loadingModal({
            position: 'auto',
            text: text,
            color: '#fff',
            opacity: '0.7',
            backgroundColor: 'rgb(0,0,0)',
            animation: 'spinner'
        });
    }

    function renderDataTable() {
        if ($.fn.DataTable.isDataTable('#service-table')) {
            $('#service-table').dataTable().fnClearTable();
            $('#service-table').dataTable().fnDestroy();
        }
        serviceTable = $('#service-table').DataTable({
            "oLanguage": {
                "sUrl": '{{ asset(__('app.datatable')) }}'
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            serverSide: true,
            responsive:true,
            pageLength: '10',
            destroy: true,
            order: [
                '0', 'desc'
            ],
            buttons: [
                'excel', 'csv'
            ],
            ajax: {
                "url": "{{ route('billing.services.list') }}",
                "type": "POST",
                "cache": false,
                data: function (d) {
                    d.client_id = '{{ $clients->id }}';
                }
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'status', data: 'status', sortable: false},
                {name: 'plan.name', data: 'plan.name'},
                {name: 'plan.cost', data: 'plan.cost'},
                {name: 'ip', data: 'ip'},
                {name: 'router.name', data: 'router.name'},
                {name: 'date_in', data: 'date_in'},
                {name: 'action', data: 'action', sortable: false, searchable: false},
            ]
        });
    }

    function addService() {
        var url = '{{route('billing.services.create', $clients->id)}}';

        $.ajaxModal('#addEditModal', url);
    }

    function banHistory(serviceId) {
        var url = '{{ route('billing.services.ban-history', ':id') }}';
        url = url.replace(':id', serviceId);

        $.ajaxModal('#addEditModal', url);
    }

    function onusUnregistered(serviceId) {
        var url = '{{ route('smartolt.check_information', ':id') }}';
        url = url.replace(':id', serviceId);

        $.ajaxModal('#addEditModal', url);
    }

    function edit(id) {
        var url = '{{route('billing.services.edit', ':id')}}';
        url = url.replace(':id', id)

        $.ajaxModal('#addEditModal', url);
    }
    function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

    $('#traf').click(function(e){

        var namecl = $('#namecl').val();
        var chart;

        function requestDatta(id) {
            $.ajax({
                url: '{{ route('client/getservice/trafic') }}',
                type:"POST",
                data:{id:id},
                datatype: "json",
                success: function(data) {
                    var midata = JSON.parse(data);
                    if( midata.length > 0 ) {
                        var TX=parseInt(midata[0].data);
                        var RX=parseInt(midata[1].data);
                        var x = (new Date()).getTime();
                        shift=chart.series[0].data.length > 19;
                        chart.series[0].addPoint([x, TX], true, shift);
                        chart.series[1].addPoint([x, RX], true, shift);
                        document.getElementById("trafico").innerHTML="<span style='color:#058DC7'>"+formatBytes(TX) + "</span> / <span style='color:#50B432'>" + formatBytes(RX)+"</span>";
                    }else{
                        document.getElementById("trafico").innerHTML="- / -";
                    }
                },
                cache: false,
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown);
                }
            });
        }

        Highcharts.createElement('link', {
            href: 'https://fonts.googleapis.com/css?family=Signika:400,700',
            rel: 'stylesheet',
            type: 'text/css'
        }, null, document.getElementsByTagName('head')[0]);

        // Add the background image to the container
        Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
            proceed.call(this);
            this.container.style.background =
                'url(https://www.highcharts.com/samples/graphics/sand.png)';
        });


        Highcharts.setOptions({
            global: {
                useUTC: false
            }
        });

        chart = new Highcharts.Chart({
            chart: {
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.5
                    }
                },
                renderTo: 'tlan',
                animation: Highcharts.svg,
                type: 'areaspline',
                events: {
                    load: function () {
                        trafcl = setInterval(function () {
                            requestDatta($('#clid').val());
                        }, 1000);
                    }
                }
            },
            title: {
                text: 'Tráfico - '+namecl
            },
            xAxis: {
                type: 'datetime',
                tickPixelInterval: 150,
                maxZoom: 20 * 1000
            },
            yAxis: {
                minPadding: 0.2,
                maxPadding: 0.2,
                title: {
                    text: 'Tráfico actual',
                    margin: 25
                }
            },
            series: [{
                name: 'Descarga (Down)',
                data: []
            }, {
                name: 'Subida (Up)',
                data: []
            }]
        });


        $('#tools').on('hidden.bs.modal', function () {
            clearInterval(trafcl);
        });

        $('#pin,#torc').click(function(event) {
            clearInterval(trafcl);
        });

    });
    //funcion para obtener las IP/Redes
    function getNet(sl, sel) {
        $.ajax({
            "url": baseUrl+"/router/getrouter/ipnet",
            "type": "POST",
            "data": { "id": sl },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            if (data.net == 'notfound') {
                msg('No se encontraron ip/redes para este router, debe agregar al menos una IP/Red, ingrese a <b>routers</b> opción editar "icono del lápiz" posterior a la pestaña ip/redes.', 'error');
                $(sel).hide('fast');
            } else if (data.net == 'full') {
                msg('No se encontraron ip disponibles para este router, debe agregar una nueva IP/Red, ingrese a <b>Gestión de red - IP redes</b>', 'error');
                $(sel).hide('fast');
            } else {
                $(sel).show('fast');
            }
        });
    }
</script>
