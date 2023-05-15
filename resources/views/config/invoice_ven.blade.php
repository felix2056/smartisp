<div class="row">
    <div class="col-sm-12">
        <form action="" id="saveVenezuala" method="post" class="saveVenezuala" >

            <div class="form-group">
                <label for="activate_mx" class="col-lg-2 control-label">Activar Integración</label>
                <label class="switch">
                    {{-- $status_factel 0 => Ecuador, 1 => Desactivado , 2 => Colombia, 3 => Mexico--}}
                    <input type="checkbox" name="activate_ven" value="1" {{ $status_ven == 1 ? 'checked': '' }}>
                    <span class="slider round"></span>
                </label>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-left">
                        <button type="button" class="btn btn-primary btn-sm" id="saveVenezuala1"> Guardar</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
