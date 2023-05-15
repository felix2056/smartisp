@extends('layouts.cliente.master')

@section('title', "Tickets")

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
						<a href="<?php echo URL::to('portal/tickets'); ?>">@lang('app.tickets')</a>
					</li>

					<li class="active">@lang('app.list')</li>
				</ul>
			</div>
			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.tickets')
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							@lang('app.list')
						</small>

						<!--Inicio de tab simple queues-->
						<button class="btn btn-sm btn-success newcl" data-toggle="modal" data-target="#add"><i class="icon-plus"></i> @lang('app.new')</button>
					</h1>

				</div>
				<!--start row-->
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-12">
						<div class="card pull-up">
							<div class="card-content">
								<div class="card-body">

									<form class="form-inline center_div" id="filterForm">
										<div class="form-group">
											<select class="form-control" name="ticket_status" id="ticket_status">
												<option value="all" selected> {{ __('app.all') }} {{ __('app.status') }} </option>
												@foreach($status as $st)
													<option value="{{$st}}">@lang('app.'.$st)</option>
												@endforeach
											</select>
										</div>

										<div class="form-group">
											<select class="form-control" name="ticket_type" id="ticket_type">
												<option value="all" selected>{{ __('app.all') }} {{ __('app.type') }} </option>
												@foreach($types as $type)
													<option value="{{$type}}">@lang('app.'.$type)</option>
												@endforeach
											</select>
										</div>

										<div class="input-group">
											<button type="button" id="search"
													class="btn cero_margin btn-sm btn-success"><i
														class="fa fa-search"></i>
												@lang('app.filter')</button>
											<button type="button" id="searchall" class="btn btn-sm btn-purple"><i
														class="fa fa-search-plus"></i>
												@lang('app.showAll')
											</button>
										</div>
									</form>

								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<!--Inicio tabla planes simple queues-->
						<div class="widget-box widget-color-blue2">
							<div class="widget-header">
								<h5 class="widget-title">@lang('app.MySupportTickets')</h5>
								<div class="widget-toolbar">
									<div class="widget-menu">
										<a href="#" data-action="settings" data-toggle="dropdown" class="white">
											<i class="ace-icon fa fa-bars"></i>
										</a>
										<ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
											<li>
												<a href="#" data-toggle="modal" class="peref" data-target="#add"><i class="fa fa-plus-circle"></i> @lang('app.add') @lang('app.new') ticket</a>  </li>
										</ul>
									</div>
									<a data-action="reload" href="#" class="recargar white"><i class="ace-icon fa fa-refresh"></i></a>
									<a data-action="fullscreen" class="white" href="#"><i class="ace-icon fa fa-expand"></i></a>
									<a data-action="collapse" href="#" class="white"><i class="ace-icon fa fa-chevron-up"></i></a>
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

				<!--Incio modal añadir nuevo ticket-->
				<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
								<h4 class="modal-title" id="myModalLabel"><i class="fa fa-ticket"></i>
									@lang('app.openNewSupportTicket')  <i class="fa fa-spinner fa-spin fa-fw" id="loads"></i></h4>
							</div>
							<div class="modal-body">
								<form class="form-horizontal" role="form" id="ticketform" method="post" enctype="multipart/form-data">
									<div class="form-group">
										<label for="name" class="col-sm-2 control-label">@lang('app.name')</label>
										<div class="col-sm-10">
											<input type="text" name="name" class="form-control" value="{{$name}}" id="disabledInput" disabled  maxlength="30">
										</div>
									</div>
									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.affair')</label>
										<div class="col-sm-10">
											<input type="text" name="subject" class="form-control" id="subject" required>
										</div>
									</div>

									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.For')</label>
										<div class="col-sm-10">
											<select class="form-control" name="section">
												<option value="administracion">@lang('app.Administration')</option>
												<option value="tecnico">@lang('app.technicalSupport')</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.type')</label>
										<div class="col-sm-10">
											<select class="form-control" name="type">
												@foreach($types as $type)
													<option value="{{$type}}">@lang('app.'.$type)</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.priority')</label>
										<div class="col-sm-10">
											<select class="form-control" name="priority">
												@foreach($priorities as $priority)
													<option value="{{$priority}}">@lang('app.'.$priority)</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.message')</label>
										<div class="col-sm-10">
											<textarea class="form-control" name="message" rows="3" required></textarea>
										</div>
									</div>

									<div class="form-group">
										<label for="subject" class="col-sm-2 control-label">@lang('app.attached')</label>
										<div class="col-sm-10">
											<input type="file" class="form-control" name="file" id="file">
										</div>
									</div>



							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
								<button type="submit" class="btn btn-primary" id="addbtnticket" data-loading-text="@lang('app.saving')..." autocomplete="off"><i class="fa fa-floppy-o"></i>
									@lang('app.save')</button>
								</form>
							</div>
						</div>
					</div>
				</div>
				<!--Fin de modal añadir plan simple queues-->

				<!--Inicio de modal editar ticket-->
				<div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
								<h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span id="load2"><i class="fa fa-cog fa-spin"></i> @lang('app.loading')</span> @lang('app.seeTicket')</h4>
							</div>
							<div class="modal-body" id="winedit">



								<div id="accordion2" class="accordion-style1 panel-group">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title">
												<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion2" href="#advedit">
													<i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
													<i class="fa fa-pencil"></i>

													&nbsp;@lang('app.Answer')
												</a>
											</h4>
										</div>
										<div class="panel-collapse collapse" id="advedit">
											<div class="panel-body">

												<form class="form-horizontal" role="form" id="resticketform" method="post" enctype="multipart/form-data">
													<div class="form-group">
														<label for="name" class="col-sm-2 control-label">@lang('app.name')</label>
														<div class="col-sm-10">
															<input type="text" name="name" class="form-control" value="{{$name}}" id="disabledInput" disabled  maxlength="30">
														</div>
													</div>

													<div class="form-group">
														<label for="subject" class="col-sm-2 control-label">@lang('app.message')</label>
														<div class="col-sm-10">
															<textarea class="form-control" name="message" id="menrep" rows="3" required></textarea>
														</div>
													</div>

													<div class="form-group">
														<label for="subject" class="col-sm-2 control-label">@lang('app.attached')</label>
														<div class="col-sm-10">
															<input type="file" class="form-control" name="efile" id="efile">
														</div>
													</div>
													<div class="form-group">
														<div class="col-sm-8">
															<div class="pull-right">
																<a data-toggle="collapse" class="btn btn-default" data-parent="#accordion2" href="#advedit">@lang('app.cancel')</a>
																<button type="submit" class="btn btn-primary">@lang('app.send')</button>
															</div>
														</div>
													</div>
													<input id="val" type="hidden" name="ticket" value="">
											</div>
										</div>
									</div>
								</div>
								</form>

								<div id="navticket"><span></span></div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>

							</div>
						</div>
					</div>
				</div>
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

	<script src="{{asset('assets/js/rocket/ticketsClient-core.js')}}"></script>
    {!! $dataTable->scripts() !!}

	<script>
        $(function () {

            $('#ticket-table').on('preXhr.dt', function (e, settings, data) {

                var status = $('#ticket_status').val();
                var type = $('#ticket_type').val();

                data['status'] = status;
                data['type'] = type;
            });

            //funcion para recuperar todos los registros
            $(document).on('click', '#searchall', function (event) {
                $('#filterForm').trigger("reset");
                window.LaravelDataTables["ticket-table"].draw()
            });
            $(document).on("click", "#search", function () {
                window.LaravelDataTables["ticket-table"].draw()
            });

        });
	</script>
@endsection
