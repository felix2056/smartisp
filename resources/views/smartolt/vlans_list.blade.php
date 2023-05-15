<div class="row">
    <button type="button" class="btn btn-sm btn-success newcl" data-toggle="modal"
            data-target="#addVlan">
        <i class="icon-plus"></i> {{ __('app.new') }} {{ __('app.vlan') }}
    </button>
</div>-

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-bordered table-hover"  id="vlans_list" style="width:100%;">

                <thead>
                <tr>
                    <th>@lang('app.vlan')</th>
                    <th>@lang('app.description')</th>
                    <th>@lang('app.scope')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($list_vlans as $vlan)
                    <tr>
                        <td>{{ $vlan->vlan }}</td>
                        <td>{{ $vlan->description }}</td>
                        <td>{{ $vlan->scope }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

<div class="modal fade" id="addVlan"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><i
                        class="fa fa-user-plus"></i>
                    {{ __('app.add') }} {{ __('app.new') }} {{__('app.vlan')}}</h4>
            </div>
            <div class="modal-body" id="winnew">
                <form class="form-horizontal" id="formaddvlan" action="/smartolt/vlan" method="post">
                    {{ csrf_field() }}

                    <div class="form-group" hidden>
                        <div class="col-sm-12">
                            <input type="number" hidden name="olt_id" id="olt_id" class="form-control" value="{{$olt_id}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">@lang('app.idvlan')</label>
                        <div class="col-sm-9">
                            <input type="number" max="4096" name="id_vlan" class="form-control" id="id_vlan" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">@lang('app.Descripción')</label>
                        <div class="col-sm-9">
                            <input type="text" name="descripcion" class="form-control" id="descripcion" >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="for_iptv" class="col-sm-3 control-label"><p class="text-success"> {{ __('app.Used for IPTV') }}</p></label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input name="for_iptv" type="checkbox" class="ace" id="for_iptv"/>
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="for_mgmt_voip" class="col-sm-3 control-label"><p class="text-success"> {{ __('app.Used for Mgmt /VoIP') }}</p></label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input name="for_mgmt_voip" type="checkbox" class="ace" id="for_mgmt_voip"/>
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dhcp_snooping" class="col-sm-3 control-label"><p class="text-success">{{ __('app.DHCP Snooping') }}</p></label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input name="dhcp_snooping" type="checkbox" class="ace" id="dhcp_snooping"/>
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lan_to_lan" class="col-sm-3 control-label"><p class="text-success"> {{ __('app.LAN-to-LAN') }}</p></label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input name="lan_to_lan" type="checkbox" class="ace" id="lan_to_lan"/>
                                    <span class="lbl"></span>
                                </label>
                            </div>
                        </div>
                    </div>


                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">{{ __('app.close') }}</button>
                    <button type="submit" class="btn btn-primary editbtnclient" data-loading-text="@lang('app.saving')..."><i
                            class="fa fa-floppy-o"></i>
                        {{ __('app.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
