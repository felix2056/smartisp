<div class="table-responsive">
    <div class="row">
        <div class="col-sm-12">
            <table class="table table-bordered table-hover"  id="onus_unregistered_list" style="width:100%;">

                <thead>
                <tr>
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
                            <td>{{ $onu_un->board }}</td>
                            <td>{{ $onu_un->port }}</td>
                            <td>{{ $onu_un->sn }}</td>
                            <td>{{ $onu_un->onu_type_name }}</td>
                            <td>{{ $onu_un->onu_type_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

