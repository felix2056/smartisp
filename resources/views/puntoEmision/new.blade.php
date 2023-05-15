
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Nuevo establecimiento</h4>
</div>
<div class="modal-body">
    <div class="splynx-dialog-content">


        <hr>
        <div class="row">
            <div class="col-lg-12">


                <table class="table table-bordered table-responsive" style="width: 99%;border-bottom: none;border-left: none" id="admin_customers_billing_invoice_items">
                    <thead>
                        <tr>
                            <th width="5%">Establecimiento</th>
                            <th width="5%">Nombre</th>
                            <th width="5%">Codigo</th>
                    </thead>
                    <tbody>

                    <td> <select>
                            @foreach ($establecimiento_data as $establecimiento)
                                <option id="valor_establecimiento" value="{{ $establecimiento->id }}">{{ $establecimiento->codigo }}</option>
                            @endforeach
                        </select><br> </td>
                    <td> <input type="text" name="valor" id="valor_nombre"><br> </td>
                    <td> <input type="text" name="valor" id="valor_codigo"><br> </td>

                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" id="save_secuencia">@lang('app.save')</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
</div>
<script>
    $('#save_secuencia').on('click', function (event) {
        var val_establecimiento =  $('#valor_establecimiento').val();
        var val_nombre =  $('#valor_nombre').val();
        var val_codigo =  $('#valor_codigo').val();
        
        var url = '{{ route('ptoEmision.create') }}';
        var accion = 'crear';
        
        $.easyAjax({
            type: 'POST',
            url: url,
            data: { val_establecimiento: val_establecimiento,val_nombre:val_nombre,val_codigo: val_codigo,accion: accion} ,
            success: function (response) {
                  location.reload();
            }
        });
    });


</script>
