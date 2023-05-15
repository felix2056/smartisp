@extends('layouts.master')

@section('title',__('app.visualEditor'))

@section('styles')
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/js/plupload/js/themes/flick/jquery-ui.min.css') }}" media="screen"/>
    <link rel="stylesheet" href="{{ asset('assets/js/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css') }}"/>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-desktop desktop-icon"></i>
                        <a href="<?php echo URL::to('admin'); ?>">@lang('app.desk')</a>
                    </li>
                    <li>
                        <a href="<?php echo URL::to('templates'); ?>">@lang('app.templates')</a>
                    </li>
                    <li class="active">@lang('app.visualEditor')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.visualEditor')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.templates')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <!--head tab-->
                        <ul class="nav nav-tabs padding-18 tab-size-bigger" role="tablist" id="myTab">
                            <li role="presentation" class="active"><a href="#inputs" aria-controls="inputs" role="tab"
                                                                      data-toggle="tab">
                                    <i class="green ace-icon fa fa-newspaper-o bigger-120"></i>
                                    @lang('app.templateEditor')</a></li>
                        </ul>
                        <!--head endtab-->
                        <!--tab content-->
                        <div class="tab-content">
                            <!--inicio tab editor visual-->
                            <form>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">@lang('app.template')</label>
                                    <select class="form-control" id="type_temp">
                                    </select>
                                </div>
                                <div class="form-group" id="Stem">
                                    <label for="name">@lang('app.name')</label>
                                    <input type="text" class="form-control" id="nameTeme">
                                </div>
                                <button type="button" id="addtemplate" class="btn btn-primary"
                                        data-loading-text="Guardando..."><i class="fa fa-floppy-o"></i>
                                    @lang('app.saveTemplate')</button>
                            </form>
                            <br>
                            <!--inicio de widget editor visual-->
                            <div class="widget-box widget-color-blue2">
                                <div class="widget-header">
                                    <h5 class="widget-title">@lang('app.templateEditor')</h5>
                                    <div class="widget-toolbar">
                                        <a data-action="collapse" href="#"><i class="ace-icon fa fa-chevron-up"></i></a>
                                    </div>
                                </div>
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <form id="teplatecontent" action="" method="get">
                                            <textarea id="myadvice" name="content"></textarea>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!--fin del widgeteditor visual-->
                            <!--en tab editor visual-->
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="addimg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">@lang('app.close')</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-image-o"></i>
                                    @lang('app.uploadImages')</h4>
                            </div>
                            <div class="modal-body" id="winnew">
                                <div id="uploader">
                                    <p>@lang('app.yourBrowserDoesnotHaveFlashSilverlightOrHTML5Support')</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">@lang('app.close')</button>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.modals')
            </div>
        </div>
    </div>
    <input id="val" type="hidden" name="register" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('assets/js/plupload/js/jquery-ui.min.js')}}" charset="UTF-8"></script>
    <script src="{{asset('assets/js/plupload/js/plupload.full.min.js')}}"></script>
    <script src="{{asset('assets/js/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js')}}"></script>
    <script src="{{asset('assets/js/plupload/js/i18n/es.js')}}"></script>
    <script src="{{asset('assets/js/rocket/visualEditor-core.js')}}"></script>

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
                            // ieSelectionBookmark = editor.selection.getBookmark;
                        }
                    });
                }
            });

        });
        function addInEditor(value) {
            if(typeof tinymce.activeEditor != "undefined") {
                tinymce.activeEditor.execCommand("mceInsertContent",false,'<b>'+value+'</b> ');
            }
        }
    </script>
@endsection
