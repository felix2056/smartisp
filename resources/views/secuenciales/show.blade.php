<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Actualizar valor de la secuencia</h4>
</div>
<div class="modal-body">
    <div class="splynx-dialog-content">


        <hr>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-responsive" style="width: 99%;border-bottom: none;border-left: none" id="admin_customers_billing_invoice_items">
                    <thead>
                        <tr>
                            <th width="5%">valor</th>
                    </thead>
                    <tbody>

                    <td> <input type="text" name="valor" id="valor_secuencia"><br> </td>
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
        var valor =  $('#valor_secuencia').val();
        var url = '{{ route('secuenciales.save', ':valor') }}';
        url = url.replace(':valor', valor)

        $.easyAjax({
            type: 'GET',
            url: url,
            container: "#export-history-table",
            success: function (response) {
                if (response.status == "success") {
                    table.draw();
                }
            }
        });
    });


</script>
