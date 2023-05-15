@extends('layouts.cliente.master')

@section('title', "documents")

@section('content')
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-desktop desktop-icon"></i>
						<a href="<?php echo URL::to('portal'); ?>">@lang('app.desk')</a>
					</li>

					<li>
						<a href="<?php echo URL::to('portal/documents'); ?>">@lang('app.documents')</a>
					</li>

					<li class="active">@lang('app.list')</li>
				</ul>
			</div>
			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.documents')
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							@lang('app.list')
						</small>
					</h1>

				</div>
				<!--start row-->
				<div class="row">
					<div class="col-sm-12">
						<!--Inicio tabla planes simple queues-->
						<div class="widget-box widget-color-blue2">
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
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script src="{{asset('assets/js/rocket/clientNotifications2-core.js')}}"></script>
	<script src="{{asset('assets/js/ace-elements.min.js')}}"></script>
	<script src="{{asset('assets/js/ace.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/bootstrap-timepicker.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
	<script src="{{asset('assets/js/bootbox.min.js')}}"></script>

{{--	<script src="{{asset('assets/js/rocket/documentsClient-core.js')}}"></script>--}}
    {!! $dataTable->scripts() !!}
@endsection
