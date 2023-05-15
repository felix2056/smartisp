<div class="table-responsive">
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-hover"  id="onus_list" style="width:100%;">
                <thead>
                <tr>
                    <th>@lang('app.Client')</th>
                    <th>@lang('app.SN')</th>
                    <th>@lang('app.Board')</th>
                    <th>@lang('app.Port')</th>
                    <th>@lang('app.Signal')</th>
                    <th>@lang('app.ONU/OLT Rx signal')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($list_onus as $onu)
                    <tr>
                        <td>{{ isset($onu['name']) ? $onu['name'] : "Sin nombre registrado" }}</td>
                        <td>{{ $onu['sn'] }}</td>
                        <td>{{ $onu['board'] }}</td>
                        <td>{{ $onu['port'] }}</td>
                        <td>{{ isset($onu['signal']) ? $onu['signal'] : '-' }}</td>
                        <td>
                            @if(isset($onu['signal_value'])) {{$onu['signal_value']}}
                            @else
                                {{ isset($onu['signal_1490']) ? $onu['signal_1490'] : '-' }}
                                /
                                {{ isset($onu['signal_1310']) ? $onu['signal_1310'] : '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

