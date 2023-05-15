<div class="row">
    <button type="button" class="btn btn-sm btn-success newcl" data-toggle="modal"
            data-target="#addZona">
        <i class="icon-plus"></i> {{ __('app.new') }} {{ __('app.zonas') }}
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover"  id="zones_list" style="width:100%;">
        <thead>
        <tr>
            <th>@lang('app.name')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list_zones as $zone)
            <tr>
                <td>{{ $zone->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addZona"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span
                        aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><i
                        class="fa fa-user-plus"></i>
                    {{ __('app.add') }} {{ __('app.new') }} {{__('app.zonas')}}</h4>
            </div>
            <div class="modal-body" id="winnew">
                <form class="form-horizontal" id="formaddzona" action="/smartolt/zona" method="post">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label for="nombre_zona" class="control-label">Nombre Zona</label>
                            <input type="text" name="nombre_zona" class="form-control" id="nombre_zona" >
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
