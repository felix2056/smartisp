@extends('layouts.master')

@section('title',__('Myprofile'))

@section('styles')
	<link rel="stylesheet" href="{{ asset('assets/css/alerts.css') }}">
@endsection

@section('content')
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-home home-icon"></i>
						<a href="#">@lang('app.desk')</a>
					</li>
					<li class="active">@lang('app.Myprofile')</li>
				</ul>
			</div>

			<div class="page-content">
				<div class="page-header">
					<h1>
						@lang('app.Myprofile')
					</h1>
				</div>

				<div class="row">
					<div class="col-xs-12">

						<div class="row">
							<div class="col-xs-12 col-sm-3 center">
								<span class="profile-picture">
									@if(Auth::user()->photo =='none')
									<img class="editable img-responsive" alt="Alex's Avatar" id="avatar2" src="assets/avatars/profile-pic.jpg" />
									@else
									<img class="editable img-responsive" alt="" src="assets/avatars/{{Auth::user()->photo}}" />

									@endif
								</span>
								<div class="space space-4"></div>
							</div>
							<div class="col-xs-12 col-sm-9">
								<h4 class="blue">
									<span class="middle">{{Auth::user()->name}}</span>
									<span class="label label-purple arrowed-in-right">
										<i class="ace-icon fa fa-circle smaller-80 align-middle"></i>
										online
									</span>
								</h4>
								<form id="uploadphoto" action="" method="post" enctype="multipart/form-data">		<div class="profile-user-info">
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.username') </div>

										<div class="profile-info-value">
											@if(Auth::user()->level=='ad')
											<div class="col-sm-6">
												<input type="text" class="form-control" maxlength="25" name="username" id="username" value="{{Auth::user()->username}}">
											</div>
											@else
											<span>{{Auth::user()->username}}</span>
											@endif
										</div>
									</div>
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.name') </div>

										<div class="profile-info-value">
											<div class="col-sm-6">

												<input type="text" class="form-control" name="name" maxlength="40" id="name" value="{{Auth::user()->name}}">
											</div>
										</div>
									</div>
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.email') </div>

										<div class="profile-info-value">
											<div class="col-sm-6">

												<input type="email" class="form-control" maxlength="60" name="email" id="email" value="{{Auth::user()->email}}">
											</div>
										</div>
									</div>
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.telephone') </div>

										<div class="profile-info-value">
											<div class="col-sm-6">
												<input type="text" class="form-control" maxlength="25" name="phone" id="phone" value="{{Auth::user()->phone}}">
											</div>
										</div>
									</div>
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.new') @lang('app.password') </div>

										<div class="profile-info-value">
											<div class="col-sm-6">
												<input type="password" maxlength="50" class="form-control" name="password" id="password">
											</div>
										</div>
									</div>
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.Repeatpassword') </div>

										<div class="profile-info-value">
											<div class="col-sm-6">
												<input type="password" class="form-control" maxlength="50" name="password_confirmation" id="password2">
											</div>
										</div>
									</div>
								</div>
								<div class="hr hr-8 dotted"></div>
								<div class="profile-user-info">
									<div class="profile-info-row">
										<div class="profile-info-name"> @lang('app.ChangeImage') </div>
										<div class="profile-info-value">
											<div class="col-sm-6">
												<input type="file" class="form-control" name="file" id="file">																</div>
											</div>
										</div>
										<div class="profile-info-row">
											<div class="profile-info-name">
												@lang('app.registered')
											</div>

											<div class="profile-info-value">
												<?php
												$fe = Auth::user()->created_at;
												echo date('d-m-Y',strtotime($fe));
												?>
											</div>
										</div>

										<div class="profile-info-row">
											<div class="profile-info-name">
												@lang('app.type')
											</div>

											<div class="profile-info-value">
												@if(Auth::user()->level=='ad')
												@lang('app.adminstrator')
												@else
												@lang('app.username')
												@endif
											</div>
										</div>
										<div class="profile-info-row">
											<div class="profile-info-name">
											</div>
											<div class="profile-info-value">
												<button type="submit" id="savebtnProfile" class="btn btn-sm btn-primary" data-loading-text="@lang('app.saving')..."><i class="ace-icon fa fa-floppy-o bigger-110"></i> Guardar Cambios</button>
											</div>
										</div>
									</div></form>
								</div>
							</div>
						</div>
					</div>
					@include('layouts.modals')
				</div>
			</div>
		</div>
@endsection

@section('scripts')
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/rocket/profile-core.js')}}"></script>
@endsection
