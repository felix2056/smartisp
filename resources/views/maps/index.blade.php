@extends('layouts.master')

@section('title', __('app.map').' '. __('app.clients'))

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"
   integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ=="
   crossorigin=""/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw-src.css" integrity="sha512-vJfMKRRm4c4UupyPwGUZI8U651mSzbmmPgR3sdE3LcwBPsdGeARvUM5EcSTg34DK8YIRiIo+oJwNfZPMKEQyug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="{{asset('assets/plugins/colorpicker/css/colorpicker.css')}}" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="//unpkg.com/leaflet-gesture-handling/dist/leaflet-gesture-handling.min.css" type="text/css">
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
<style>
	#change_line_color .modal-content, #change_marker_icon .modal-content {
		border: 0;
	}
	#change_line_color .modal-dialog, #change_marker_icon .modal-dialog {
		width: 386px;
	}
	#change_line_color .btn-success {
		margin-bottom: 4px;
		margin-right: 4px;
	}
	#change_marker_icon .icons > ul > li {
		list-style-type: none;
		cursor: pointer;
	}
	#change_marker_icon .icons > ul > li > *{
		border: 2px solid transparent;
		padding: 2px;
	}
	#change_marker_icon .icons > ul > li > *:hover, #change_marker_icon .icons > ul > li.selected > * {
		border: 2px solid #428bca;
	}
	.size-48 {
		font-size: 48px;
	}
	.size-32 {
		font-size: 32px;
	}
	.remixicon.leaflet-marker-icon {
		background-color: transparent;
		border: none;
	}
	.remixicon.iconanchor32 {
		margin-left: -16px !important;
    	margin-top: -32px !important;
	}
	.remixicon.iconanchor48 {
		margin-left: -24px !important;
    	margin-top: -48px !important;
	}
</style>
@endsection

@section('content')
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-home home-icon"></i>
						<a href="{{URL::to('admin')}}">{{ __('app.desk') }}</a>
					</li>
					<li>
						<a href="{{URL::to('clients')}}">{{ __('app.clients') }}</a>
					</li>
					<li class="active">{{ __('app.map') }} {{ __('app.clients') }}</li>
				</ul>
			</div>



			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.map_types.' . $global->map_type ?? '')
					</h1>
				</div>

				<div class="row">
					<div class="col-xs-12">
						<div class="tabbable">
							<ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
								<li class="active">
									<a data-toggle="" href="{{ route('client.maps') }}">
										<i class="blue ace-icon fa fa-map bigger-120"></i>
										{{ __('app.map') }} {{ __('app.clients') }}
									</a>
								</li>
								<li class="">
									<a data-toggle="" href="{{ route('client.maps.caja') }}">
										<i class="blue ace-icon fa fa-map bigger-120"></i>
										{{ __('app.map') }}  NAPs
									</a>
								</li>
							</ul>
							<div class="tab-content no-border padding-24">
								<div id="faq-tab-1" class="tab-pane fade in active">

									<form action="#" class="form-horizontal">
										<div class="form-horizontal">
											<div class="form-group">
												<label class="col-sm-1 control-label"><span class="blanco">{{ __('app.router') }}</span>:</label>
												<div class="col-sm-5">
													<select class="form-control" id="router">
														<option value="0" selected>{{ __('app.everybody') }}</option>
													</select>
												</div>
											</div>
										</div>
									</form>

									<br>

									<div class="map">
										<div id="map-default" style="with:420px;height:850px;" data-map-type="{{ $global->map_type }}"></div>
									</div>


									<br><br>

								</div>
								<div id="faq-tab-2" class="tab-pane fade">
									<div id="plans" class="col-xs-10">
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
@endsection

@section('scripts')
	@if($map!='0')
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&key={{$map}}&libraries=places"></script>
		<script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"
   integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ=="
   crossorigin=""></script>
   <script src="{{asset('assets/plugins/leaflet/bouncemarker.js')}}"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js" integrity="sha512-ozq8xQKq6urvuU6jNgkfqAmT7jKN2XumbrX1JiB3TnF7tI48DPI4Gy1GXKD/V3EExgAs1V+pRO7vwtS1LHg0Gw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	@endif
	<script src="{{asset('assets/plugins/colorpicker/js/colorpicker.js')}}"></script> 
	<script src="{{asset('assets/js/rocket/maps-core.js')}}"></script>
	<script src="//unpkg.com/leaflet-gesture-handling"></script>
	<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
@endsection
