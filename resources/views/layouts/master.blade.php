
<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta charset="utf-8" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<title>SmartISP - @yield('title')</title>
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}"/>
	<link rel="stylesheet" href="{{ asset('assets/css/jquery.gritter.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/ace-fonts.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/ace.min.css') }}" class="ace-main-stylesheet" id="main-ace-style">
	<link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
	<script src="{{ asset('assets/js/ace-extra.min.js') }}"></script>
	<link rel="stylesheet" href="{{ asset('assets/css/new_template.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/newTemplate/app-assets/css/components.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/newTemplate/app-assets/css/core/menu/menu-types/vertical-menu.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/newTemplate/app-assets/fonts/simple-line-icons/style.min.css') }}">
	<link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">
		<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap.min.css') }}">
		<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/buttons.dataTables.min.css') }}">
	@yield('styles')
	@stack('styles')

	<link rel="stylesheet" href="{{ asset('assets/plugins/froiden-helper/helper.css') }}">
	<style>
		.m-b-10 {
			margin-bottom: 10px;
		}
		.m-l-10 {
			margin-left: 10px;
		}
		.placeholders {
			padding: 6px;
			border: 1px solid #abbac3;
			border-radius: 15px;
			cursor: pointer;
		}
		.m-b-20 {
			margin-bottom: 20px;
		}
		.placeholders:active {
			transform: scale(0.98);
			/* Scaling button to 0.98 to its original size */
			box-shadow: 3px 2px 22px 1px rgba(0, 0, 0, 0.24);
			/* Lowering the shadow */
		}
		.tt-menu {
            z-index: 401 !important;
        }
        span.twitter-typeahead {
            display: block !important;
        }
	</style>
</head>
<body class="no-skin">
	<div id="navbar"
		 class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-light bg-info navbar-shadow">

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
		@yield('content')

		<div class="modal fade bs-example-modal-lg" role="dialog"
			 aria-labelledby="myLargeModalLabel" id="addEditModal">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">

				</div>
			</div>
		</div>
		<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog"
			 aria-labelledby="myLargeModalLabel" id="addEditPlaceholderModal">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Add Placeholder</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">
								<fieldset>

									@php
										$allPlaceholders = [
											'COMPANY_NAME' => __('app.companyName'),
											'CLIENT_NAME' => __('app.clientName'),
											'EMAIL_CLIENT' => __('app.clientEmail'),
											'DNI_CLIENT' => __('app.clientDni'),
											'PHONE_CLIENT' => __('app.clientPhoneNumber'),
											'EMAIL_ISP' => __('app.companyEmail'),
											'DNI_ISP' => __('app.companyDni'),
											'PHONE_ISP' => __('app.companyPhone'),
											'DATE_REGISTRATION' => __('app.registrationDate')
										]
									@endphp

									@foreach($allPlaceholders as $key => $placeholder)
										<div class="col-sm-4 m-b-20">
											<div class="form-group">
												<p class="placeholders" onclick="addInEditor('{{$key}}')" data-placeholder="{{$key}}">{{ $placeholder }}</p>
											</div>
										</div>
									@endforeach

								</fieldset>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		@include('layouts.footer')
		<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
			<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
		</a>
	</div>
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
	<script src="{{route('assets.lang', \App::getLocale())}}"></script>
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/rocket/checkUpdate-core.js')}}"></script>
	<script src="{{asset('assets/js/rocket/userNotifications-core.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
	<script src="{{asset('assets/js/dataTables/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/js/dataTables/jquery.dataTables.bootstrap.min.js')}}"></script>
	<script src="{{asset('assets/js/ace.min.js')}}"></script>
	<script src="{{asset('assets/js/ace-elements.min.js')}}"></script>
	{{-- <script src="{{asset('assets/js/jquery-ui.custom.min.js')}}"></script> --}}
	<script src="{{asset('assets/js/jquery.ui.touch-punch.min.js')}}"></script>
	<script src="{{asset('assets/plugins/froiden-helper/helper.js')}}"></script>
	<script type="text/javascript">
		if('ontouchstart' in document.documentElement) document.write("<script src='{{ asset('assets/js/jquery.mobile.custom.min.js') }}'>"+"<"+"/script>");
	</script>

	<script>
        var locale = '{{ App::getLocale() }}';
        var baseUrl = '{{ url('/') }}';
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	</script>
	<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/datatable/js/dataTables.responsive.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/datatable/js/responsive.bootstrap.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/datatable/js/dataTables.buttons.min.js') }}"></script>

    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
	@stack('scripts')

	@yield('scripts')

	@yield('custom-js')
</body>
</html>
