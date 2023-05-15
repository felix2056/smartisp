@extends('layouts.master')

@section('title', 'Perfiles')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
    <style type="text/css" media="screen">
        .negro_c {
            color: #000 !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}"/>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                @include('includes.zone-tabs')
            <!--start row-->
                <div class="row">
                    <div class="col-sm-12">
                        <!--Inicio de tab simple queues-->
                        <button class="btn btn-success" data-toggle="modal" data-target="#add"><i
                                    class="icon-plus"></i> {{ __('app.add') }} Zona
                        </button>
                        <br>
                        <br>
                        <br>

                        <!--Inicio tabla planes simple queues-->
                        <div class="widget-box widget-color-blue2">
                            <div class="widget-header">
                                <h5 class="widget-title">Todas las Zonas</h5>
                                <div class="widget-toolbar">
                                    <div class="widget-menu">
                                        <a href="#" data-action="settings" data-toggle="dropdown" class="white">
                                            <i class="ace-icon fa fa-bars"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
                                            <li>
                                                <a href="#" data-toggle="modal" class="peref" data-target="#add"><i
                                                            class="fa fa-plus-circle"></i> {{ __('app.new') }} Zona</a>
                                            </li>

                                        </ul>
                                    </div>
                                    <a data-action="reload" href="#" class="recargar white"><i
                                                class="ace-icon fa fa-refresh"></i></a>
                                    <a data-action="fullscreen" class="white" href="#"><i
                                                class="ace-icon fa fa-expand"></i></a>
                                    <a data-action="collapse" href="#" class="white"><i
                                                class="ace-icon fa fa-chevron-up"></i></a>
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
                        <!--Fin tabla planes simple queues-->
                    </div><!--end col-->
                </div>
                <!--end row-->

                <!---------------------Inicio de Modals------------------------------->

                <!--Incio modal añadir plan-->
                <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-tachometer"></i>
                                    {{ __('app.add') }} Nueva Zona</h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-horizontal" role="form" id="formaddplan">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">{{ __('app.name') }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" id="namepl"
                                                   maxlength="30">
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}</button>
                                <button type="button" class="btn btn-primary" id="addbtnplan"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Fin de modal añadir plan-->

                <!--Inicio de modal editar plan -->
                <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                                            class="sr-only">{{ __('app.close') }}</span></button>
                                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span
                                            id="load2"><i class="fa fa-cog fa-spin"></i> @lang('app.loading')</span>
                                    Editar Zone</h4>
                            </div>
                            <div class="modal-body" id="winedit">
                                <form class="form-horizontal" role="form" id="PlanformEdit">
                                    <div class="form-group">
                                        <label for="name" class="col-sm-2 control-label">{{ __('app.name') }}</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="edit_name" class="form-control" id="edit_name"
                                                   maxlength="30">
                                            <input type="hidden" name="plan_id">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">{{ __('app.close') }}</button>
                                <button type="button" class="btn btn-primary" id="editbtnplan"
                                        data-loading-text="@lang('app.saving')..."><i class="fa fa-floppy-o"></i>
                                    {{ __('app.save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.modals')
            </div>
        </div>
    </div>
    <input id="val" type="hidden" name="plan" value="">
@endsection

@section('scripts')
    <script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
    <script src="{{asset('assets/js/bootbox.min.js')}}"></script>
    <script src="{{asset('assets/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
    {!! $dataTable->scripts() !!}
    <script type="text/javascript">
        function msg_alert(msg, type) {
            if (type == 'success') {
                var clase = 'gritter-success';
                var tit = '{{__('app.registered')}}';
                var img = '/assets/img/ok.png';
                var stincky = false;
            }
            if (type == 'error') {
                var clase = 'gritter-error';
                var tit = '{{ __('app.error') }}';
                var img = '/assets/img/error.png';
                var stincky = false;
            }
            if (type == 'debug') {
                var clase = 'gritter-error gritter-center';
                var tit = '{{__('app.internalError')}} (Debug - mode)';
                var img = '';
                var stincky = false;
            }
            if (type == 'info') {
                var clase = 'gritter-info';
                var tit = '{{ __('app.information') }}';
                var img = '/assets/img/info.png';
                var stincky = false;
            }
            if (type == 'mkerror') {
                var clase = 'gritter-error';
                var tit = '{{ __('app.errorFromMikrotik') }}';
                var img = '';
                var stincky = false;
            }

            if (type == 'system') {
                var clase = 'gritter-light gritter-center';
                var tit = '{{ __('app.systemInformation') }}';
                var img = '';
                var stincky = false;
            }

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: tit,
                // (string | mandatory) the text inside the notification
                text: msg,
                image: img, //in Ace demo dist will be replaced by correct assets path
                sticky: stincky,
                class_name: clase
            });
        }
    </script>

    <script>

        //añadir plan
        $(document).on("click", "#addbtnplan", function (event) {
            event.stopImmediatePropagation();
            var routerdata = $('#formaddplan').serialize();

            $.easyAjax({
                type: "POST",
                url: "{{ route('profiles.zone.create') }}",
                data: routerdata,
                container: '#formaddplan',
                success: function(data) {
                    if (data.msg == 'errorDownload')
                        msg_alert('EL campo descarga no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');
                    if (data.msg == 'errorUpload')
                        msg_alert('EL campo subida no es válido, la velocidad debe estar en kilobytes y contener al final la letra "k" o "M" para (megabytes) ejemplos: 512k, 1000k, 3M', 'error');

                    if (data.msg == 'success') {
                        $('#add').modal('toggle');
                        $('#formaddplan')[0].reset();//reseteamos el formulario
                        msg_alert('La zona fue añadido correctamente.', 'success');
                        window.LaravelDataTables["zone-table"].draw();
                    }
                }
            })
        });

        //get editar plan
        $(document).on("click", '.editar', function (event) {
            event.stopImmediatePropagation();
            $('[name=plan]').val($(this).attr('id'));
            $('#winedit').waiting({fixed: true});
            var fdata = $('#val').serialize();
            $('#load2').show();

            $('#PlanformEdit').find(".has-error").each(function () {
                $(this).find(".help-block").text("");
                $(this).removeClass("has-error");
            });

            $.ajax({
                type: "POST",
                url: "{{ route('profiles.getzone.data') }}",
                data: fdata,
                dataType: "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {
                if (data.success) {

                    var myText = data.priority;

                    $("#editpriority").children().filter(function () {
                        return $(this).val() == myText;
                    }).prop('selected', true);

                    $('#PlanformEdit input[name="plan_id"]').val(data.id);
                    $('#PlanformEdit input[name="edit_name"]').val(data.name);

                    $('#winedit').waiting('done');
                    $('#load2').hide();
                } else {
                    $('#load2').hide();
                    msg_alert('No pudo cargar la información de la base de datos', 'error');
                }
            });

        }); // fin de editar


        //eliminar plan
        $(document).on("click", '.del', function (event) {
            event.stopImmediatePropagation();
            var idp = $(this).attr("id");
            bootbox.confirm("¿ Esta seguro de eliminar esta zona ?", function (result) {
                if (result) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('profiles.zone.delete') }}",
                        data: {"id": idp},
                        dataType: "json",
                        'error': function (xhr, ajaxOptions, thrownError) {
                            debug(xhr, thrownError);
                        }
                    }).done(function (data) {
                        if (data.msg == 'errorclient')
                            msg_alert('No se puede eliminar el plan, existen clientes asociados.', 'error');
                        if (data.msg == 'error')
                            msg_alert('No se encontro el plan.', 'error');
                        if (data.msg == 'success') {
                            msg_alert('La zona fue eliminado.', 'success');
                            window.LaravelDataTables["zone-table"].draw();
                        }
                    });
                }
            });
        });

        function startloading(selector, text) {

            $(selector).loadingModal({
                position: 'auto',
                text: text,
                color: '#fff',
                opacity: '0.7',
                backgroundColor: 'rgb(0,0,0)',
                animation: 'spinner'
            });
        }

        // guardar editar plan
        $(document).on("click", "#editbtnplan", function (event) {
            event.stopImmediatePropagation();
            var plandata = $('#PlanformEdit').serialize();
            $.easyAjax({
                type: "POST",
                url: "{{ route('profiles.zone.update') }}",
                data: plandata,
                container: '#PlanformEdit',
                success: function(data) {
                    if (data.msg == 'errorConnect') {
                        $('body').loadingModal('destroy');
                        $('#edit').modal('toggle');
                        msg_alert('Se produjo un error no se tiene acceso al router, verifique los datos de autentificación, además si esta encendido y conectado a la red.', 'error');
                    }

                    if (data.msg == 'errorDownload') {
                        $('body').loadingModal('destroy');
                        msg_alert('EL campo descarga no es válido, la velocidad debe estar en kilobytes ejemplos: 512, 1000', 'error');
                    }
                    if (data.msg == 'errorUpload') {
                        $('body').loadingModal('destroy');
                        msg_alert('EL campo subida no es válido, la velocidad debe estar en Kilobytes ejemplos: 512, 1000', 'error');
                    }

                    if (data.msg == 'success') {
                        $('body').loadingModal('destroy');
                        msg_alert('La zona fue actualizado correctamente.', 'info');
                        $('#edit').modal('toggle');
                        window.LaravelDataTables["zone-table"].draw();
                    }
                }
            })
        });
        //fin guardar editar plan

        ///// funcion de depuracion
        function debug(xhr, thrownError) {
            $.ajax({
                "url": "config/getconfig/debug",
                "type": "GET",
                "data": {},
                "dataType": "json"
            }).done(function (deb) {

                if (deb.debug == '1') {
                    msg_alert('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
                } else
                    alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
            });
        }


    </script>
@endsection
