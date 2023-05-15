<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Edit Document</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="add_edit_document" class="form-horizontal" role="form" method="POST">
                <input type="hidden" name="_method" value="put">
                @csrf
                <fieldset>

                    <div class="form-group">
                        <label for="invoice_bill_num" class="control-label col-lg-3 col-md-3">
                            @lang('app.title')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <input
                                    id="title"
                                    class="form-control input-sm datepicker"
                                    type="text"
                                    autocomplete="false"
                                    value="{{ $document->title }}"
                                    name="title">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                            @lang('app.description')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <textarea id="description" class="form-control" rows="4" name="description">{{ $document->description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="preadv"
                               class="col-sm-3 control-label">@lang('app.visibleToCustomer')</label>
                        <div class="col-sm-9">
                            <label><input id="visible_to_client"
                                          name="visible_to_client"
                                          class="ace ace-switch ace-switch-6"
                                          value="1"
                                          type="checkbox" @if($document->visible_to_client == 1) checked @endif/>
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject"
                               class="col-sm-3 control-label">@lang('app.file')</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" name="file" id="file">
                        </div>
                    </div>
                    <input type="hidden" name="client_id" value="{{ $clientId }}">

                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="add_document" type="button" class="btn btn-primary" onclick="addUpdateDocument('{{ $document->id }}'); return false;">Add</button>
</div>

<script>
    $('#file').ace_file_input({
        no_file:Lang.app.SelectFile+' ...',
        btn_choose:Lang.app.select,
        btn_change:Lang.app.change,
        droppable:false,
        onchange:null,
        thumbnail:false,
        whitelist:'gif|png|jpg|jpeg|pdf|txt',
        blacklist:'exe|php'
    });
</script>
