@extends('layouts.cashdesk.master')

@section('title',$company)

@section('styles')

    <link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/new_template.css')}}">
    <link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/css/components.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/css/core/menu/menu-types/vertical-menu.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/newTemplate/app-assets/fonts/simple-line-icons/style.min.css')}}">
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i%7CQuicksand:300,400,500,700" rel="stylesheet">
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
                            <div class="col-xs-12 col-sm-9">
                                <h4 class="blue">
                                    <span class="middle"><?php echo $user->name; ?></span>
                                    @if($user->status=='1')
                                        <span class="label label-purple arrowed-in-right">
											<i class="ace-icon fa fa-circle smaller-80 align-middle"></i>
											@lang('app.active')
										</span>
                                    @else
                                        <span class="label label-danger arrowed-in-right">
											<i class="ace-icon fa fa-circle smaller-80 align-middle"></i>
											@lang('app.suspended')
										</span>
                                    @endif

                                </h4>
                                <form id="uploadphoto" method="post" enctype="multipart/form-data">
                                    <div class="profile-user-info">

                                        <div class="profile-info-row">
                                            <div class="profile-info-name"> @lang('app.fullName') </div>

                                            <div class="profile-info-value">
                                                <div class="col-sm-6">

                                                    <input type="text" class="form-control" maxlength="40" id="name"
                                                           disabled value="<?php echo $user->name; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-info-row">
                                            <div class="profile-info-name"> Email</div>

                                            <div class="profile-info-value">
                                                <div class="col-sm-6">

                                                    <input type="email" class="form-control" maxlength="60" id="email"
                                                           disabled value="<?php echo $user->email; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-info-row">
                                            <div class="profile-info-name"> @lang('app.telephone') </div>

                                            <div class="profile-info-value">
                                                <div class="col-sm-6">
                                                    <input type="text" class="form-control" maxlength="25" id="phone"
                                                           disabled value="<?php echo $user->phone; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-info-row">
                                            <label for="language"
                                                   class="profile-info-name">@lang('app.language')</label>
                                            <div class="profile-info-value">
                                                <div class="col-sm-6">
                                                    <select class="form-control" name="language"
                                                            id="language">
                                                        @foreach($languages as $language)
                                                            <option value="{{$language->language_code}}" {{ $user->locale === $language->language_code ? 'selected' : '' }}>{{ $language->language_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="profile-info-row">
                                            <div class="profile-info-name"> @lang('app.new') @lang('app.password') </div>

                                            <div class="profile-info-value">
                                                <div class="col-sm-6">
                                                    <input type="password" maxlength="50" class="form-control"
                                                           name="password" id="password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-info-row">
                                            <div class="profile-info-name"> @lang('app.Repeatpassword') </div>

                                            <div class="profile-info-value">
                                                <div class="col-sm-6">
                                                    <input type="password" class="form-control" maxlength="50"
                                                           name="password_confirmation" id="password2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="profile-info-row">
                                            <div class="profile-info-name">
                                            </div>
                                            <div class="profile-info-value">
                                                <button type="submit" id="savebtnProfile"
                                                        class="btn btn-sm btn-primary"><i
                                                            class="ace-icon fa fa-floppy-o bigger-110"></i> @lang('app.SaveChanges')
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!--[if !IE]> -->
    <script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <!-- <![endif]-->
    <!--[if IE]>
    <script src="{{asset('assets/js/libs/jquery1.11/jquery.min.js')}}"></script>
    <![endif]-->
    <!--[if !IE]> -->
    <script type="text/javascript">
        window.jQuery || document.write("<script src='{{asset('assets/js/jquery.min.js')}}'>" + "<" + "/script>");
    </script>
    <!-- <![endif]-->
    <!--[if IE]>
    <script type="text/javascript">
        window.jQuery || document.write("<script src='{{asset('assets/js/jquery1x.min.js')}}'>" + "<" + "/script>");
    </script>
    <![endif]-->
    <script type="text/javascript">
        if ('ontouchstart' in document.documentElement) document.write("<script src='{{asset('assets/js/jquery.mobile.custom.min.js')}}'>" + "<" + "/script>");
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
    <script src="{{ asset('assets/js/rocket/checkUpdate-pages.js') }}"></script>
    <script src="{{ asset('assets/js/rocket/clientNotifications2-core.js') }}"></script>
    <script src="{{ asset('assets/js/ace-elements.min.js') }}"></script>
    <script src="{{ asset('assets/js/ace.min.js') }}"></script>
    <script src="{{ asset('assets/js/rocket/profileCashdesk-core.js') }}"></script>
@endsection


