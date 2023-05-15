<h3>Información Fiscal (API: <a target="_blank" href="https://www.facturapi.io/">https://www.facturapi.io</a>)</h3>
<br />

<form action="" method="post" class="form_emisor form-horizontal" >
    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Api Key</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" id="api_key" name="api_key" placeholder="Api Key" value="{{@$invoice_mx->apikey}}" required />
        </div>
    </div>
    
    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Api Key Sandbox</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" id="api_testkey" name="api_testkey" placeholder="Api Key" required value="{{@$invoice_mx->apikey_sandbox}}"/>
        </div>
    </div>

    <div class="form-group w100">
        <label for="activate_mx" class="col-lg-2 control-label">Activar Produccion</label>
        <label class="switch">
            {{-- $status_factel 0 => Ecuador, 1 => Desactivado , 2 => Colombia, 3 => Mexico--}}
            <input type="checkbox" name="activate_live" value="activate_live" {{@$invoice_mx->is_live == 1 ? 'checked' : ''}}>
            <span class="slider round"></span>
        </label>
    </div>
    <div class="form-group w100">
        <label for="rfc" class="col-lg-2 control-label">RFC</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" name="rfc" id="rfc" placeholder="RFC" value="{{@$invoice_mx->rfc}}" required />
        </div>
    </div>
    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Código de Producto/Servicio</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" name="sat_prod_serv" id="cod" value="{{ isset( $invoice_mx->product_code ) ? $invoice_mx->product_code : '81112101'}}" placeholder="Código de Producto/Servicio" required />
        </div>
    </div>
    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Código para Unidad de medida</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" id="sat_unit" name="sat_unit" value="{{ isset($invoice_mx->unit_code) ? $invoice_mx->unit_code : 'EA'}}" placeholder="Código para Unidad de medida" required />
        </div>
    </div>

    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Serie para Facturas</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" id="sat_unit" name="serie" value="{{isset( $invoice_mx->serie ) ? $invoice_mx->serie : 'FACT' }}" placeholder="Serie" required />
        </div>
    </div>

    <div class="form-group w100">
        <label for="api_key" class="col-lg-2 control-label">Folio para Facturas</label>
        <div class="col-lg-10">
            <input type="text" class="form-control" id="sat_unit" name="folio" value="{{ isset( $invoice_mx->folio) ? $invoice_mx->folio : '1'}}" placeholder="Folio" required />
        </div>
    </div>

    <div class="form-group w100">
        <label for="activate_mx" class="col-lg-2 control-label">Activar Integración</label>
        <label class="switch">
            {{-- $status_factel 0 => Ecuador, 1 => Desactivado , 2 => Colombia, 3 => Mexico--}}
            <input type="checkbox" name="activate_mx" value="activate_mx" {{ $status_factel == '3' ? 'checked': '' }}>
            <span class="slider round"></span>
        </label>
    </div>
    <div class="text-center">
        <input type="hidden" name="status_factel" value="3">
        <input type="hidden" name="provider" value="facturapi">
        <button type="submit" class="btn btn-primary btn-sm" id="logoform2"> Guardar</button>
    </div>
</form>
