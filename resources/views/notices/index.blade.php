@extends('layouts.master')

@section('title',__('app.emails'))

@section('styles')
	<link rel="stylesheet" href="{{ asset('assets/css/chosen.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
@endsection

@section('content')
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-desktop desktop-icon"></i>
						<a href="{{ URL::to('admin') }}">{{ __('app.desk') }}</a>
					</li>
					<li>
						<a href="{{ URL::to('advice') }}">{{ __('app.emails') }}</a>
					</li>
					<li class="active">{{__('app.listado')}}</li>
				</ul>
			</div>

			<div class="page-content">
				<div class="page-header">
					<h1>
						{{ __('app.emails') }}
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							{{ __('app.listado') }}
						</small>
                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addadv" id="new"><i class="icon-plus"></i> {{ __('app.new') }} {{ __('app.email') }}</button>
                    </h1>
				</div>
				<div class="row">

					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-12 col-sm-12 widget-container-col">
								<div class="widget-box widget-color-blue2">
									<div class="widget-header">
										<h5 class="widget-title">{{ __('app.allEmails') }}</h5>
										<div class="widget-toolbar">
											<div class="widget-menu">
												<a href="#" data-action="settings" data-toggle="dropdown" class="white">
													<i class="ace-icon fa fa-bars"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
													<li>
														<a href="#" data-toggle="modal" class="peref" data-target="#addadv"><i class="fa fa-plus-circle"></i> {{ __('app.send') }} {{ __('app.email') }}</a>
													</li>
												</ul>
											</div>
											<a href="#" data-action="fullscreen" class="white">
												<i class="ace-icon fa fa-expand"></i>
											</a>
											<a href="#" data-action="reload" class="white recargar">
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


							<div class="modal fade" id="addadv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span></button>
											<h4 class="modal-title" id="myModalLabel"><i class="fa fa-bullhorn"></i>
											{{ __('app.send') }} {{ __('app.new') }} {{ __('app.email') }}</h4>
										</div>
										<div class="modal-body" id="winnew">
											<form class="form-horizontal" id="sendnewadv">
												<div class="form-group">
													<label for="slcrouter" class="col-sm-2 control-label">{{ __('app.router') }}</label>
													<div class="col-sm-10">
														<select class="form-control" name="router_id" id="slcrouter"></select>
													</div>
												</div>
												<div class="form-group">
													<label for="name_adv" class="col-sm-2 control-label">{{ __('app.name') }}</label>
													<div class="col-sm-10">
														<input type="text" class="form-control" name="name" id="name_adv">
													</div>
												</div>
												<div class="form-group" id="seltype">
													<label for="slctype" class="col-sm-2 control-label">{{ __('app.type') }}</label>
													<div class="col-sm-10">
														<select class="form-control" id="slctype" name="typetem">

															<option value="email" selected>{{ __('app.email') }}</option>
														</select>
													</div>
												</div>
												<div class="form-group" id="sltemplate">
													<label for="type_temp" class="col-sm-2 control-label">{{ __('app.templates') }}</label>
													<div class="col-sm-10">
														<select class="form-control" id="type_temp" name="template"></select>
														<br>
														<a href="" class="btn btn-success btn-xs" id="btnpreview" target="_blank"><i class="fa fa-desktop"></i>
														{{ __('app.preview') }}</a>
													</div>
												</div>

												<div class="form-group" id="lsclient">
													<label for="ms" class="control-label col-xs-12 col-sm-2">{{ __('app.send') }} a</label>
													<div class="col-xs-12 col-sm-10">
														<select id="ms" class="multiselect" multiple="" name="clients[]"></select>
													</div>
												</div>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-default" data-dismiss="modal">{{ __('app.close') }}</button>
											<button type="button" class="btn btn-primary" id="btnSend" data-loading-text="Enviando..." autocomplete="off"><i class="fa fa-share-square"></i>
											{{ __('app.send') }}</button>
										</div>
									</div>
								</div>
							</div>


							@include('layouts.modals')
						</div>
					</div>
				</div>
			</div></div>    </div>
	<input id="val" type="hidden" name="register" value="">
@endsection

@section('scripts')
	<script>
		var language = '{{ __('app.datatable') }}';
        // global app configuration object
        var config = {
            routes: {
                send: "{{ route('advice.send') }}",
                send: "{{ route('advice.send') }}",
            }
        };
	</script>
	<script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
	<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
	<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/moment.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-multiselect.min.js')}}"></script>
	<script src="{{asset('assets/js/bootstrap-multiselect-collapsible-groups.js')}}"></script>
	<script src="{{asset('assets/js/rocket/notices-core.js')}}"></script>
    {!! $dataTable->scripts() !!}
@endsection
