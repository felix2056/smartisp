@extends('layouts.master')

@section('title',__('app.reports'))

@section('styles')
	<link rel="stylesheet" href="{{ asset('assets/css/datepicker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-timepicker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}" />
	<link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}" />
    <style>
        .date-range-picker-height {
            height: 37px !important;
        }
        .input-group>.btn.btn-sm {
            line-height: 29px;
        }
        .form-control {
            height: 36px;
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
						<a href="<?php echo URL::to('reports'); ?>">@lang('app.reports')</a>
					</li>
					<li class="active">@lang('app.listado')</li>
				</ul>
			</div>

			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.reports')
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							@lang('app.listado')
						</small>
					</h1>
				</div>
				<!--start row-->
				<div class="row">

					<div class="col-xl-12 col-lg-12 col-12">
						<div class="card pull-up">
							<div class="card-content">
								<div class="card-body">

									<form class="form-inline center_div" method="get" action="reports">
                                        <div class="form-group">
                                            <select class="form-control" name="admin" id="admin">
                                                <option value="all">All Secretary</option>
                                                @foreach($admins as $admin)
                                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-calendar bigger-110"></i>
											</span>
											<input class="form-control date-range-picker-height" type="text" name="date-range" id="date-range-picker" readonly />
										</div>
										<div class="input-group">
											<button type="button" id="search" class="btn cero_margin btn-sm btn-success"><i class="fa fa-search"></i>
											@lang('app.filter')</button>
											<button type="button" id="searchall" class="btn btn-sm btn-purple"><i class="fa fa-search-plus"></i>
												@lang('app.showAll')
											</button>
										</div>
									</form>

								</div>
							</div>
						</div>
					</div>


					<div class="col-xs-12">
						<div class="row">



							<div class="col-xl-4 col-lg-4 col-12">
								<div class="card pull-up">
									<div class="card-content">
										<div class="card-body">
											<div class="media d-flex">
												<div class="media-body text-left">
													<h3 class="verde_color ajusth3" id="ing">@lang('app.loading') ...</h3>
													<h6>@lang('app.income')</h6>
												</div>
												<div>
													<i class="la la-cloud-download verde_color font-large-2 float-right"></i>
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>

							<div class="col-xl-4 col-lg-4 col-12">
								<div class="card pull-up">
									<div class="card-content">
										<div class="card-body">
											<div class="media d-flex">
												<div class="media-body text-left">
													<h3 class="color_red ajusth3" id="egr">@lang('app.loading') ...</h3>
													<h6>@lang('app.expenses')</h6>
												</div>
												<div>
													<i class="la la-cloud-upload color_red font-large-2 float-right"></i>
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>


							<div class="col-xl-4 col-lg-4 col-12">
								<div class="card pull-up">
									<div class="card-content">
										<div class="card-body">
											<div class="media d-flex">
												<div class="media-body text-left">
													<h3 class="info ajusth3" id="sal">@lang('app.loading') ...</h3>
													<h6>@lang('app.balance')</h6>
												</div>
												<div>
													<i class="la la-money info font-large-2 float-right"></i>
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>






						</div>
						<!--Inicio de tab simple queues-->
						<div class="clearfix">
							<div class="pull-right tableTools-container"></div>
						</div>
						<div class="widget-box widget-color-blue2">
							<div class="widget-header">
								<h5 class="widget-title">@lang('app.allRecords')</h5>
								<div class="widget-toolbar">

									<a data-action="reload" href="#" class="recargar white"><i class="ace-icon fa fa-refresh"></i></a>
									<a data-action="fullscreen" class="white" href="#"><i class="ace-icon fa fa-expand"></i></a>
									<a data-action="collapse" href="#" class="white"><i class="ace-icon fa fa-chevron-up"></i></a>
								</div>
							</div>
							<div class="widget-body">
								<div class="widget-main">
									<!--Contenido widget-->
									<div class="table-responsive">
										<table id="reports-table" class="table table-bordered table-hover" width="100%">
											<thead>
												<tr>
													<th>@lang('app.clientBusinessName')</th>
													<th>@lang('app.secretary')</th>
													<th>@lang('app.detail')</th>
													<th>@lang('app.type')</th>
													<th>@lang('app.date')</th>
													<th>@lang('app.amount')</th>
													<th>@lang('app.remove')</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
						<!--Fin tabla planes simple queues-->

						<hr>
						<div class="row">
							<div class="col-lg-6 col-md-9">
								<div class="panel panel-default">
									<div class="panel-heading">
										<strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
									</div>
									<div class="panel-body" id="totals">

									</div>
								</div>
							</div>
							<div class="col-lg-6 col-md-9">
								<div class="panel panel-default">
									<div class="panel-heading">
										<strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Filtro</font></font></strong>
									</div>
									<div class="panel-body">
										<table class="display supertable table table-striped table-bordered">
											<tbody>
											@foreach($data as $total)
												<tr>
													<td><label class="label label-primary"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> {{ $total }}</font></font></label></td>
													<td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total }}</td>
												</tr>
											@endforeach

											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div><!--end col-->
				</div>
				<!--end row-->
				@include('layouts.modals')
			</div>
		</div>
	</div>
	<input id="val" type="hidden" name="plan" value="">
	<input id="vl" type="hidden" name="perfil" value="">
@endsection

@section('scripts')
	<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
	<script src="{{asset('assets/js/dataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/moment-with-locales.min.js')}}"></script>
	<script src="{{asset('assets/js/date-time/daterangepicker.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
	<script src="{{asset('assets/js/rocket/report1-core.js')}}"></script>
@endsection
