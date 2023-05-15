<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta charset="utf-8" />
	<title>Client Portal | @yield('title')</title>
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
	<link rel="stylesheet" href="{{ asset('assets/plugins/froiden-helper/helper.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/buttons.dataTables.min.css') }}">
	@yield('styles')
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
		@include('layouts.cliente.sidebar')

		@yield('content')

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
	<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery-ui.custom.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.ui.touch-punch.min.js')}}"></script>

    <script src="{{asset('assets/js/dataTables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/dataTables/jquery.dataTables.bootstrap.min.js')}}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/responsive.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>

    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>

	<script src="{{asset('assets/js/ace-elements.min.js')}}"></script>
	<script src="{{asset('assets/js/ace.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
	<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
	<script src="{{asset('assets/plugins/froiden-helper/helper.js')}}"></script>
	<script src="https://checkout.stripe.com/checkout.js"></script>
	<script src="{{asset('assets/js/rocket/billsClient-core.js')}}"></script>
	<script src="{{route('assets.lang', \App::getLocale())}}"></script>

    @yield('scripts')

</body>
</html>
