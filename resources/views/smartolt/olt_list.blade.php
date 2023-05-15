<div class="table-responsive">
        <table class="table table-bordered table-hover"  id="olt_list" style="width:100%;">
            <thead>
            <tr>
                <th>@lang('app.name')</th>
                <th>@lang('app.hardware version')</th>
                <th>@lang('app.ip')</th>
                <th>@lang('app.telnet_port')</th>
                <th>@lang('app.snmp_port')</th>
                <th>@lang('app.actions')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list_olt as $olt)
                <tr>
                    <td>{{ $olt->name }}</td>
                    <td>{{ $olt->olt_hardware_version }}</td>
                    <td>{{ $olt->ip }}</td>
                    <td>{{ $olt->telnet_port }}</td>
                    <td>{{ $olt->snmp_port }}</td>
                    <td class="col-md-1"><a class="green editar" title="Ver Detalle" href = "smartolt/{{$olt->id}}" ><i class = "ace-icon fa fa-list bigger-130" ></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
</div>
