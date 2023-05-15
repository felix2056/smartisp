@extends('layouts.master')

@section('title',__('app.htmlEditor'))

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/js/codemirror/codemirror.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/codemirror/addon/dialog/dialog.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/js/codemirror/addon/search/matchesonscrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/codemirror.css') }}">
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
                    <li class="active">@lang('app.htmlEditor')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.htmlEditor')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.templates')
                        </small>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-warning">
                            Whatsapp template guidelines:
                            
                            <ul>
                                <li>Message templates in an Aproved state can be edited up to 10 times in a 30 day window, or 1 time in a 24 hour window.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <!--head tab-->
                        <ul class="nav nav-tabs padding-18 tab-size-bigger" role="tablist" id="myTab">
                            <li role="presentation" class="active"><a href="#inputs" aria-controls="inputs"
                                                                      role="tab" data-toggle="tab">
                                    <i class="green ace-icon fa fa-newspaper-o bigger-120"></i>
                                    @lang('app.templateEditor')</a></li>
                        </ul>
                        <!--head endtab-->
                        <!--tab content-->
                        <div class="tab-content">
                            <!--tab table list templates-->
                            <div role="tabpanel" class="tab-pane active" id="inputs">
                                <!--inicio tabla ingresos-->
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
                                    <div class="form-group" id="typeTemplate">
                                        <label for="typeTemplate">@lang('app.templateType')</label>
                                        <select class="form-control" id="tp">
                                            <option value="email">@lang('app.email')</option>
                                            <option value="invoice" selected>@lang('app.bill')</option>
                                            <option value="screen">@lang('app.notice')</option>
                                            <option value="sms">@lang('app.sms')</option>
                                            <option value="contract">@lang('app.contract')</option>
                                            <option value="whatsapp">@lang('app.whatsapp')</option>
                                        </select>
                                    </div>
                                    <button type="button" id="addtemplate" class="btn btn-primary"
                                            data-loading-text="@lang('app.saving')..."><i
                                            class="fa fa-floppy-o"></i>
                                        @lang('app.saveTemplate')</button>
                                </form>
                                <br>
                                <!--inicio de widget editor visual-->
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.codeEditor')</h5>
                                        <div class="widget-toolbar">
                                            <a data-action="collapse" href="#"><i
                                                    class="ace-icon fa fa-chevron-up"></i></a>
                                            <a href="#" data-action="fullscreen" class="white">
                                                <i class="ace-icon fa fa-expand"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <textarea id="conthtml" name="content"></textarea>
                                            <br>
                                            <span class="label label-primary">@lang('app.preview')</span>
                                            <iframe id="preview"></iframe>
                                        </div>
                                    </div>
                                </div>
                                <!--fin del widgeteditor visual-->

                                <!--Fin tabla templates-->
                            </div>
                            <!--end tab table list templates-->
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
    <script src="{{asset('assets/js/codemirror/codemirror.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/addon/edit/closetag.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/mode/xml/xml.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/addon/dialog/dialog.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/addon/search/searchcursor.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/addon/search/search.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/mode/javascript/javascript.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/mode/css/css.js')}}"></script>
    <script src="{{asset('assets/js/codemirror/mode/htmlmixed/htmlmixed.js')}}"></script>
    <script src="{{asset('assets/js/rocket/htmlEditor-core.js')}}"></script>
    <script>
        var delay;

        var editor = CodeMirror.fromTextArea(document.getElementById("conthtml"),{
            lineNumbers: true,
            autoCloseTags: true,
            mode: 'text/html'
        });

        editor.on("change", function() {
            clearTimeout(delay);
            delay = setTimeout(updatePreview, 300);
        });
        function updatePreview() {
            var previewFrame = document.getElementById('preview');
            var preview =  previewFrame.contentDocument ||  previewFrame.contentWindow.document;
            preview.open();
            preview.write(editor.getValue());
            preview.close();
        }
        setTimeout(updatePreview, 300);


    </script>
@endsection
