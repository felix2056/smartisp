
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
                            
                            <th width="5%">Codigo</th>
                            <th width="5%">url</th>
                            <th width="5%">nombreComercial</th>
                            <th width="5%">direccion</th>
                    </thead>
                    <tbody>

                    <td> <input type="text" name="valor" id="valor_codigo"><br> </td>
                    <td> <input type="text" name="valor" id="valor_url"><br> </td>
                    <td> <input type="text" name="valor" id="valor_nombreComercial"><br> </td>
                    <td> <input type="text" name="valor" id="valor_direccion"><br> </td>
                    
                    
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
        var valor =  $('#valor_codigo').val();
        var val_url =  $('#valor_url').val();
        var nombreComercial =  $('#valor_nombreComercial').val();
        var direccion =  $('#valor_direccion').val();
        
        
        var url = '{{ route('establecimientos.create') }}';
        var accion = 'crear';
        
        $.easyAjax({
            type: 'POST',
            url: url,
            data: { valor: valor,val_url:val_url,nombreComercial: nombreComercial,direccion: direccion,accion: accion} ,
            success: function (response) {
                location.reload();
            }
        });
    });


</script>
