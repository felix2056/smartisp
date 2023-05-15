<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
                aria-hidden="true">&times;</span><span
                class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i
                class="fa fa-plug"></i> @lang('app.changeRouterIp')
    </h4>
</div>
<div class="modal-body">
    <form class="form-horizontal" role="form" id="changeIp">
        <div class="form-group">
            <label for="name"
                   class="col-sm-2 control-label">@lang('app.name')</label>
            <div class="col-sm-10">
                <input type="text" name="name"
                       class="form-control" id="name"
                       value="{{ $router->name ?? '' }}"
                       maxlength="30">
            </div>
        </div>
        <div class="form-group" id="modelfe">
            <label for="model"
                   class="col-sm-2 control-label">@lang('app.model')</label>
            <div class="col-sm-10">
                <input type="text" name="model"
                       class="form-control" id="model"
                       value="{{ $router->model ?? '' }}"
                       maxlength="30" readonly>
            </div>
        </div>
        <div class="form-group">
            <label for="newIp"
                   class="col-sm-2 control-label">@lang('app.ip')</label>
            <div class="col-sm-10">
                <input type="text" name="newIp" class="form-control"
                       id="newIp" maxlength="30" value="{{ $router->ip ?? '' }}">
            </div>
        </div>

        <div class="form-group" id="loginfe">
            <label for="inputLoginEdit"
                   class="col-sm-2 control-label">@lang('app.login')</label>
            <div class="col-sm-10">
                <input type="text" name="login_edit"
                       class="form-control" id="inputLoginEdit"
                       value="{{ $router->login ?? '' }}"
                       maxlength="50">
            </div>
        </div>
        <div class="form-group" id="portapife">
            <label for="inputPortEdit"
                   class="col-sm-2 control-label">@lang('app.port')
                <u>API</u></label>
            <div class="col-sm-10">
                <input type="text" name="port_edit"
                       class="form-control" id="inputPortEdit"
                       value="{{ $router->port ?? '' }}"
                       placeholder="8728">
            </div>
        </div>
        <div class="form-group">
            <label for="password"
                   class="col-sm-2 control-label">@lang('app.password')</label>
            <div class="col-sm-10">
                <input type="password" name="password"
                       class="form-control" id="password"
                       maxlength="50"
                       placeholder="@lang('app.new') @lang('app.password')">
            </div>
        </div>

        <div class="form-group">
            <input type="hidden" name="router_id">
            <label for="inputAddressEdit"
                   class="col-sm-2 control-label">@lang('app.direction')</label>
            <div class="col-sm-10">
                <input type="text" name="location"
                       class="form-control"
                       id="inputAddressEdit" maxlength="50" value="{{ $router->location ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label for="edilocation"
                   class="col-sm-2 control-label">@lang('app.location')</label>
            <div class="col-sm-8">
                <input type="text" name="coordinates"
                       class="form-control coordinates" id="coordinates" value="{{ $router->coordinates ?? '' }}">
            </div>

            <div class="col-sm-1">

                <button type="button"
                    class="btn btn-sm btn-danger"
                    id="btnmapedit" data-toggle="modal"
                    data-target="#modalmapedit"
                    title="@lang('app.open') Mapa"><i
                    class="fa fa-map"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default"
            data-dismiss="modal">@lang('app.close')</button>
    <button type="button" class="btn btn-primary" onclick="changeIp()"><i
                class="fa fa-floppy-o"></i>
        @lang('app.save')</button>
</div>

<script>
    function changeIp() {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('router.submit-change-ip', $router->id) }}",
            container: "#changeIp",
            data: $('#changeIp').serialize(),
            success: function(response) {
                if(response.status == 'success') {
                    $('#addEditModal').modal('hide');
                    treload.ajax.reload();
                }
            }
        });
    }
</script>
