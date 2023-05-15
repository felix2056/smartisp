@extends('layouts.master')

@section('title', __('app.credits'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <style type="text/css">
        .global {
            height: 300px;
            width: 88%;
            border: 1px solid #ddd;
            background: #ffffff;
            overflow-y: scroll;
            margin-left: 72px;
            padding-left: 48px;
            overflow-x: hidden;
        }

        .mensajes {
            height: auto;
        }

        .texto {
            padding: 4px;
            background: #fff;
        }
    </style>
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
                        <a href="<?php URL::to('users'); ?>">@lang('app.credits')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.credits')
                        <small>
                            <i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.list')
                        </small>
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#add"><i
                                    class="icon-plus"></i> @lang('app.new')</button>
                    </h1>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 widget-container-col">
                                <div class="widget-box widget-color-blue2">
                                    <div class="widget-header">
                                        <h5 class="widget-title">@lang('app.all') @lang('app.credits')</h5>

                                        <div class="widget-toolbar">
                                            <div class="widget-menu">
                                                <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                                    <i class="ace-icon fa fa-bars"></i>
                                                </a>

                                                <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                                    <li>
                                                        <a href="#" data-toggle="modal" class="peref"
                                                           data-target="#add"><i
                                                                    class="fa fa-plus-circle"></i> @lang('app.add') @lang('app.adminstrator')
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <a href="#" data-action="fullscreen" class="white">
                                                <i class="ace-icon fa fa-expand"></i>
                                            </a>
                                            <a href="#" data-action="reload" class="recargar white">
                                                <i class="ace-icon fa fa-refresh"></i>
                                            </a>
                                            <a href="#" data-action="collapse" class="white">
                                                <i class="ace-icon fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="widget-body">
                                        <div class="widget-main">
                                            <!--Contenido widget-->
                                            <div class="table-responsive">
                                                {!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable', 'width' => '100%']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                        aria-hidden="true">&times;</span><span
                                                        class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i class="fa fa-user-plus"></i>
                                                @lang('app.add') @lang('app.new') @lang('app.credits')</h4>
                                        </div>
                                        <div class="modal-body" id="winnew">
                                            <form class="form-horizontal" id="formadduser">
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <div class="form-group">
                                                    <label for="name"
                                                           class="col-sm-4 control-label">Balance</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" name="credit" class="form-control" id="credit"
                                                               maxlength="40">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone"
                                                           class="col-sm-4 control-label">@lang('app.comments')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="comment" class="form-control" id="comment" maxlength="60" min="0">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">@lang('app.close')</button>
                                            <button type="button" class="btn btn-primary" id="addbtnuser"
                                                    data-loading-text="@lang('app.saving')..." autocomplete="off"><i
                                                        class="fa fa-floppy-o"></i>
                                                @lang('app.save')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('layouts.modals')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <input id="val" type="hidden" name="user" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
{{--    <script src="{{asset('assets/js/rocket/users-credits-core.js')}}"></script>--}}
    {!! $dataTable->scripts() !!}

    <script>
        $('#add').on('hidden.bs.modal', function () {
            $('#add form')[0].reset();
            $(".has-error").find(".help-block").remove();
            $(".has-error").removeClass("has-error");
        });

        //agregar usuario
        $(document).on('click','#addbtnuser',function(){
            var data = $('#formadduser').serialize();
            $.easyAjax({
                type: 'POST',
                url: "{{ route('user-credits-create') }}",
                data: data,
                container: "#formadduser",
                success: function(data) {
                    if(data.status == 'success'){
                        $('#add').modal('toggle');
                        window.LaravelDataTables["user-credit-table"].draw();
                    }
                }
            });

        });

        $(document).on("click", '.del', function (event) {
            var idp = $(this).attr ("id");
            bootbox.confirm('are you sure ?', function(result) {
                if(result) {
                    $.ajax ({
                        type: "POST",
                        url: "{{ route('user-credit-delete') }}",
                        data: { "id" : idp },
                        dataType: "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr,thrownError);
                        }
                    }).done(function(data){
                        window.LaravelDataTables["user-credit-table"].draw();

                        if(data.msg=='notfound')
                            msg('No se encontro al usuario en la BD.', 'error');
                        if(data.msg=='success')
                            msg('El usuario fue eliminado.', 'success');
                    });
                }
            });
        });
    </script>
@endsection
