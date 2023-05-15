<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Agregar a item la Nota</h4>
</div>
<div class="modal-body">
    <div class="row">
        <table class="table table-striped">
            <thead>
                <th>Id</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>% IVA</th>
                <th>Total IVA</th>
                <th>Total</th>
                <th>Add</th>
            </thead>
            <tbody>
            @foreach ($bill_customer_item as $d)
                <tr>
                    <td>{{($d->plan_id=='')?$d->id:$d->plan_id}}</td>
                    <td>{{$d->description}}</td>
                    <td>{{$d->quantity}}</td>
                    <td>{{$d->price}}</td>
                    <td>{{$d->iva}}</td>
                    <td>{{round(($d->iva/1000)*$d->quantity*$d->price,2)}}</td>
                    <td>{{$d->total}}</td>
                    <td><a data-dismiss="modal" class="btn btn-default btn-sm" onclick="addItem({{($d->plan_id=='')?$d->id:$d->plan_id}},'{{$d->description}}','{{$d->quantity}}','{{$d->price}}','{{$d->iva}}')";>Add</a></td>
                </tr>
            @endforeach            
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button id="" type="button" onclick="activarscroll();" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
</div>
<script>
    function addItem(id,description,quantity,price,iva){
        var resp=true;
        $( "#admin_customers_billing_note_items tbody tr .class_id" ).each(function( index ) {
            if(id==$(this).val()){
                resp=false;
            }
        });
        if(resp){
            $('#{{$idtr}}').find('.class_id').val(id);
            $('#{{$idtr}}').find('.description').val(description);
            $('#{{$idtr}}').find('.quantity').val(quantity);
            $('#{{$idtr}}').find('.price').val(price);
            $('#{{$idtr}}').find('.iva').val(iva);
            calculate_without_tax();
        }else{
            alert('El item ya esta agregado');
        }   
        $('#addCreateModal').css('overflow','scroll');     
    }
    function activarscroll(){
        $('#addCreateModal').css('overflow','scroll');  
    }
</script>