@extends('layouts.master')

@section('title', __('app.users'))

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
                        <a href="<?php URL::to('users'); ?>">@lang('app.adminstrator')</a>
                    </li>
                    <li class="active">@lang('app.list')</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        @lang('app.adminstrator')
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
                                        <h5 class="widget-title">@lang('app.all') @lang('app.adminstrator')</h5>

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
                                                @lang('app.add') @lang('app.new') @lang('app.adminstrator')</h4>
                                        </div>
                                        <div class="modal-body" id="winnew">
                                            <form class="form-horizontal" id="formadduser">
                                                <div class="form-group">
                                                    <label for="name"
                                                           class="col-sm-4 control-label">@lang('app.fullName')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="name" class="form-control" id="name"
                                                               maxlength="40">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="phone"
                                                           class="col-sm-4 control-label">@lang('app.telephone')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="phone" class="form-control" id="phone"
                                                               maxlength="25">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="email"
                                                           class="col-sm-4 control-label">@lang('app.email')</label>
                                                    <div class="col-sm-8">
                                                        <input type="email" name="email" class="form-control" id="email"
                                                               maxlength="60">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="username"
                                                           class="col-sm-4 control-label">@lang('app.nameOf') @lang('app.username')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="username" class="form-control"
                                                               id="username" maxlength="25">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="password"
                                                           class="col-sm-4 control-label">@lang('app.password')</label>
                                                    <div class="col-sm-8">
                                                        <input type="password" name="password" class="form-control"
                                                               id="password" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dir"
                                                           class="col-sm-4 control-label">@lang('app.confirm') @lang('app.password')</label>
                                                    <div class="col-sm-8">
                                                        <input type="password" name="password_confirmation"
                                                               class="form-control" id="password2" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status"
                                                           class="col-sm-4 control-label">@lang('app.enable') @lang('app.username')</label>
                                                    <div class="col-sm-8">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="status" value="1" id="status"
                                                                       type="checkbox" class="ace" checked/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="status"
                                                           class="col-sm-4 control-label">Cashdesk User</label>
                                                    <div class="col-sm-8">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="cashdesk" value="cs" id="cashdesk"
                                                                       type="checkbox" class="ace"/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <label for="perms" class="col-sm-4 control-label"
                                                       style="margin-bottom: 13px;">@lang('app.permissionsOf') @lang('app.username')</label>
                                                <div class="global">
                                                    <div class="mensajes">
                                                        <br>
                                                        <div class="form-group">

                                                            <div class="col-xs-8 col-sm-6">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="cli"
                                                                               type="checkbox" class="ace" checked/>
                                                                        <span class="lbl"> @lang('app.clients')</span>
                                                                    </label>
                                                                </div>

                                                                <div style="margin-left: 35px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.options')</strong>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="cliente_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="cliente_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>

                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.otherOptions')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="maps_client_access"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.clientMaps')</span>
                                                                        </label>
                                                                    </div>

                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.clientLocations')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="locations_access"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.clientLocations')</span>
                                                                        </label>
                                                                    </div>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="splitter"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> Caja (Splitter)</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="onu_cpu"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> ONUs/CPE</span>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="access_system"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.system')</span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="cliente_activar"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> Activar/desactivar</span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="pla"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.plans')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="rou"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.routers')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="use"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.users')</span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="pay"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.payments')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="tem"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.templates')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="report"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.reports')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="ticket"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.supportTickets')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="sms"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.posts') SMS</span>
                                                                    </label>
                                                                </div>

                                                                <br>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="facturacion"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.billing')</span>
                                                                    </label>
                                                                </div>

                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">Servicios</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_new" type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl">Nuevo</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_edit" type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_delete"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>

                                                                    {{-- <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="servicio_info" type="checkbox" class="ace" />
                                                                            <span class="lbl">Información</span>
                                                                        </label>
                                                                    </div> --}}

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_activate_desactivar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl">Activar/desactivar</span>
                                                                        </label>
                                                                    </div>

                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.transactions')</strong>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_facturacion_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_facturacion_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.bills')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="edit_client_balance"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.edit') @lang('app.balance')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_pagar" type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.payBill')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.payments')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="pagos_nuevo"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.add') @lang('app.payments')</span>
                                                                        </label>
                                                                    </div>


                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_editar" type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                {{-- finanzas										 --}}
                                                                <br>
                                                                <div class="checkbox" style="margin-left: -3px;">
                                                                    <label>
                                                                        <input name="user_acc[]" value="finanzas"
                                                                               type="checkbox" class="ace"/>
                                                                        <span class="lbl"> @lang('app.finance')</span>
                                                                    </label>
                                                                </div>

                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.transaction')</strong>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="estado_financier"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.estadoFinancier')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.bills')</strong>


                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_pagar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.payBill')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.payments')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.billingSettings')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="billing_setting_update"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.invoiceView')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label for="regpay" class="col-sm-3 control-label"><p
                                                                class="text-success"><i
                                                                    class="fa fa-files-o"></i> @lang('app.copy')</p>
                                                    </label>
                                                    <div class="col-sm-9">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="copy" type="checkbox" class="ace"
                                                                       id="copy"/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
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
                            <div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span
                                                        aria-hidden="true">&times;</span><span
                                                        class="sr-only">@lang('app.close')</span></button>
                                            <h4 class="modal-title" id="myModalLabel"><i
                                                        class="fa fa-pencil-square-o"></i> <span id="load"><i
                                                            class="fa fa-cog fa-spin"></i> @lang('app.loading')</span> @lang('app.edit') @lang('app.username')
                                            </h4>
                                        </div>
                                        <div class="modal-body" id="winedit">
                                            <form class="form-horizontal" id="UserformEdit">
                                                <div class="form-group">
                                                    <label for="edit_name"
                                                           class="col-sm-4 control-label">@lang('app.fullName')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="edit_name" class="form-control"
                                                               id="edit_name" maxlength="40">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_phone"
                                                           class="col-sm-4 control-label">@lang('app.telephone')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="edit_phone" class="form-control"
                                                               id="edit_phone" maxlength="25">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_email"
                                                           class="col-sm-4 control-label">@lang('app.email')</label>
                                                    <div class="col-sm-8">
                                                        <input type="email" name="edit_email" class="form-control"
                                                               id="edit_email" maxlength="60">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_username"
                                                           class="col-sm-4 control-label">@lang('app.nameOf') @lang('app.username')</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" name="edit_username" class="form-control"
                                                               id="edit_username" readonly>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="password"
                                                           class="col-sm-4 control-label">@lang('app.new') @lang('app.password')</label>
                                                    <div class="col-sm-8">
                                                        <input type="password" name="password" class="form-control"
                                                               id="password" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="dir"
                                                           class="col-sm-4 control-label">@lang('app.confirm') @lang('app.password')</label>
                                                    <div class="col-sm-8">
                                                        <input type="password" name="password_confirmation"
                                                               class="form-control" id="password2" maxlength="50">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_status"
                                                           class="col-sm-4 control-label">@lang('app.enable') @lang('app.username')</label>
                                                    <div class="col-sm-8">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="edit_status" id="edit_status" value="1"
                                                                       type="checkbox" class="ace" checked/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="status"
                                                           class="col-sm-4 control-label">Cashdesk User</label>
                                                    <div class="col-sm-8">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input name="edit_cashdesk" value="cs" id="edit_cashdesk"
                                                                       type="checkbox" class="ace"/>
                                                                <span class="lbl"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <label for="perms" class="col-sm-4 control-label"
                                                       style="margin-bottom: 13px;">'@lang('app.permissionsOf')
                                                    ' @lang('app.username')</label>
                                                <div class="global">
                                                    <div class="mensajes">
                                                        <br>

                                                        <div class="form-group">
                                                            <div class="col-xs-8 col-sm-6">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="cli"
                                                                               id="edit_acc_cli" type="checkbox"
                                                                               class="ace" checked/>
                                                                        <span class="lbl"> @lang('app.clients')</span>
                                                                    </label>

                                                                    <div style="margin-left: 35px;margin-top: 10px;">
                                                                        <strong style="font-size: 13px;margin-top: 10px">@lang('app.options')</strong>
                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="cliente_editar"
                                                                                       id="edit_cliente_editar"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> @lang('app.edit')</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="cliente_eliminar"
                                                                                       id="edit_cliente_eliminar"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> @lang('app.remove')</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="cliente_activar"
                                                                                       id="edit_cliente_activar"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> Activar/desactivar</span>
                                                                            </label>
                                                                        </div>

                                                                        <strong style="font-size: 13px;margin-top: 10px">@lang('app.otherOptions')</strong>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="maps_client_access"
                                                                                       id="edit_maps_client_access"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> @lang('app.clientMaps')</span>
                                                                            </label>
                                                                        </div>

                                                                        <strong style="font-size: 13px;margin-top: 10px">@lang('app.clientLocations')</strong>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="locations_access"
                                                                                       id="edit_locations_access"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> @lang('app.clientLocations')</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]"
                                                                                       value="splitter"
                                                                                       id="edit_splitter"
                                                                                       type="checkbox" class="ace"/>
                                                                                <span class="lbl"> Caja (Splitter)</span>
                                                                            </label>
                                                                        </div>

                                                                        <div class="checkbox">
                                                                            <label>
                                                                                <input name="user_acc[]" value="onu_cpe"
                                                                                       id="edit_onu_cpe" type="checkbox"
                                                                                       class="ace"/>
                                                                                <span class="lbl"> ONUs/CPE</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="access_system"
                                                                               id="edit_access_system" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.system')</span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="pla"
                                                                               id="edit_acc_pla" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.plans')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="rou"
                                                                               id="edit_acc_rou" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.routers')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="use"
                                                                               id="edit_acc_use" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.users')</span>
                                                                    </label>
                                                                </div>

                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="pay"
                                                                               id="edit_acc_pay" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.payments')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="tem"
                                                                               id="edit_acc_tem" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.templates')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="reports"
                                                                               id="edit_acc_reports" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.reports')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="ticket"
                                                                               id="edit_acc_ticket" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.supportTickets')</span>
                                                                    </label>
                                                                </div>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" value="sms"
                                                                               id="edit_acc_sms" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.posts') SMS</span>
                                                                    </label>
                                                                </div>


                                                                <br>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input name="user_acc[]" id="edit_facturacion"
                                                                               value="facturacion" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.billing')</span>
                                                                    </label>
                                                                </div>

                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">Servicios</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_new"
                                                                                   id="edit_servicio_new"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl">Nuevo</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_edit"
                                                                                   id="edit_servicio_edit"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="servicio_delete"
                                                                                   id="edit_servicio_delete"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>

                                                                    {{-- <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="servicio_info"
                                                                            id="edit_servicio_info"
                                                                            type="checkbox" class="ace" />
                                                                            <span class="lbl">Información</span>
                                                                        </label>
                                                                    </div> --}}

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   id="edit_servicio_activate_desactivar"
                                                                                   value="servicio_activate_desactivar"
                                                                                   type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl">Activar/desactivar</span>
                                                                        </label>
                                                                    </div>

                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.transactions')</strong>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_facturacion_editar"
                                                                                   id="edit_tran_facturacion_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_facturacion_eliminar"
                                                                                   id="edit_tran_facturacion_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.bills')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="edit_client_balance"
                                                                                   id="edit_client_balance"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.edit') @lang('app.balance')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_pagar"
                                                                                   id="edit_factura_pagar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.payBill')</span>
                                                                        </label>
                                                                    </div>


                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_editar"
                                                                                   id="edit_factura_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_eliminar"
                                                                                   id="edit_factura_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.payments')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]" value="pagos_nuevo"
                                                                                   id="edit_pagos_nuevo" type="checkbox"
                                                                                   class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.add') @lang('app.payments')</span>
                                                                        </label>
                                                                    </div>


                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_editar"
                                                                                   id="edit_pagos_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_eliminar"
                                                                                   id="edit_pagos_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                {{-- finanzas										 --}}
                                                                <br>
                                                                <div class="checkbox" style="margin-left: -3px;">
                                                                    <label>
                                                                        <input name="user_acc[]" value="finanzas"
                                                                               id="edit_finanzas" type="checkbox"
                                                                               class="ace"/>
                                                                        <span class="lbl"> @lang('app.finance')</span>
                                                                    </label>
                                                                </div>

                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.transaction')</strong>
                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_finanzas_editar"
                                                                                   id="edit_tran_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="tran_finanzas_eliminar"
                                                                                   id="edit_tran_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="estado_financier"
                                                                                   id="edit_estado_financier"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.estadoFinancier')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.bills')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_pagar"
                                                                                   id="edit_factura_finanzas_pagar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> 	@lang('app.payBill')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_editar"
                                                                                   id="edit_factura_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="factura_finanzas_eliminar"
                                                                                   id="edit_factura_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>


                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.payments')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_finanzas_editar"
                                                                                   id="edit_pagos_finanzas_editar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.edit')</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="pagos_finanzas_eliminar"
                                                                                   id="edit_pagos_finanzas_eliminar"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.remove')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div style="margin-left: 45px;margin-top: 10px;">
                                                                    <strong style="font-size: 13px;margin-top: 10px">@lang('app.billingSettings')</strong>

                                                                    <div class="checkbox">
                                                                        <label>
                                                                            <input name="user_acc[]"
                                                                                   value="billing_setting_update"
                                                                                   id="billing_setting_update"
                                                                                   type="checkbox" class="ace"/>
                                                                            <span class="lbl"> @lang('app.invoiceView')</span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                    </div>

                                                </div>


                                                <input type="hidden" name="user_id">
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">@lang('app.close')</button>
                                            <button type="button" class="btn btn-primary" id="editbtnuser"
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
    <script src="{{asset('assets/js/rocket/users-core.js')}}"></script>
    {!! $dataTable->scripts() !!}
@endsection
