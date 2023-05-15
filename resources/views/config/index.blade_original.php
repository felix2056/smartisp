@extends('layouts.master')

@section('title',__('app.configuration'))

@section('styles')
@parent
<link rel="stylesheet" href="assets/css/select2.min.css" />
<link rel="stylesheet" href="assets/css/bootstrap-timepicker.min.css" />
<style>
	.pac-container {
		z-index: 99999;
	}
</style>
@stop
@section('content')
<div id="navbar" class="navbar navbar-default">
	<div class="navbar-container" id="navbar-container">
		<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
			<span class="sr-only">Toggle sidebar</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		@include('layouts.sidebartopleft')
		<!-- navbarheader right -->
		@include('layouts.navbartopright')
		<!-- navbarheader right -->
	</div>
</div>
<div class="main-container" id="main-container">
	<!-- sidebar left menu -->
	@include('layouts.sidebarmenu')
	<!-- sidebar left menu -->
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-home home-icon"></i>
						<a href="{{URL::to('admin')}}">@lang('app.desk')</a>
					</li>
					<li>
						<a href="#">@lang('app.system')</a>
					</li>
					<li class="active">@lang('app.configuration')</li>
				</ul>
			</div>

			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.configuration')
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
                            @lang('app.configureImportantAspects')
						</small>
					</h1>
				</div>
				<div class="row">
					<div class="col-xs-12">
                        <div class="tabbable">
                                <ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
                                    <li class="active">
                                        <a data-toggle="tab" href="#faq-tab-1">
                                            <i class="blue ace-icon icon-briefcase bigger-120"></i>
                                            General
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-2">
                                            <i class="menu-icon ace-icon la la-cog bigger-120"></i>
                                            {{-- <i class="green ace-icon fa fa-rocket bigger-120"></i> --}}
                                            @lang('app.system')
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-4">
                                            <i class="ace-icon la la-user bigger-120"></i>
                                            @lang('app.clientPortal')
                                        </a>
                                    </li>

                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-5" id="lsapis">
                                            <i class="ace-icon icon-feed bigger-120"></i>
                                            APIS
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-6" id="tabsms">
                                            <i class="ace-icon la la-commenting bigger-120"></i>
                                            SMS
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-7" id="tabpayment">
                                            <i class="ace-icon la la-money bigger-120"></i>
                                            @lang('app.payment')
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-9" id="tabfacturaiconelectronica">
                                            <i class="blue ace-icon fa fa-rocket bigger-120"></i>
                                            @lang('app.transmitter')
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-8" id="tabfacturaiconelectronica">
                                            <i class="green ace-icon fa fa-rocket bigger-120"></i>
                                            @lang('app.electronicBilling')
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#faq-tab-3" id="tablanguagessettings">
                                            <i class="green ace-icon fa fa-language bigger-120"></i>
                                            @lang('app.languageSettings')
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content no-border padding-24">
                                    <div id="faq-tab-1" class="tab-pane fade in active">

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button class="btn btn-success" type="button" id="savebtnGeneral">
                                                        <i class="icon-plus"></i> @lang('app.save')
                                                    </button>
                                                </div>
                                            </div>

                                            <h4 class="blue">
                                                <i class="ace-icon fa fa-check bigger-110"></i>
                                                General
                                            </h4>
                                            <div class="space-8"></div>

                                            <div id="faq-list-1" class="panel-group accordion-style1 accordion-style2">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-1" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-building-o bigger-130"></i>
                                                            &nbsp; @lang('app.companyOrganization')
                                                        </a>
                                                    </div>

                                                    <div class="panel-collapse collapse" id="faq-1-1">
                                                        <div class="panel-body">

                                                            <form class="form-inline">
                                                                <div class="form-group">
                                                                    <label class="sr-only" for="exampleInputEmail3">@lang('app.company')</label>
                                                                    <input type="text" class="form-control" id="name" maxlength="50" >
                                                                </div>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-2" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                            <i class="ace-icon fa fa-usd"></i>
                                                            &nbsp; @lang('app.currency')
                                                        </a>
                                                    </div>

                                                    <div class="panel-collapse collapse" id="faq-1-2">
                                                        <div class="panel-body">
                                                            <form class="form-inline">
                                                                <div class="form-group">
                                                                    <label class="sr-only" for="exampleInputEmail3">@lang('app.symbol')</label>
                                                                    <input type="text" class="form-control" id="smoney" placeholder="Símbolo" maxlength="12">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="sr-only" for="exampleInputPassword3">@lang('app.currency')</label>
                                                                    <input type="text" class="form-control" id="money" placeholder="@lang('app.currency')" maxlength="12">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-3" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-sort-numeric-asc"></i>
                                                            &nbsp;@lang('app.InvoiceNumbering')
                                                        </a>
                                                    </div>

                                                    <div class="panel-collapse collapse" id="faq-1-3">
                                                        <div class="panel-body">
                                                            <form>
                                                                <div class="form-group">
                                                                    <label for="exampleInputEmail3">@lang('app.billNumber')</label>
                                                                    <input type="number" class="form-control" id="nbill" maxlength="4">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="exampleInputEmail3">@lang('app.invoiceTemplate')</label>
                                                                    <select class="form-control" name="invoice_template_id" id="invoice_template_id">
                                                                        @foreach($facturaTemplates as $template)
                                                                            <option value="{{ $template->id }}" @if($template->id == $global->invoice_template_id) selected @endif>{{ $template->name  }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-6" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-calendar-o"></i>
                                                            &nbsp; @lang('app.autoCutTolerance')
                                                        </a>
                                                    </div>

                                                    <div class="panel-collapse collapse" id="faq-1-6">
                                                        <div class="panel-body">
                                                            <form>
                                                                <div class="form-group">
                                                                    <label for="tolerance">@lang('app.daysAfterExpiration')</label>
                                                                    <input type="number" min="0" value="0" name="tolerance" class="form-control" id="tolerance" maxlength="2">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-5" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-bell-o bigger-130"></i>
                                                            &nbsp; @lang('app.clientNotifications')
                                                        </a>
                                                    </div>
                                                    <div class="panel-collapse collapse" id="faq-1-5">
                                                        <div class="panel-body">


                                                            <form>
                                                                <div class="form-group">
                                                                    <label for="inputdays"> @lang('app.notifyDaysBefore')</label>
                                                                    <input type="number" name="daysnotify" class="form-control" id="inputdays" placeholder="@lang('app.enterNumberOfDays')">
                                                                </div>
                                                                <!-- time Picker -->
                                                                <div class="bootstrap-timepicker">
                                                                    <div class="form-group">
                                                                        <label>@lang('app.shippingTime')</label>

                                                                        <div class="input-group">
                                                                            <input type="text" name="timeemail" id="hrsemail" readonly class="form-control timepicker">

                                                                            <div class="input-group-addon">
                                                                                <i class="fa fa-clock-o"></i>
                                                                            </div>
                                                                        </div>
                                                                        <!-- /.input group -->
                                                                    </div>
                                                                    <!-- /.form group -->
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="preadv" class="col-sm-9 control-label">@lang('app.sendEmailBeforeCourt')</label>
                                                                    <div class="col-sm-6">
                                                                        <label><input id="preadv" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="presms" class="col-sm-9 control-label">@lang('app.sendSMSPriorNotice')</label>
                                                                    <div class="col-sm-6">
                                                                        <label><input id="presms" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
																
																<div class="form-group">
                                                                    <label for="prewhatsapp" class="col-sm-9 control-label">Enviar whatsapp sms pre aviso de Corte</label>
                                                                    <div class="col-sm-6">
                                                                        <label>
																		<input id="prewhatsapp" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-7" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-database bigger-130"></i>
                                                            &nbsp; @lang('app.backups')
                                                        </a>
                                                    </div>
                                                    <div class="panel-collapse collapse" id="faq-1-7">
                                                        <div class="panel-body">



                                                            <!-- time Picker -->
                                                            <div class="bootstrap-timepicker">
                                                                <div class="form-group">
                                                                    <label>@lang('app.dailyCreationTime')</label>

                                                                    <div class="input-group">
                                                                        <input type="text" name="timebackup" id="hrsbackup" readonly class="form-control timepicker2">

                                                                        <div class="input-group-addon">
                                                                            <i class="fa fa-clock-o"></i>
                                                                        </div>
                                                                    </div>
                                                                    <!-- /.input group -->
                                                                </div>
                                                                <!-- /.form group -->
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="backups" class="col-sm-9 control-label">@lang('app.backups') @lang('app.automatic')</label>
                                                                <div class="col-sm-6">
                                                                    <label><input id="backups" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>


                                                        </form>
                                                    </div>
                                                </div>

                                                </div>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <a href="#faq-1-8" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
                                                            <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                            <i class="ace-icon fa fa-refresh bigger-130"></i>
                                                            &nbsp; @lang('app.routerSync')
                                                        </a>
                                                    </div>
                                                    <div class="panel-collapse collapse" id="faq-1-8">
                                                        <div class="panel-body">
                                                            <!-- time Picker -->
                                                            <div class="form-group">
                                                                <label for="phonecode" class="col-sm-3 control-label">@lang('app.selectIntervel')</label>
                                                                <div class="col-sm-8">
                                                                    <select class="form-control" name="router-interval" style="width: 100%" id="router-interval">
                                                                        <option value="1" @if($global->router_interval == 1) selected @endif>@lang('app.every') 1 Hour</option>
                                                                        <option value="2" @if($global->router_interval == 2) selected @endif>@lang('app.every') 2 Hour</option>
                                                                        <option value="3" @if($global->router_interval == 3) selected @endif>@lang('app.every') 3 Hour</option>
                                                                        <option value="4" @if($global->router_interval == 4) selected @endif>@lang('app.every') 4 Hour</option>
                                                                        <option value="5" @if($global->router_interval == 5) selected @endif>@lang('app.every') 5 Hour</option>
                                                                        <option value="6" @if($global->router_interval == 6) selected @endif>@lang('app.every') 6 Hour</option>
                                                                        <option value="7" @if($global->router_interval == 7) selected @endif>@lang('app.every') 7 Hour</option>

                                                                    </select>
                                                                </div>

                                                            </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>

                                    <div id="faq-tab-2" class="tab-pane fade">
                                        <h4 class="title_con">
                                            <i class="ace-icon la la-cog bigger-120"></i>
                                            @lang('app.system')
                                        </h4>
                                        <div class="space-8"></div>
                                        <div id="faq-list-2" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-2-1" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-info-circle bigger-130"></i>
                                                        &nbsp; @lang('app.systemInformation')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-2-1">
                                                    <div class="panel-body">

                                                        <ul class="list-unstyled spaced">
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.platform'):<b class="green"> <?php echo php_uname(); ?> </b>
                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.database'): <b class="green"> <?php echo mysqli_get_client_info(); ?> </b>
                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.version') PHP: <b class="green"> <?php echo phpversion(); ?> </b>
                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                Web Server: <b class="green">

                                                                </b>

                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.coreDirectory'): <b class="green"> <?php echo base_path(); ?> </b>

                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.homeDirectory'): <b class="green"> <?php echo public_path(); ?> </b>

                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.browser'): <b class="green"> <?php echo $_SERVER [ 'HTTP_USER_AGENT' ]; ?></b>
                                                            </li>
                                                            <li class="divider"></li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.author'): <b class="green">Smartisp</b>
                                                            </li>
                                                            <li>
                                                                <i class="ace-icon fa fa-caret-right blue"></i>
                                                                @lang('app.authorWebsite'): <a href="http://Smartisp.us" target="_blank">http://Smartisp.us</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-4" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-envelope-o bigger-130"></i>
                                                        &nbsp; Email SMTP principal
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-1-4">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-horizontal" id="smtpform">
                                                                <div class="form-group">
                                                                    <label for="smtpserver" class="col-sm-2 control-label">@lang('app.server')</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" name="server" id="smtpserver" maxlength="30" placeholder="smtp.gmail.com">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="email" class="col-sm-2 control-label">@lang('app.email')</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="email" class="form-control" name="email" id="email" placeholder="tuempresa@gmail.com" maxlength="60">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="password" class="col-sm-2 control-label">@lang('app.password')</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="password" class="form-control" name="pass" id="password" maxlength="50">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="protocol" class="col-sm-2 control-label">@lang('app.protocol')</label>
                                                                    <div class="col-sm-10">
                                                                        <select class="form-control" name="protocol" id="protocol">
                                                                            <option value="tls" selected>TLS</option>
                                                                            <option value="ssl">SSL</option>
                                                                            <option value="">@lang('app.none')</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="port" class="col-sm-2 control-label">@lang('app.port')</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="text" class="form-control" name="port" id="port" placeholder="587" maxlength="5">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <div class="col-sm-offset-2 col-sm-10">
                                                                        <button type="button" class="btn btn-primary btn-sm" id="btnsavesmtp">@lang('app.save')</button>
                                                                    </div>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-14" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-envelope bigger-130"></i>
                                                        &nbsp; @lang('app.emailNotificationsTickets')
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-1-14">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-horizontal">
                                                                <div class="form-group">

                                                                    <div class="col-sm-10">
                                                                        <p>@lang('app.emailThatReceivesNotifications')</p>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="emailtickets" class="col-sm-2 control-label">@lang('app.email')</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="email" class="form-control" id="emailtickets" placeholder="soporte@gmail.com" maxlength="60">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <div class="col-sm-offset-2 col-sm-10">
                                                                        <button type="button" class="btn btn-primary btn-sm" id="btnsavesemailticket">@lang('app.save')</button>
                                                                    </div>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            {{-- <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-11" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-compass bigger-130"></i>
                                                        &nbsp; Zona horaria
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-1-11">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-inline">
                                                                <div class="form-group">
                                                                    <label class="sr-only" for="exampleInputPassword3">Password</label>
                                                                    <select class="form-control" name="time-zone" id="zone">
                                                                        <option value="America/Caracas">America/Caracas</option>
                                                                        <option value="America/Buenos_Aires">America/Buenos_Aires</option>
                                                                        <option value="America/Los_Angeles">America/Los_Angeles</option>
                                                                        <option value="America/Sao_Paulo">America/Sao_Paulo</option>
                                                                        <option value="America/Toronto">America/Toronto</option>
                                                                        <option value="America/Santa_Isabel">America/Santa_Isabel</option>
                                                                        <option value="America/Dominica">America/Dominica</option>
                                                                        <option value="America/Monterrey">America/Monterrey</option>
                                                                        <option value="America/New_York">America/New_York</option>
                                                                        <option value="America/Costa_Rica">America/Costa_Rica</option>
                                                                        <option value="America/La_Paz">America/La_Paz</option>
                                                                        <option value="America/Phoenix">America/Phoenix</option>
                                                                        <option value="America/Santiago">America/Santiago</option>
                                                                        <option value="America/Mexico_City">America/Mexico_City</option>
                                                                        <option value="America/Lima">America/Lima</option>
                                                                        <option value="America/Guatemala">America/Guatemala</option>
                                                                        <option value="America/Panama">America/Panama</option>
                                                                        <option value="America/Managua">America/Managua</option>
                                                                        <option value="America/Guayaquil">America/Guayaquil</option>
                                                                        <option value="America/Porto_Velho">America/Porto_Velho</option>
                                                                        <option value="America/Bogota">America/Bogota</option>
                                                                        <option value="Europe/Madrid">Europe/Madrid</option>
                                                                        <option value="Europe/Moscow">Europe/Moscow</option>
                                                                        <option value="Europe/Paris">Europe/Paris</option>
                                                                        <option value="Indian/Chagos">Indian/Chagos</option>
                                                                        <option value="Indian/Maldives">Indian/Maldives</option>
                                                                        <option value="Indian/Antananarivo">Indian/Antananarivo</option>
                                                                        <option value="Asia/Singapore">Asia/Singapore</option>
                                                                        <option value="Asia/Taipei">Asia/Taipei</option>
                                                                        <option value="Asia/Tokyo">Asia/Tokyo</option>
                                                                        <option value="Africa/Niamey">Africa/Niamey</option>
                                                                        <option value="Africa/Dakar">Africa/Dakar</option>
                                                                        <option value="Africa/Cairo">Africa/Cairo</option>
                                                                        <option value="Africa/Luanda">Africa/Luanda</option>
                                                                        <option value="UTC">UTC</option>
                                                                    </select>
                                                                </div>
                                                                <button type="button" class="btn btn-primary btn-sm" id="btnsavezone"> Guardar</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> --}}


                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-12" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-map bigger-130"></i>
                                                        &nbsp;@lang('app.googleMapsDefaulLocation')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-1-12">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-horizontal">


                                                                <div class="form-group">

                                                                    <div class="col-sm-5">
                                                                        <input type="text" class="form-control" id="locationdefault" placeholder="-0.1806532,-78.46783820000002">
                                                                    </div>
                                                                    @if($map!='0')
                                                                    <div class="col-sm-1">
                                                                        <button type="button" class="btn btn-sm btn-danger" id="openmap" data-toggle="modal" data-target="#modalmapedit" title="@lang('app.open') Mapa"><i class="fa fa-map"></i></button>
                                                                    </div>
                                                                    @endif

                                                                </div>

                                                                <div class="form-group">
                                                                    <div class="col-sm-5">
                                                                        <button type="button" class="btn btn-primary btn-sm" id="btnsavedefaultmap">@lang('app.save')</button>
                                                                    </div>
                                                                </div>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-8" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-bug bigger-130"></i>
                                                        &nbsp; @lang('app.debugMode')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-1-8">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-inline">
                                                                <div class="form-group">
                                                                    <label for="debug" class="col-sm-7 control-label">@lang('app.debugMode')</label>
                                                                    <div class="col-sm-3">
                                                                        <label><input id="debug" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-10" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-eraser bigger-130"></i>
                                                        &nbsp; @lang('app.systemCache')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-1-10">
                                                    <div class="panel-body">
                                                        <div class="col-xs-6">
                                                            <form class="form-inline">
                                                                <div class="form-group">
                                                                    <label for="cache" class="col-sm-7 control-label">@lang('app.clearCache')</label>
                                                                    <div class="col-sm-3">
                                                                        <label><input id="cache" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-1-9" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-file-image-o bigger-130"></i>
                                                        &nbsp; @lang('app.loginLogo')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-1-9">
                                                    <div class="panel-body">
                                                        <div class="col-xs-12">
                                                            <form id="logoform" method="post" enctype="multipart/form-data">
                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-1 control-label">@lang('app.uploadImage')</label>
                                                                    <div class="col-xs-5">
                                                                        <input type="file" class="form-control" name="file" id="file">
                                                                        <p>@lang('app.imageWithExtension').</p>
                                                                        <button type="submit" class="btn btn-primary btn-sm" id="btnsaveimg"> @lang('app.save')</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>




                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-2-3" data-parent="#faq-list-2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-exclamation-triangle bigger-130"></i>
                                                        &nbsp; @lang('app.resetValues')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-2-3">
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <b class="red"><i class="ace-icon fa fa-exclamation-triangle bigger-120"></i> @lang('app.cautionRestoring').</b>
                                                        </div>
                                                        <div class="col-xs-6">                                                            <br>
                                                            <form class="form-inline">
                                                                <!-- <div class="form-group">
                                                                    <label for="ressys" class="col-sm-8 control-label">@lang('app.resetSystem')</label>
                                                                    <div class="col-sm-3">
                                                                        <label><input id="ressys" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="respay" class="col-sm-8 control-label">@lang('app.ResetPayments')</label>
                                                                    <div class="col-sm-3">
                                                                        <label><input id="respay" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div> -->
                                                                <div class="form-group">
                                                                    <label for="reslog" class="col-sm-8 control-label">@lang('app.ResetLogs')</label>
                                                                    <div class="col-sm-3">
                                                                        <label><input id="reslog" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                            <span class="lbl"></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="faq-tab-6" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon la la-commenting bigger-120"></i>
                                            SMS
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-6" class="panel-group accordion-style1 accordion-style2">

                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-6-1" data-parent="#faq-list-6" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="fa fa-envelope" aria-hidden="true"></i>
                                                        &nbsp; Twilio SMS
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-6-1">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="formsmsgateway">


                                                            <div class="form-group">
                                                                <label  for="smsemail" class="col-sm-1 control-label">Account Sid</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="token" class="form-control" value="@if($twsms['options']['t']){{$twsms['options']['t']}}@endif"/>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label  for="deviceid" class="col-sm-1 control-label">Auth Token</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="deviceid" value="@if($twsms['options']['d']){{$twsms['options']['d']}}@endif"/>
                                                                </div>
                                                            </div>
															<div class="form-group">
                                                                <label  for="deviceid" class="col-sm-1 control-label">From Number</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="twinumber" value="@if($twsms['options']['n']){{$twsms['options']['n']}}@endif"/>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                               
                                                                <label for="smsg" class="col-sm-1 control-label">Activar</label>
                                                                <div class="col-sm-11">
																
                                                                    <label>
																	<input  name="enabledsmsg"  class="ace ace-switch ace-switch-6" type="checkbox" @if(($twsms['options']['e'])=='1')checked @endif  />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">

                                                                <div  class="col-sm-8 control-label"></div>
                                                            
                                                                <div  class="col-sm-4">
                                                                    <a target="_blanck" href="https://www.twilio.com/try-twilio?promo=qqU4Zd">Crear AQUI... Una Cuenta en Twilio</a>
                                                                </div>
                                                            
                                                            </div>

                                                            <div class="form-group">
                                                              
                                                                <div class="col-sm-12">
                                                                    <label for="btnsmsmgateway" class="col-sm-1 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnsmsmgateway"> @lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-6-2" data-parent="#faq-list-6" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="fa fa-whatsapp" aria-hidden="true"></i>
                                                        &nbsp; Twilio Whatsapp SMS
                                                    </a>
                                                </div>

                                                 <div class="panel-collapse collapse" id="faq-6-2">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="formwhatsapp">


                                                            <div class="form-group">
                                                                <label  for="smsemail" class="col-sm-1 control-label">Account Sid</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" name="wappsid" class="form-control" value="@if($twsmsarr['options']['t']){{$twsmsarr['options']['t']}}@endif" />
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label  for="deviceid" class="col-sm-1 control-label">Auth Token</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="wapptoken" value="@if($twsmsarr['options']['d']){{$twsmsarr['options']['d']}}@endif" />
                                                                </div> 
                                                            </div>
															<div class="form-group">
                                                                <label  for="deviceid" class="col-sm-1 control-label">From Number</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="wappnumber" value="@if($twsmsarr['options']['n']){{$twsmsarr['options']['n']}}@endif" />
                                                                </div> 
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="smsg" class="col-sm-1 control-label">Activar</label>
                                                                 <div class="col-sm-11">
                                                                    <label>
																
																	<input  name="enabledsmsg"  class="ace ace-switch ace-switch-6" type="checkbox" @if(($twsmsarr['options']['e'])=='1')checked @endif />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                </div>
                                                            </div>


                                                            <div class="form-group">

                                                                <div  class="col-sm-8 control-label"></div>
                                                            
                                                                <div  class="col-sm-4">
                                                                    <a target="_blanck" href="https://www.twilio.com/try-twilio?promo=qqU4Zd">Crear AQUI... Una Cuenta en Twilio</a>
                                                                </div>
                                                            
                                                            </div>
                                                            <div class="form-group">

                                                                <div class="col-sm-12">
                                                                    <label for="btnformwhatsapp" class="col-sm-1 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnformwhatsapp"> @lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
											<div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-6-3" data-parent="#faq-list-6" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="fa fa-cog" aria-hidden="true"></i>
                                                        &nbsp; @lang('app.configuration') @lang('app.additional') SMS
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-6-3">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="formsmsgeneral">
                                                            <div class="form-group">
                                                                <label for="phonecode" class="col-sm-1 control-label">@lang('app.countryCode')</label>
                                                                <div class="col-sm-10">
                                                                    <select class="form-control" name="phonecode" style="width: 100%" id="phonecode">
                                                                        <option  value="213">Algeria (+213)</option>
                                                                        <option  value="49">Alemania (+49)</option>
                                                                        <option  value="376">Andorra (+376)</option>
                                                                        <option  value="244">Angola (+244)</option>
                                                                        <option  value="1264">Anguilla (+1264)</option>
                                                                        <option  value="1268">Antigua &amp; Barbuda (+1268)</option>
                                                                        <option  value="54" selected>Argentina (+54)</option>
                                                                        <option  value="374">Armenia (+374)</option>
                                                                        <option  value="297">Aruba (+297)</option>
                                                                        <option  value="61">Australia (+61)</option>
                                                                        <option  value="43">Austria (+43)</option>
                                                                        <option  value="994">Azerbaijan (+994)</option>
                                                                        <option  value="1242">Bahamas (+1242)</option>
                                                                        <option  value="591">Bolivia (+591)</option>
                                                                        <option  value="55">Brazil (+55)</option>
                                                                        <option  value="1">Canada (+1)</option>
                                                                        <option  value="238">Islas de Cabo Verde (+238)</option>
                                                                        <option  value="1345">Islas Caimán (+1345)</option>
                                                                        <option  value="236">República Centroafricana (+236)</option>
                                                                        <option  value="56">Chile (+56)</option>
                                                                        <option  value="57">Colombia (+57)</option>
                                                                        <option  value="269">Comoros (+269)</option>
                                                                        <option  value="242">Congo (+242)</option>
                                                                        <option  value="506">Costa Rica (+506)</option>
                                                                        <option  value="53">Cuba (+53)</option>
                                                                        <option  value="42">República Checa (+42)</option>
                                                                        <option  value="45">Dinamarca (+45)</option>
                                                                        <option  value="1809">Dominica (+1809)</option>
                                                                        <option  value="1809">República Dominicana (+1809)</option>
                                                                        <option  value="593">Ecuador (+593)</option>
                                                                        <option  value="503">El Salvador (+503)</option>
                                                                        <option  value="240">Guinea Ecuatorial (+240)</option>
                                                                        <option  value="500">Islas Malvinas (+500)</option>
                                                                        <option  value="33">Francia (+33)</option>
                                                                        <option  value="594">Guayana Francesa (+594)</option>
                                                                        <option  value="233">Ghana (+233)</option>
                                                                        <option  value="30">Grecia (+30)</option>
                                                                        <option  value="1473">Granada (+1473)</option>
                                                                        <option  value="590">Guadalupe (+590)</option>
                                                                        <option  value="502">Guatemala (+502)</option>
                                                                        <option  value="592">Guyana (+592)</option>
                                                                        <option  value="509">Haiti (+509)</option>
                                                                        <option  value="504">Honduras (+504)</option>
                                                                        <option  value="852">Hong Kong (+852)</option>
                                                                        <option  value="354">Islandia (+354)</option>
                                                                        <option  value="91">India (+91)</option>
                                                                        <option  value="62">Indonesia (+62)</option>
                                                                        <option  value="98">Iran (+98)</option>
                                                                        <option  value="964">Iraq (+964)</option>
                                                                        <option  value="972">Israel (+972)</option>
                                                                        <option  value="39">Italia (+39)</option>
                                                                        <option  value="1876">Jamaica (+1876)</option>
                                                                        <option  value="81">Japón (+81)</option>
                                                                        <option  value="962">Jordan (+962)</option>
                                                                        <option  value="7">Kazakhstan (+7)</option>
                                                                        <option  value="254">Kenya (+254)</option>
                                                                        <option  value="850">Corea del Norte (+850)</option>
                                                                        <option  value="82">Corea del Sur (+82)</option>
                                                                        <option  value="965">Kuwait (+965)</option>
                                                                        <option  value="996">Kyrgyzstan (+996)</option>
                                                                        <option  value="856">Laos (+856)</option>
                                                                        <option  value="371">Latvia (+371)</option>
                                                                        <option  value="352">Luxembourgo (+352)</option>
                                                                        <option  value="853">Macao (+853)</option>
                                                                        <option  value="389">Macedonia (+389)</option>
                                                                        <option  value="261">Madagascar (+261)</option>
                                                                        <option  value="265">Malawi (+265)</option>
                                                                        <option  value="60">Malasia (+60)</option>
                                                                        <option  value="223">Mali (+223)</option>
                                                                        <option  value="356">Malta (+356)</option>
                                                                        <option  value="52">Mexico (+52)</option>
                                                                        <option  value="691">Micronesia (+691)</option>
                                                                        <option  value="377">Monaco (+377)</option>
                                                                        <option  value="976">Mongolia (+976)</option>
                                                                        <option  value="258">Mozambique (+258)</option>
                                                                        <option  value="977">Nepal (+977)</option>
                                                                        <option  value="31">Países Bajos (+31)</option>
                                                                        <option  value="64">Nueva Zelanda (+64)</option>
                                                                        <option  value="505">Nicaragua (+505)</option>
                                                                        <option  value="507">Panama (+507)</option>
                                                                        <option  value="675">Papúa Nueva Guinea (+675)</option>
                                                                        <option  value="595">Paraguay (+595)</option>
                                                                        <option  value="51">Peru (+51)</option>
                                                                        <option  value="63">Filipinas (+63)</option>
                                                                        <option  value="48">Polonia (+48)</option>
                                                                        <option  value="351">Portugal (+351)</option>
                                                                        <option  value="1787">Puerto Rico (+1787)</option>
                                                                        <option  value="974">Qatar (+974)</option>
                                                                        <option  value="40">Rumania (+40)</option>
                                                                        <option  value="7">Rusia (+7)</option>
                                                                        <option  value="378">San Marino (+378)</option>
                                                                        <option  value="239">Sao Tome &amp; Principe (+239)</option>
                                                                        <option  value="221">Senegal (+221)</option>
                                                                        <option  value="232">Sierra Leona (+232)</option>
                                                                        <option  value="65">Singapur (+65)</option>
                                                                        <option  value="27">Sud Africa (+27)</option>
                                                                        <option  value="34">España (+34)</option>
                                                                        <option  value="1868">Trinidad &amp; Tobago (+1868)</option>
                                                                        <option  value="598">Uruguay (+598)</option>
                                                                        <option  value="379">Ciudad del Vaticano (+379)</option>
                                                                        <option  value="58">Venezuela (+58)</option>

                                                                    </select>
                                                                </div>

                                                            </div>

                                                            <div class="form-group">
                                                                <label  for="delaysend" class="col-sm-1 control-label">@lang('app.Pausebetweenmessages')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="number" class="form-control" name="delaysend" id="delaysend">
                                                                    <span id="helpBlock" class="help-block">@lang('app.Valueexpressedinseconds').</span>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">

                                                                <div class="col-sm-12">
                                                                    <label for="btnsmsgeneral" class="col-sm-1 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnsmsgeneral">@lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                    </div>

                                    <div id="faq-tab-7" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon la la-money bigger-120"></i>
                                            PAGO
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-7" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-7-1" data-parent="#faq-list-7" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>


                                                        <i class="ace-icon fa fa-cc-paypal bigger-130"></i>
                                                        &nbsp; PayPal
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-7-1">
                                                    <div class="panel-body">

                                                        <form class="form-horizontal" id="form-paypal">
                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-sm-2 control-label">Client ID</label>
                                                                <div class="col-sm-10">
                                                                    <input class="form-control" type="text" name="paypal_client_id" id="paypal_client_id" value="{{ $paypal_client_id ? $paypal_client_id : '' }}">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="paypal_secret" class="col-sm-2 control-label">Secret</label>
                                                                <div class="col-sm-10">
                                                                    <input class="form-control" type="text" name="paypal_secret" id="paypal_secret" value="{{ $paypal_secret ? $paypal_secret : '' }}">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="paypal_mode" class="col-sm-2 control-label">Paypal Mode</label>
                                                                <div class="col-sm-4">
                                                                    <select class="form-control" name="paypal_mode" id="paypal_mode">
                                                                        <option value="sandbox" {{ $paypal_mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                                        <option value="live" {{ $paypal_mode === 'live' ? 'selected' : '' }}>Live</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-12">
                                                                    <label for="btnpaypal" class="col-sm-2 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnpaypal"> @lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-7-2" data-parent="#faq-list-7" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                        &nbsp; Stripe
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-7-2">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="form-stripe">


                                                            <div class="form-group">
                                                                <label for="stripe_key" class="col-sm-2 control-label">Stripe Key</label>
                                                                <div class="col-sm-10">
                                                                    <input class="form-control" type="text" name="stripe_key" id="stripe_key" value="{{ $stripe_key ?? '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="stripe_secret" class="col-sm-2 control-label">Stripe Secret</label>
                                                                <div class="col-sm-10">
                                                                    <input class="form-control" type="text" name="stripe_secret" id="stripe_secret" value="{{ $stripe_secret ?? '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="col-sm-12">
                                                                    <label for="btnstripe" class="col-sm-2 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnstripe"> @lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-7-3" data-parent="#faq-list-7" data-toggle="collapse"
                                                    class="accordion-toggle collapsed">
                                                    <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                    <i class="ace-icon fa fa-cc-visa bigger-130"></i>
                                                    &nbsp; PayU
                                                </a>
                                            </div>

                                            <div class="panel-collapse collapse" id="faq-7-3">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-payu">

                                                        <div class="form-group">
                                                            <label for="stripe_key" class="col-sm-2 control-label">Merchant ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text" name="payu_merchant_id" id="payu_merchant_id" value="{{ $payu_merchant_id ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Account ID</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text" name="payu_account_id" id="payu_account_id" value="{{ $payu_account_id ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="stripe_secret" class="col-sm-2 control-label">Api Key</label>
                                                            <div class="col-sm-10">
                                                                <input class="form-control" type="text" name="payu_api_key" id="payu_api_key" value="{{ $payu_api_key ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="paypal_mode" class="col-sm-2 control-label">Payu Mode</label>
                                                            <div class="col-sm-4">
                                                                <select class="form-control" name="payu_mode" id="payu_mode">
                                                                    <option value="sandbox" {{ $payu_mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                                    <option value="live" {{ $payu_mode === 'live' ? 'selected' : '' }}>Live</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnpayu" class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm" id="btnpayu"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                    <div id="faq-tab-8" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon la la-money bigger-120"></i>
                                            @lang('app.electronicBilling')
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-8" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-8-1" data-parent="#faq-list-8" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                        &nbsp; @lang('app.DIGITALSIGNATURE')
                                                    </a>
                                                </div>

                                                <div class="panel-heading">
                                                    <a href="#faq-8-2" data-parent="#faq-list-8" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                        &nbsp; @lang('app.activate')/@lang('app.deactivate') @lang('app.electronicBilling')
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-8-1">
                                                    <div class="panel-body">
                                                        <div class="col-xs-12">
                                                            <form id="logoform2" method="post" enctype="multipart/form-data">
                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">@lang('app.uploadDigitalCertificate')</label>

                                                                    <input type="file" class="form-control" name="certificado_digital" id="certificado_digital">
                                                                    <p>@lang('app.FileWithExtensionp')</p>

                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">@lang('app.DigitalCertificatePassword')</label>

                                                                    <input type="text" class="form-control" name="pass_certificado" id="pass_certificado">

                                                                </div>
                                                                <div class="form-group">
                                                                    <button type="submit" class="btn btn-primary btn-sm" id="logoform2"> @lang('app.save')</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-8-2">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="form-factel-active" enctype="multipart/form-data">

                                                            <div class="form-group">
                                                                <label for="paypal_client_id" class="col-sm-2 control-label text-lowercase">@lang('app.electronicBilling')</label>
                                                                <div class="col-sm-10">
                                                                    <input type="radio" name="status_factel"  id="status_factel" value="0" {{ $status_factel === 0 ? 'checked' : '' }}> @lang('app.activate') <br>
                                                                    <input type="radio" name="status_factel" id="status_factel" value="1" {{ $status_factel === 1 ? 'checked' : '' }}> @lang('app.deactivate')<br>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-12">
                                                                    <label for="btnstripe" class="col-sm-2 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnsavefactel"> @lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="faq-tab-9" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon la la-money bigger-120"></i>
                                            @lang('app.transmitter')
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-9" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-9-1" data-parent="#faq-list-9" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-cc-stripe bigger-130"></i>
                                                        &nbsp; @lang('app.issuerData')
                                                    </a>

                                                </div>

                                                <div class="panel-collapse collapse" id="faq-9-1">
                                                    <div class="panel-body">
                                                        <div class="col-xs-12">
                                                            <form id="form_emisor" method="post">

                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">RUC</label>
                                                                    <input type="text" class="form-control" name="ruc" id="ruc" value="{{$emisor_rut}}">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">@lang('app.businessName')</label>
                                                                    <input type="text" class="form-control" name="razonSocial" id="razonSocial" value="{{$emisor_razonSocial}}">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">@lang('app.Tradename')</label>
                                                                    <input type="text" class="form-control" name="nombreComercial" id="nombreComercial" value="{{$emisor_nombreComercial}}">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-12 control-label">@lang('app.direction')</label>
                                                                    <input type="text" class="form-control" name="direccion" id="Direccion" value="{{$emisor_direccion}}">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="paypal_client_id" class="col-sm-2 control-label">@lang('app.ForcedToKeepAccounting')</label>
                                                                    <div>
                                                                        <input type="radio" name="status_cont"  id="status_cont" value="SI" {{ $status_cont === 'SI' ? 'checked' : '' }}> @lang('app.yes') <br>
                                                                        <input type="radio" name="status_cont" id="status_cont" value="NO" {{ $status_cont === 'NO' ? 'checked' : '' }}> NO<br>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group" style="text-align: left;">
                                                                    <button type="submit" class="btn btn-primary btn-sm" id="btnsaveemisor"> @lang('app.save')</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="panel panel-default">
                                                {{-- <div class="panel-heading">
                                                    <a href="#faq-9-2" data-parent="#faq-list-7" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-envelope-o bigger-130"></i>
                                                        &nbsp; Email smtp @lang('app.electronicBilling)
                                                    </a>
                                                </div> --}}

                                                <div class="panel-collapse collapse" id="faq-9-2">
                                                    <div class="panel-body">
                                                        <form id="form_email_f" method="post">
                                                            <div class="form-group">
                                                                <label for="cache" class="col-xs-12 control-label">@lang('app.CorreoElectrònico')</label>
                                                                <input type="text" value="{{$email_f}}" class="form-control" name="email_f" id="email_f">
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="form-group" style="text-align: left;">
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnsaveemail_f"> @lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-9-3" data-parent="#faq-list-7" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-file-image-o bigger-130"></i>
                                                        &nbsp; @lang('app.electronicBillingLogo')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-9-3">
                                                    <div class="panel-body">
                                                        <div class="col-xs-12">
                                                            <form id="logoform_f" method="post" enctype="multipart/form-data">
                                                                <div class="form-group">
                                                                    <label for="cache" class="col-xs-1 control-label">@lang('app.uploadImage')</label>
                                                                    <div class="col-xs-5">
                                                                        <input type="file" class="form-control" name="file" id="file">
                                                                        <p>@lang('app.maximumMustBeExtended').</p>
                                                                        <button type="submit" class="btn btn-primary btn-sm" id="btnsaveimg"> @lang('app.save')</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>







                                            <div class="panel-collapse collapse" id="faq-8-2">
                                                <div class="panel-body">
                                                    <form class="form-horizontal" id="form-factel-active" enctype="multipart/form-data">

                                                        <div class="form-group">
                                                            <label for="paypal_client_id" class="col-sm-2 control-label text-lowercase">@lang('app.electronicBilling')</label>
                                                            <div class="col-sm-10">
                                                                <input type="radio" name="status_factel"  id="status_factel" value="0" {{ $status_factel === 0 ? 'checked' : '' }}> @lang('app.activate') <br>
                                                                <input type="radio" name="status_factel" id="status_factel" value="1" {{ $status_factel === 1 ? 'checked' : '' }}> @lang('app.deactivate')<br>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="col-sm-12">
                                                                <label for="btnstripe" class="col-sm-2 control-label"></label>
                                                                <button type="button" class="btn btn-primary btn-sm" id="btnsavefactel"> @lang('app.save')</button>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="faq-tab-5" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon icon-feed bigger-120"></i>
                                            APIS
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-5" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-5-1" data-parent="#faq-list-5" data-toggle="collapse" class="accordion-toggle collapsed" id="">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-server bigger-130"></i>
                                                        &nbsp; Mikrotik API
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-5-1">
                                                    <div class="panel-body">
                                                        <p>@lang('app.HereYoucanconfigurethe').</p>

                                                        <form class="form-horizontal" id="formapimk">
                                                            <div class="form-group">
                                                                <label for="attempts" class="col-sm-2 control-label">Attempts</label>
                                                                <div class="col-sm-6">
                                                                    <input type="text" class="form-control" maxlength="2" name="attempts" id="attempts">
                                                                    <span class="help-block">@lang('app.Numberofconnection').</span>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="Timeout" class="col-sm-2 control-label">Timeout</label>
                                                                <div class="col-sm-6">
                                                                    <input type="text" class="form-control" maxlength="2" name="timeout" id="Timeout">
                                                                    <span class="help-block">@lang('app.Connectionattempttimeout').</span>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="mkdeug" class="col-sm-2 control-label">@lang('app.debug')</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="mkdebug" name="mkdebug" value="true" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                    <span class="help-block">@lang('app.showDebugInformation').</span>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="mkssl" class="col-sm-2 control-label">SSL</label>
                                                                <div class="col-sm-3">
                                                                    <label><input id="mkssl" name="mkssl" value="true" class="ace ace-switch ace-switch-6" type="checkbox" />
                                                                        <span class="lbl"></span>
                                                                    </label>
                                                                    <span class="help-block">@lang('app.ConnectUsingSSL').</span>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <button type="button" id="btnsavemkapi" class="btn btn-primary">@lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!--start tab-->

                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-5-2" data-parent="#faq-list-5" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="fa fa-map" aria-hidden="true"></i>
                                                        &nbsp; Google Maps API
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-5-2">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="formapimaps">


                                                            <div class="form-group">
                                                                <label  for="maps" class="col-sm-1 control-label">@lang('app.Key') API</label>
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="googlemapsapi" id="maps">
                                                                    <span class="help-block">@lang('app.GetYourGoogleMapsAPI'), <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">@lang('app.getmyAPIAPIkeyfor')</a>.</span>
                                                                </div>
                                                            </div>


                                                            <div class="form-group">

                                                                <div class="col-sm-12">
                                                                    <label for="btnsmsmgateway" class="col-sm-1 control-label"></label>
                                                                    <button type="button" class="btn btn-primary btn-sm" id="btnsaveapimaps"> @lang('app.save')</button>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end tab-->




                                        </div>
                                    </div>
                                    <div id="faq-tab-4" class="tab-pane fade">
                                        <h4 class="title_con">
                                            <i class="ace-icon la la-user bigger-120"></i>
                                            @lang('app.clientPortal')
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-4" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-4-1" data-parent="#faq-list-4" data-toggle="collapse" class="accordion-toggle collapsed" id="adv">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
                                                        <i class="ace-icon fa fa-paper-plane bigger-130"></i>
                                                        &nbsp; @lang('app.PortalAddress')
                                                    </a>
                                                </div>
                                                <div class="panel-collapse collapse" id="faq-4-1">
                                                    <div class="panel-body">
                                                        <form class="form-horizontal" id="formaddadv">
                                                            <div class="form-group">
                                                                <label for="url" class="col-sm-2 control-label">IP/URL @lang('app.server')</label>
                                                                <div class="col-sm-3">
                                                                    <input type="text" class="form-control" name="ip" id="url" maxlength="50">
                                                                </div>


                                                            </div>

                                                            <div class="form-group">
                                                                <label for="url" class="col-sm-2 control-label">@lang('app.directory') </label>
                                                                <div class="col-sm-3">
                                                                    <input type="text" class="form-control" name="path" placeholder="aviso" id="path" maxlength="20">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <div class="col-sm-offset-2 col-sm-10">
                                                                    <input type="hidden" name="idadv" id="idadv">
                                                                    <button type="button" class="btn btn-primary" id="savebtnadv">@lang('app.save')</button>
                                                                    <button type="reset" class="btn btn-success">@lang('app.clear')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="faq-tab-3" class="tab-pane fade">

                                        <h4 class="title_con">
                                            <i class="ace-icon fa fa-language bigger-120"></i>
                                            @lang('app.languageSettings')
                                        </h4>
                                        <div class="space-8"></div>

                                        <div id="faq-list-3" class="panel-group accordion-style1 accordion-style2">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-3-1" data-parent="#faq-list-3" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-language bigger-130"></i>
                                                        &nbsp; @lang('app.languageSetting')
                                                    </a>

                                                </div>

                                                <div class="panel-collapse collapse" id="faq-3-1">
                                                    <div class="panel-body">
                                                        <form id="form_language_settings" method="post">
                                                            <div class="col-xs-12">
                                                                @foreach($allLanguages as $language)
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="backups" class="col-sm-9 control-label">{{$language->language_name}}</label>
                                                                            <div class="col-sm-6">
                                                                                <label><input id="backups[{{$language->id}}]" name="language_code[{{$language->language_code}}]" value="true" class="ace ace-switch ace-switch-6" @if($language->status == 'enabled') checked @endif type="checkbox" />
                                                                                    <span class="lbl"></span>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            <div class="col-xs-12">
                                                                <div class="form-group text-center">
                                                                    <button type="submit" class="btn btn-primary btn-sm" id="btnsavelanguagesettings"> @lang('app.save')</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="#faq-3-2" data-parent="#faq-list-3" data-toggle="collapse" class="accordion-toggle collapsed">
                                                        <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>

                                                        <i class="ace-icon fa fa-language bigger-130"></i>
                                                        @lang('app.SelectLanguage')
                                                    </a>
                                                </div>

                                                <div class="panel-collapse collapse" id="faq-3-2">
                                                    <div class="panel-body">
                                                        <div class="col-xs-12">
                                                            <form id="form_select_language" method="post">
                                                                <div class="form-group">
                                                                    <label for="paypal_mode" class="col-sm-2 control-label">@lang('app.language')</label>
                                                                    <div class="col-sm-4">
                                                                        <select class="form-control" name="language" id="language">
                                                                            @foreach($languages as $language)
                                                                                <option value="{{$language->language_code}}" {{ $global->locale === $language->language_code ? 'selected' : '' }}>{{ $language->language_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <div class="form-group" style="text-align: left;">
                                                                        <button type="button" class="btn btn-primary btn-sm" id="btnsavelanguage"> @lang('app.save')</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <a href="{{ url('/translations') }}" target="_blank"class="accordion-toggle collapsed">
                                                        <i class="green ace-icon fa fa-language"></i>  @lang('app.changeTranslations')
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                            </div>
                    </div>
			        </div>
                </div>

			<div class="modal fade bs-example-modal-lg" tabindex="-1" id="modalmapedit" role="dialog" aria-labelledby="myLargeModalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">

						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
							<h4 class="modal-title" id="myModalLabel"><i class="fa fa-map"></i> Google Maps</h4>
						</div>

						<div class="modal-body">
							<div class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-1 control-label">@lang('app.lookFor'):</label>

									<div class="col-sm-11">
										<input type="text" class="form-control" id="us4-address" />
									</div>
								</div>

								<div id="us4" style="width: 100%; height: 400px;"></div>
								<div class="clearfix">&nbsp;</div>



							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-crosshairs"></i> @lang('app.toAccept')</button>

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
@include('layouts.footer')
<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
	<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
</a>
</div>

@section('scripts')
@parent
@if($map!='0')
<script src="https://maps.googleapis.com/maps/api/js?v=3&amp;libraries=places,geometry&amp;key={{$map}}"></script>
<script src="{{asset('assets/js/jquery-locationpicker/dist/locationpicker.jquery.min.js')}}"></script>
@endif
<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
<script src="{{asset('assets/js/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
<script src="{{asset('assets/js/rocket/config-core.js')}}"></script>
@stop
@stop
