@extends('layouts.master')

@section('title',__('app.networks'))

@section('styles')
	<link rel="stylesheet" href="{{ asset('assets/css/waiting.css') }}" />
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
						<a href="<?php echo URL::to('networks'); ?>">@lang('app.IPNetworks')</a>
					</li>
					<li class="active">@lang('app.list')</li>
				</ul>
			</div>

			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.IPNetworks')
						<small>
							<i class="ace-icon fa fa-angle-double-right"></i>
							@lang('app.list')
						</small>
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#add"><i class="icon-plus"></i> @lang('app.new')</button>
                    </h1>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="row">
							<div class="col-xs-12 col-sm-12 widget-container-col">
								<div class="widget-box widget-color-blue2">
									<div class="widget-header">
										<h5 class="widget-title">@lang('app.all') @lang('app.IPNetworks')</h5>
										<div class="widget-toolbar">
											<div class="widget-menu">
												<a href="#" data-action="settings" data-toggle="dropdown" class="white">
													<i class="ace-icon fa fa-bars"></i>
												</a>
												<ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
													<li>
														<a href="#" data-toggle="modal" class="peref" data-target="#add"><i class="fa fa-plus-circle"></i> @lang('app.add') IP/@lang('app.net')</a>
													</li>
												</ul>
											</div>
											<a href="#" data-action="fullscreen" class="white">
												<i class="ace-icon fa fa-expand"></i>
											</a>
											<a href="#" data-action="reload" class="white recargar">
												<i class="ace-icon fa fa-refresh"></i>
											</a>
											<a href="#" data-action="collapse">
												<i class="ace-icon fa fa-chevron-up white"></i>
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
							<div class="modal fade" id="info-router" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
											<h4 class="modal-title" id="myModalLabel"><i class="fa fa-info-circle"></i>
												@lang('app.IPInformation')/@lang('app.net') <span id="networkd"></span></h4>
											</div>
											<div class="modal-body">

												<!--inicio info-->
												<div class="profile-user-info profile-user-info-striped">
													<div class="profile-info-row">
														<div class="profile-info-name"> @lang('app.direction') </div>
														<div class="profile-info-value">
															<span class="editable" id="address"></span>
														</div>
													</div>
													<div class="profile-info-row">
														<div class="profile-info-name">@lang('app.mask') bits</div>
														<div class="profile-info-value">
															<span class="editable" id="maskbit"></span>
														</div>
													</div>
													<div class="profile-info-row">
														<div class="profile-info-name"> @lang('app.mask') @lang('app.net') </div>

														<div class="profile-info-value">
															<span class="editable" id="maskadd"></span>
														</div>
													</div>
													<div class="profile-info-row">
														<div class="profile-info-name">@lang('app.class')</div>

														<div class="profile-info-value">
															<span class="editable" id="classip"></span>
														</div>												</div>
														<div class="profile-info-row">
															<div class="profile-info-name">@lang('app.IPRange')</div>
															<div class="profile-info-value">
																<span class="editable" id="hostrange"></span>
															</div>
														</div>
														<div class="profile-info-row">
															<div class="profile-info-name">Broadcast</div>
															<div class="profile-info-value">
																<span class="editable" id="broadcast"></span>
															</div>
														</div>
														<div class="profile-info-row">
															<div class="profile-info-name">Gateway</div>
															<div class="profile-info-value">
																<span class="editable" id="gateway"></span>
															</div>
														</div>
														<div class="profile-info-row">
															<div class="profile-info-name">@lang('app.availableIPs')</div>

															<div class="profile-info-value">
																<span class="editable" id="totalips"></span>
															</div>
														</div>
														<div class="profile-info-row">
															<div class="profile-info-name">@lang('app.binary')</div>
															<div class="profile-info-value">
																<span class="editable" id="binary"></span>
															</div>												</div>

														</div>
														<!--fin info-->


													</div>
												</div>
											</div>
										</div>
										<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
											<div class="modal-dialog">
												<div class="modal-content">
													<div class="modal-header">
														<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
														<h4 class="modal-title" id="myModalLabel"><i class="fa fa-sitemap"></i> @lang('app.addNetworkIP')</h4>
													</div>
													<div class="modal-body">
														<form class="form-horizontal" role="form" id="formaddnetwork">
															<div class="form-group">
																<label for="name" class="col-sm-2 control-label">@lang('app.name')</label>
																<div class="col-sm-10">
																	<input type="text" name="name" class="form-control" id="name"  maxlength="30">
																</div>
															</div>
															<div class="form-group">
																<label for="inputEmail3" class="col-sm-2 control-label">@lang('app.net')</label>
																<div class="col-sm-7">
																	<input type="text" class="form-control ip_address" name="network" id="newnetwork" maxlength="15" placeholder="0.0.0.0">
																	<p class="help-block">@lang('app.forExample'): 192.168.1.0 /24</p>
																</div>
																<div class="col-sm-3">
																	<select class="form-control" name="mask" id="mask">


																		<option value="30">/30</option>
																		<option value="29">/29</option>
																		<option value="28">/28</option>
																		<option value="27">/27</option>
																		<option value="26">/26</option>
																		<option value="25">/25</option>
																		<option value="24" selected>/24</option>


																		<option value="23">/23</option>
																		<option value="22">/22</option>
																		<option value="21">/21</option>
																		<option value="20">/20</option>
																		<option value="19">/19</option>
																		<option value="18">/18</option>


																	</select>
																</div>

															</div>

															<div class="form-group">
																<label for="inputEmail3" class="col-sm-2 control-label">@lang('app.type')</label>
																<div class="col-sm-10">
																	<select class="form-control" name="type" id="routing">
																		<option value="static">@lang('app.static')</option>
																		<option value="pool">Pool</option>
																	</select>
																</div>

															</div>



															<div class="form-group">
																<label for="regpay" class="col-sm-2 control-label"><p class="text-success"><i class="fa fa-files-o"></i> @lang('app.copy')</p></label>
																<div class="col-sm-10">
																	<div class="checkbox">
																		<label>
																			<input name="copy" type="checkbox" class="ace" id="copy" />
																			<span class="lbl"></span>
																		</label>
																	</div>
																</div>
															</div>


														</form>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
														<button type="button" class="btn btn-primary" id="addbtnnetwork" data-loading-text="@lang('app.saving')..." autocomplete="off"><i class="fa fa-floppy-o"></i>
														@lang('app.save')</button>
													</div>
												</div>
											</div>
										</div>

										<div class="modal fade bs-edit-modal-lg" id="edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
											<div class="modal-dialog modal-lg">
												<div class="modal-content">
													<div class="modal-header">
														<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
														<h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span id="load"><i class="fa fa-cog fa-spin"></i> @lang('app.loading') </span>Editar IP/@lang('app.net') </h4>
													</div>
													<div class="modal-body">
														<form class="form-horizontal" role="form" id="formeditnetwork">
															<div class="form-group">
																<label for="name" class="col-sm-2 control-label">@lang('app.name')</label>
																<div class="col-sm-10">
																	<input type="text" name="name" class="form-control" id="editname"  maxlength="30">
																</div>
															</div>
															<div class="form-group">
																<label for="inputEmail3" class="col-sm-2 control-label">@lang('app.net')</label>
																<div class="col-sm-7">
																	<p class="form-control-static" id="edit_address"></p>
																</div>
															</div>

															<div class="form-group">
																<label for="inputEmail3" class="col-sm-2 control-label">@lang('app.type')</label>
																<div class="col-sm-10">
																	<select class="form-control" name="edit_type" id="edit_routing">
																		<option value="static">@lang('app.static')</option>
																		<option value="pool">Pool</option>
																	</select>
																</div>

															</div>
															<input id="val" type="hidden" name="netid">
														</form>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
															<button type="button" class="btn btn-primary" id="editbtnnetwork" data-loading-text="@lang('app.saving')..." autocomplete="off"><i class="fa fa-floppy-o"></i> @lang('app.save')</button>
														</div>
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
@endsection

@section('scripts')
	<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/jquery.mask.min.js')}}"></script>
	<script src="{{asset('assets/js/rocket/networks-core.js')}}"></script>
	<script src="{{asset('assets/js/rocket/tlan.js')}}"></script>
    {!! $dataTable->scripts() !!}
@endsection
