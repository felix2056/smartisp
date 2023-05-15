<!DOCTYPE html>
<html lang="es"><head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta charset="utf-8" />
	<title>@lang('app.clientPortal')| @lang('app.bills')</title>
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/jquery-ui.custom.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/bootstrap-timepicker.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/ace-fonts.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/ace.min.css')}}" class="ace-main-stylesheet" id="main-ace-style" />
	<link rel="stylesheet" href="{{asset('assets/css/waiting.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/reading-table.css')}}" />


	<script src="{{asset('assets/js/ace-extra.min.js')}}"></script>

	<link rel="shortcut icon" href="{{asset('assets/img/favicon.ico')}}">


	<script src="{{asset('assets/js/ace-extra.min.js')}}"></script>
	<link rel="stylesheet" href="{{asset('assets/css/new_template.css')}}">
	<link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/css/components.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/css/core/menu/menu-types/vertical-menu.min.css')}}">
	<link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/fonts/simple-line-icons/style.min.css')}}">
	<link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">



</head>
<body class="no-skin">
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
			@include('layouts.cliente.navbartopright')
			<!-- navbarheader right -->


		</div>
	</div>
	<div class="main-container" id="main-container">


		<div id="sidebar" class="sidebar responsive main-menu menu-fixed menu-light menu-accordion    menu-shadow ">
			<div class="main-menu-content ps-container ps-theme-dark ps-active-y" data-ps-id="a54019dc-7015-3695-86b4-54d119322d5c">

				<ul class="nav nav-list">
					<li class="">
						<a href="{{URL::to('portal')}}">
							<i class="menu-icon fa fa-desktop"></i>
							<span class="menu-text"> @lang('app.desk') </span>
						</a>

						<b class="arrow"></b>
					</li>

					<li class="active">
						<a href="{{URL::to('portal/bills')}}">
							<i class="menu-icon fa fa-list-alt"></i>
							<span class="menu-text">
								@lang('app.bills')
								<span id="bills" class="badge badge-primary"></span>
							</span>
						</a>

						<b class="arrow"></b>
					</li>

					<li class="">
						<a href="{{URL::to('portal/tickets')}}">
							<i class="menu-icon fa fa-ticket"></i>

							<span class="menu-text">
								@lang('app.supportTickets')

								<span id="tickets" class="badge badge-primary"></span>
							</span>
						</a>

						<b class="arrow"></b>
					</li>



				</ul><!-- /.nav-list -->
				<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
					<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
				</div>
			</div>
		</div>



		<div class="main-content">
			<div class="main-content-inner">
				<div class="breadcrumbs" id="breadcrumbs">
					<ul class="breadcrumb">
						<li>
							<i class="ace-icon fa fa-desktop desktop-icon"></i>
							<a href="<?php echo URL::to('portal'); ?>">@lang('app.desk')</a>
						</li>
						<li>
							<a href="<?php echo URL::to('portal/bills'); ?>">@lang('app.bills')</a>
						</li>
						<li class="active">@lang('app.list')</li>
					</ul>
				</div>
				<div class="page-content">
					<div class="page-header">
						<h1>
                            @lang('app.bills')
						</h1>
					</div>
					<!--start row-->
					<div class="row">
						<div class="col-sm-12">
							@if ($message = \Session::get('success'))
								<div class="alert alert-success alert-dismissable">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
									<i class="fa fa-check"></i> {!! $message !!}
								</div>
                                <?php \Session::forget('success');?>
							@endif

							@if ($message = \Session::get('error'))
								<div class="custom-alerts alert alert-danger fade in">
									<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
									{!! $message !!}
								</div>
                                <?php \Session::forget('error');?>
							@endif
						</div>
						<div class="col-sm-12">
                            {{--PayU Form--}}
                            <form method="post" id="payu_form" action="<?php echo $payu_url; ?>">

                                <input name="merchantId" type="hidden" value="<?php echo $merchantId; ?>">
                                <input name="accountId" type="hidden" value="<?php echo $accountId; ?>">
                                <input name="email" type="hidden" id="email" value="<?php echo $email; ?>">

                                <input name="description" type="hidden" value="invoice-<?php echo $invoiceId; ?>">
                                <input name="referenceCode" type="hidden" value="<?php echo $referenceCode; ?>">
                                <input name="amount" type="hidden" value="<?php echo $amount; ?>">

                                <input name="tax" type="hidden" value="0">
                                <input name="taxReturnBase" type="hidden" value="0">

                                <input name="currency" type="hidden" value="<?php echo $currency; ?>">
                                <input name="signature" type="hidden" value="<?php echo $signature; ?>">

                                <input name="test" type="hidden" value="{{ $payu_mode == 'sandbox' ? true : false }}">

                                <input name="buyerEmail" type="hidden" value="<?php echo $email; ?>">
                                <input name="responseUrl" type="hidden" value="<?php echo route('payuresponse'); ?>">
                                <input name="confirmationUrl" type="hidden" value="<?php echo route('payuconfirmation'); ?>">
                                <input name="Submit" type="submit" id="payu_submit" value="Enviar">
                                Hold on while we take you to the PayU payment page...
                            </form>
						</div><!--end col-->
					</div>
					<!--end row-->
					<!---------------------Inicio de Modals------------------------------->


				</div>
			</div>
		</div>
		@include('layouts.footer')
		<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
			<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
		</a>


	</div>
	<!--[if !IE]> -->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<!-- <![endif]-->
		<!--[if IE]>
<script src="{{asset('assets/js/libs/jquery1.11/jquery.min.js')}}"></script>
<![endif]-->
<!--[if !IE]> -->
<script type="text/javascript">
	window.jQuery || document.write("<script src='{{asset('assets/js/jquery.min.js')}}'>"+"<"+"/script>");
</script>
<!-- <![endif]-->
		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='{{asset('assets/js/jquery1x.min.js')}}'>"+"<"+"/script>");
</script>
<![endif]-->
<script type="text/javascript">
	if('ontouchstart' in document.documentElement) document.write("<script src='{{asset('assets/js/jquery.mobile.custom.min.js')}}'>"+"<"+"/script>");
</script>
	<script>
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	</script>
<script src="{{asset('assets/js/rocket/clientNotifications2-core.js')}}"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
<script src="{{asset('assets/js/jquery-ui.custom.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.ui.touch-punch.min.js')}}"></script>
<script src="{{asset('assets/js/dataTables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/dataTables/jquery.dataTables.bootstrap.min.js')}}"></script>
<script src="{{asset('assets/js/ace-elements.min.js')}}"></script>
<script src="{{asset('assets/js/ace.min.js')}}"></script>
<script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/froiden-helper/helper.js')}}"></script>
	<script src="https://checkout.stripe.com/checkout.js"></script>
<script src="{{asset('assets/js/rocket/billsClient-core.js')}}"></script>


<script>
    $(document).ready(function () {
        $('#payu_form').submit();
        $('#payu_submit').hide();
    });
</script>
</body>
</html>
