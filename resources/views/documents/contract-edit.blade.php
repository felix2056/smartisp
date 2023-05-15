<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">Edit Contract</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <form id="add_edit_document" class="form-horizontal" role="form" method="POST">
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
                                name="title"
                                value="{{ $document->title }}"
                            >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                            @lang('app.description')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <textarea id="description" class="form-control" type="text" rows="4" name="description">{{ $document->description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="invoice_memo" class="control-label col-lg-3 col-md-3">
                            @lang('app.template')
                        </label>

                        <div class="col-lg-9 col-md-9">
                            <select class="form-control" name="template" id="templates">
                                @foreach($templates as $template)
                                    <option value="none">Choose Template</option>
                                    <option value="{{ $template->name }}" @if($document->template_id == $template->name) selected @endif >{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="preadv"
                               class="col-sm-3 control-label">@lang('app.visibleToCustomer')</label>
                        <div class="col-sm-9">
                            <label><input id="visible_to_client"
                                          name="visible_to_client"
                                          class="ace ace-switch ace-switch-6"
                                          type="checkbox"
                                          value="1"
                                />
                                <span class="lbl"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="preadv"
                               class="col-sm-3 control-label">Preview</label>
                        <div class="col-sm-9">
                            <textarea id="myadvice" name="content">{!! $document->contract_content !!}</textarea>
                        </div>
                    </div>
                    {{--<div class="form-group">--}}
                        {{--<label for="subject"--}}
                               {{--class="col-sm-3 control-label">@lang('app.file')</label>--}}
                        {{--<div class="col-sm-9">--}}
                            {{--<input type="file" class="form-control" name="file" id="file">--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    <input type="hidden" id="contractClientID" name="client_id" value="{{ $clientId }}">

                </fieldset>
            </form>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button id="add_document" type="button" class="btn btn-primary" onclick="addUpdateContract('{{ $document->id }}'); return false;">Update</button>
</div>
<script src="{{asset('assets/js/tinymce/tinymce.min.js')}}"></script>

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
<script type="text/javascript">
    $(document).ready(function (e) {
        tinymce.init({
            selector: "#myadvice",
            inline_styles: true,
            imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
            plugins: "colorpicker bootstrap advlist lists link image anchor searchreplace fullscreen insertdatetime code media table paste textcolor imagetools",
            bootstrapConfig: {
                'imagesPath': '<?php echo URL::to("assets/imgeditor/imagenes"); ?>' // replace with your images folder path
            },
            toolbar1: "bootstrap",
            toolbar2: "insertfile undo redo | styleselect | bold italic | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor | link image | hr | imagetools | inserttable | searchreplace | fullscreen",
            schema: "html5",
            valid_children: "+a[div]",
            language: locale,
            menu : {
                file   : {title : 'File'  , items : 'newdocument'},
                edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'},
                insert : {title : 'Insert', items : 'link media | template hr'},
                view   : {title : 'View'  , items : 'visualaid'},
                format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
                table  : {title : 'Table' , items : 'inserttable deletetable | cell row column'},
                tools  : {title : 'Tools' , items : 'spellchecker code'},
                placeholder: {title : 'Placeholder', items : 'placeholder'}
            },
            menubar: 'file edit insert view format table tools placeholder',
            setup: function(editor) {
                editor.addMenuItem('placeholder', {
                    text: 'Add Placeholder',
                    context: 'placeholder',
                    onclick: function () {
                        $('#addEditPlaceholderModal').modal('show');
                    }
                });
            }
        });

        {{--setTimeout(() => {--}}
            {{--var html = '{{ $document->contract_content }}';--}}
            {{--tinymce.activeEditor.setContent(html, {format : 'text'});--}}
        {{--}, 300);--}}

        // $('#templates').trigger('change');
    });


    //Mostrar campo nombre si se selecciona nueva plantilla
    $(document).on("change","#templates",function(event){
        event.preventDefault();
        event.stopImmediatePropagation();
        var op = $('#templates').val();
        if(op=='new'){
            tinyMCE.activeEditor.setContent('');
        }
        else if(op == 'none'){
            msg('Please choose a template from list', 'error');
        }
        else{

            //recupermos el template elegido
            $.ajax({
                type: "POST",
                url: "{{ route('templates.seteme') }}",
                data: { "name" : op },
                dataType: "html",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr,thrownError);
                }
            }).done(function(data){

                $.ajax({
                    type: "POST",
                    url:"{{ route('templates.setype') }}",
                    data:{"name":op},
                    dataType: "json"
                }).done(function(datos){
                    console.log(data, "Hello from data");
                    tinymce.activeEditor.setContent(data);

                });

            });

            //fin de recuperar
        }

    });

    function addInEditor(value) {
        if(typeof tinymce.activeEditor != "undefined") {
            tinymce.activeEditor.execCommand("mceInsertContent",false,'<b>'+value+'</b> ');
        }
    }

    $('#addEditPlaceholderModal').on('hidden.bs.modal', function () {
        $('body').addClass('modal-open');
    });

</script>
