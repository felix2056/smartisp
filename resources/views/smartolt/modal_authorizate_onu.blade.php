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
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-12">
{{--                    <form action="/smartolt/authorize_nuevo" method="post" onsubmit="myButton.disabled = true; myButton.val = '@lang('app.Autorizando, por favor espere...')' ; return true; ">--}}
                    <form action="/smartolt/authorize_nuevo" method="post" onsubmit="myButton.disabled = true; myButton.val = 'Autorizando, por favor espere...' ; return true; ">
                        @csrf

                        <input readonly required type="hidden" name="olt_id" class="form-control" id="olt_id" value="{{ $olt_id }}" >
                        <input readonly required type="hidden" name="id_service" class="form-control" id="id_service" value="{{ $id_service }}" >

                        <div class="row form-group">
                            <label for="cliente" class="col-sm-3 control-label">@lang('app.Client')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="cliente" class="form-control" id="cliente" value="{{ $cliente }}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="board" class="col-sm-3 control-label">@lang('app.Board')</label>
                                <div class="col-sm-9">
                                    <input readonly required type="text" name="board" class="form-control" id="board" value="{{$board}}" >
                                </div>
                        </div>
                        <div class="row form-group">
                            <label for="port" class="col-sm-3 control-label">@lang('app.Port')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="port" class="form-control" id="port" value="{{$port}}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="sn" class="col-sm-3 control-label">@lang('app.SN')</label>
                            <div class="col-sm-9">
                                <input readonly required type="text" name="sn" class="form-control" id="sn" value="{{$sn}}" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="type_id" class="col-sm-3 control-label">@lang('app.onus type')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="onu_type" id="onu_type">
                                    <option value="">@lang('app.none')</option>
                                    @foreach($onus_type as $type)
                                        <option @if($type->id == $type_id) selected @endif value="{{$type->name}}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row form-group">
                            <label for="custom_profile"
                                   class="col-sm-3 control-label">@lang('app.Custom profile')</label>
                            <div class="col-sm-9">
                                <label><input id="check_custom_profile" name="check_custom_profile" value="true"
                                              class="ace ace-switch ace-switch-6"
                                              type="checkbox"/>
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                        <div class="row form-group" hidden id="custom_profile_row">
                            <label for="type_id" class="col-sm-3 control-label">@lang('app.Custom profile')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="custom_profile" id="custom_profile">
                                    <option value="Generic_1">Generic_1</option>
                                    <option value="Generic_2">Generic_2</option>
                                    <option value="Generic_3">Generic_3</option>
                                    <option value="Generic_4">Generic_4</option>
                                    <option value="Generic_5">Generic_5</option>
                                    <option value="Generic_6">Generic_6</option>
                                </select>
                            </div>
                        </div>

                        <div class="row form-group">
                            <label for="type_id" class="col-sm-3 control-label">@lang('app.onus mode')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="onu_mode" id="onu_mode">
                                    <option @if($onu_mode == 'Routing') selected @endif value="Routing">Routing</option>
                                    <option @if($onu_mode == 'Bridging') selected @endif value="Bridging">Bridging</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="type_id" class="col-sm-3 control-label">@lang('app.pon type')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="pon_type" id="pon_type">
                                    <option value="gpon">GPON</option>
                                    <option value="epon">EPON</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="vlan_id" class="col-sm-3 control-label">@lang('app.vlans')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="vlan_id" id="vlan_id">
                                    <option value="">@lang('app.none')</option>
                                    @foreach($list_vlans as $vlan)
                                        <option value="{{$vlan->vlan}}">{{ $vlan->vlan }} - {{ $vlan->description }}</option>

                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="zone_id" class="col-sm-3 control-label">@lang('app.zonas')</label>
                            <div class="col-sm-9">
                                <select required class="form-control" name="zone" id="zone">
                                    <option value="">@lang('app.none')</option>
                                    @foreach($list_zones as $zone)
                                        <option value="{{$zone->name}}">{{ $zone->name }}</option>

                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button name="myButton" type="submit" class="btn btn-primary pull-right" autocomplete="off">
                            <i class="fa fa-floppy-o"></i> @lang('app.autorizar')
                        </button>
                    </form>
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
$('#check_custom_profile').change(function(){
    var check = $('#check_custom_profile').is(":checked");
    if(check){
        $('#custom_profile_row').removeAttr("hidden");
    }
    else{
        $('#custom_profile_row').attr("hidden",true);
    }
});
</script>
