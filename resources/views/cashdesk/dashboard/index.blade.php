@extends('layouts.cashdesk.master')

@section('title',$company)

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
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
                <div class="row">
                    <div class="col-xs-4">
                        <div class="card pull-up left-border info_c">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left ">
                                            <h3 class="info ajusth3" id="stRouter">Balance</h3>
                                            <h6 class="info_c_text">{{ auth()->guard('cashdesk')->user()->balance }}</h6>
                                        </div>
                                        <div>
                                            <i class="fa fa-dollar icon_i info font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="card pull-up left-border info_c">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="media-body text-left ">
                                            <h3 class="info ajusth3" id="stRouter">{{ __('app.payments') }}</h3>
                                            <h6 class="info_c_text">{{ $payments }}</h6>
                                        </div>
                                        <div>
                                            <i class="fa fa-dollar icon_i info font-large-2 float-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
@endsection


