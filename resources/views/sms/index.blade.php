@extends('layouts.master')
@section('title','SMS')
@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/chosen.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-multiselect.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/js/Loading/css/jquery.loadingModal.min.css') }}">
<style>
   .sent-card .card {
   float: right;
   margin-left: 40px;
   margin-top : 15px;
   }
   .received-card .card {
   float: left;
   margin-right: 20px;
   margin-left: 15px;
   }
   .sent-card .card-body {
   width: auto !important;
   background: #ffbf60;
   border-radius: 10px 10px 0 10px;
   padding: 4px 10px 7px !important;
   text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
   word-wrap: break-word;
   }
   .received-card .card-body {
   width: auto !important;
   padding: 4px 10px 7px !important;
   border-radius: 10px 10px 10px 0;
   background: #e7e7e7;
   text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
   word-wrap: break-word;
   }
   .sent-card p.small, .received-card p.small {
      margin-bottom: 0;
   }
   .sent-card p.message, .received-card p.message {
    text-align: left;
   }
   .list-unstyled_msg {
   width: 100px;
   overflow: hidden;
   white-space: nowrap;
   text-overflow: ellipsis;
   }

   .reply_mes{
      display: none;
   }

   .fa-2x {
   font-size: 1.5em !important;
   }
   .heading {
   padding: 10px 16px 10px 15px;
   height: 60px;
   width: 100%;
   background-color: #f7f7f7;
   z-index: 1000;
   }
   .heading-avatar {
   padding: 0 !important;
   cursor: pointer;
   }
   .heading-avatar-icon img {
   border-radius: 50%;
   height: 40px;
   width: 40px;
   }
   .heading-name {
   padding: 0 !important;
   cursor: pointer;
   }
   .heading-name-meta {
   font-weight: 700;
   font-size: 100%;
   padding: 5px;
   padding-bottom: 0;
   text-align: left;
   text-overflow: ellipsis;
   white-space: nowrap;
   color: #000;
   display: block;
   }
   .heading-online {
   display: none;
   padding: 0 5px;
   font-size: 12px;
   color: #93918f;
   }
   .heading-compose {
   padding: 0;
   }
   .heading-compose i {
   text-align: center;
   padding: 5px;
   color: #93918f;
   cursor: pointer;
   }
   
   .searchBox {
   margin: 0 !important;
   height: 60px;
   width: 100%;
   }
   .searchBox-inner {
   height: 100%;
   width: 100%;
   padding: 10px !important;
   background-color: #fbfbfb;
   }

   .searchBox-inner input:focus {
   outline: none;
   border: none;
   box-shadow: none;
   }
   .sideBar {
   padding: 0 !important;
   margin: 0 !important;
   background-color: #fff;
   overflow-y: auto;
   border: 1px solid #f7f7f7;
   height: calc(100% - 120px);
   }
   .sideBar-body {
   position: relative;
   padding: 10px !important;
   border-bottom: 1px solid #f7f7f7;
   height: 72px;
   margin: 0 !important;
   cursor: pointer;
   }
   .sideBar-body:hover {
   background-color: #f2f2f2;
   }
   .sideBar-avatar {
   text-align: center;
   padding: 0 !important;
   }
   .avatar-icon img {
   border-radius: 50%;
   height: 49px;
   width: 49px;
   }
   .sideBar-main {
   padding: 0 !important;
   }
   .sideBar-main .row {
   padding: 0 !important;
   margin: 0 !important;
   }
   .sideBar-name {
   padding: 10px !important;
   }
   .name-meta {
   font-size: 100%;
   padding: 1% !important;
   text-align: left;
   text-overflow: ellipsis;
   white-space: nowrap;
   color: #000;
   }
   .sideBar-time {
   padding: 10px !important;
   }
   .time-meta {
   text-align: right;
   font-size: 12px;
   padding: 1% !important;
   color: rgba(0, 0, 0, .4);
   vertical-align: baseline;
   }

   .numberCircle {
    border-radius: 50%;
    width: 18px;
    height: 18px;
    background: #ffa217;
    border: 3px solid #ffa217;
    color: white;
    text-align: center;
    font: 11px white, sans-serif;
}
   
   .reply {
   height: 60px;
   width: 100%;
   padding: 10px 5px 10px 5px !important;
   background-color: #f5f1ee;
   z-index: 1000;
   }
   .reply-send {
   padding: 5px !important;
   display: grid;
   }
   .reply-send i {
   text-align: center;
   color: #93918f;
   cursor: pointer;
   }
   .reply-main {
   padding: 2px 5px !important;
   }
   .reply-main textarea {
   width: 100%;
   resize: none;
   overflow: hidden;
   padding: 5px !important;
   outline: none;
   border: none;
   text-indent: 5px;
   box-shadow: none;
   height: 100%;
   font-size: 16px;
   }
   .reply-main textarea:focus {
   outline: none;
   border: none;
   text-indent: 5px;
   box-shadow: none;
   }
   @media only screen and (min-width: 991px) {
   .back-arr{
         display: none;
   }
}
   @media only screen and (max-width: 646px) {
      .py-5 {
         display: none;

      }
      .row-top{
      display: block;
   }
   }
   @media only screen and (min-width: 600px) {
   .py-5 {
      display: block;

   }
   .row-top{
      display: block;
   }
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
               <a href="<?php URL::to('sms'); ?>">Sms</a>
            </li>
            <li class="active">@lang('app.list')</li>
         </ul>
      </div>
      <div class="page-content">
         <div class="page-header">
            <h1>
               SMS
               <small>
               <i class="ace-icon fa fa-angle-double-right"></i>
               @lang('app.list')
               </small>
               <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#add"
                  id="new"><i class="icon-plus"></i> @lang('app.new')</button>
            </h1>
         </div>
         <div class="row">
            <div class="col-xs-12">
               <!--head tab-->
               <ul class="nav nav-tabs" role="tablist" id="myTab">
                  <li role="presentation" class="active"><a href="#inputs" aria-controls="inputs" role="tab"
                     data-toggle="tab">@lang('app.sent')</a></li>
                  {{-- <li role="presentation"><a href="#outputs" aria-controls="outputs" role="tab"
                     data-toggle="tab">@lang('app.received')</a></li> --}}
                  <li role="presentation"><a href="#whatsapp-chat-tab" aria-controls="whatsapp-chat-tab" role="tab"
                     data-toggle="tab">@lang('app.whatsapp_chat')</a></li>
               </ul>
               <!--head endtab-->
               <!--tab content-->
               <div class="tab-content">
                  <!--tab home-->
                  <div role="tabpanel" class="tab-pane active" id="inputs">
                     <!--inicio tabla ingresos-->
                     <div class="widget-box widget-color-blue2">
                        <div class="widget-header">
                           <h5 class="widget-title">@lang('app.allMessagesSent')</h5>
                           <div class="widget-toolbar">
                              <a data-action="reload" href="#" class="recargar white"><i
                                 class="ace-icon fa fa-refresh"></i></a>
                              <a data-action="fullscreen" class="white" href="#"><i
                                 class="ace-icon fa fa-expand"></i></a>
                              <a data-action="collapse" href="#" class="white"><i
                                 class="ace-icon fa fa-chevron-up"></i></a>
                           </div>
                        </div>
                        <div class="widget-body">
                           <div class="widget-main">
                              <!--Contenido widget-->
                              <table id="send-table" class="table table-bordered table-hover">
                                 <thead>
                                    <tr>
                                       <th>@lang('app.client')</th>
                                       <th>@lang('app.router')</th>
                                       <th>@lang('app.destinationNo')</th>
                                       <th>@lang('app.date')</th>
                                       <th>@lang('app.message')</th>
                                       <th>Gateway</th>
                                       <th>@lang('app.state')</th>
                                       <th>@lang('app.operations')</th>
                                    </tr>
                                 </thead>
                              </table>
                           </div>
                        </div>
                     </div>
                     <!--Fin tabla ingresos-->
                  </div>
                  <!--end tab home-->
                  <div role="tabpanel" class="tab-pane" id="outputs">
                     <!--inicio tab egresos-->
                     <!--inicio tabla egresos-->
                     <div class="widget-box widget-color-blue2">
                        <div class="widget-header">
                           <h5 class="widget-title">@lang('app.allMessagesReceived')</h5>
                           <div class="widget-toolbar">
                              <a data-action="reload" href="#" class="recargar white"><i
                                 class="ace-icon fa fa-refresh"></i></a>
                              <a data-action="fullscreen" class="white" href="#"><i
                                 class="ace-icon fa fa-expand"></i></a>
                              <a data-action="collapse" href="#" class="white"><i
                                 class="ace-icon fa fa-chevron-up"></i></a>
                           </div>
                        </div>
                        <div class="widget-body">
                           <div class="widget-main">
                              <!--Contenido widget-->
                              <table id="insms-table" class="table table-bordered table-hover">
                                 <thead>
                                    <tr>
                                       <th>@lang('app.client')</th>
                                       <th>@lang('app.number')</th>
                                       <th>@lang('app.date')</th>
                                       <th>@lang('app.message')</th>
                                       <th>Gateway</th>
                                       <th>@lang('app.operations')</th>
                                    </tr>
                                 </thead>
                              </table>
                           </div>
                        </div>
                     </div>
                     <!--fin de tabla egresos-->
                  </div>
                  <!--fin del tab egresos-->
                  <div role="tabpanel" class="tab-pane" id="whatsapp-chat-tab">
                     <!--inicio tab egresos-->
                     <!--inicio tabla egresos-->
                     <div class="widget-box widget-color-blue2">
                        <div class="widget-header">
                           <h5 class="widget-title">@lang('app.whatsapp_chat')</h5>
                           <div class="widget-toolbar">
                              <a data-action="reload" href="#" class="reload-whatsapp-chat white"><i
                                 class="ace-icon fa fa-refresh"></i></a>
                              <a data-action="fullscreen" class="white" href="#"><i
                                 class="ace-icon fa fa-expand"></i></a>
                              <a data-action="collapse" href="#" class="white"><i
                                 class="ace-icon fa fa-chevron-up"></i></a>
                           </div>
                        </div>
                        <div class="widget-body">
                           <div class="widget-main_1" style="display: flex;" id="whatsapp-chat-form">
                              <!--Contenido widget-->
                              <div class="row_top" id="row_top" style="margin-bottom: 30px; ">
                                 <div class="col-md-12">
                                    <div class="heading">
                                       <div class="col-sm-3 col-xs-3 heading-avatar">
                                          <div class="heading-avatar-icon">
                                             <img src="{{asset('assets/images/images.png')}}">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row searchBox">
                                       <div class="col-sm-12 searchBox-inner">
                                          <div class="form-group has-feedback">
                                             <input  onkeyup="whatsappsearch()" id="searchText" type="text" class="form-control" name="searchText" placeholder="Search">
                                             <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- sideBar -->
                                    
                                    <div class="row sideBar" style="height: 458px; overflow-x: hidden; overflow-y: auto;">
                                       @foreach ($clients_list as $client)
                                       <div class="row sideBar-body">
                                          <div class="col-sm-3 col-xs-3 sideBar-avatar">
                                             <div class="avatar-icon">
                                                <img src="{{asset('assets/images/Admin.png')}}">
                                             </div>
                                          </div>
                                          <div class="col-sm-9 col-xs-9 sideBar-main">
                                             @if($client->type == '1')
                                             <div class="row">
                                                <div onclick="chatdiv({{ $client->phone }});" class="col-sm-8 col-xs-8 sideBar-name">
                                                   <span class="name-meta">{{ $client->name }}
                                                   </span>
                                                   <br>
                                                   <div class="d-flex flex-row ">
                                                      <svg xmlns="https://www.w3.org/TR/SVG/" viewBox="0 0 18 18" width="18" height="18">
                                                         <path fill="Orange" d="M17.394 5.035l-.57-.444a.434.434 0 0 0-.609.076l-6.39 8.198a.38.38 0 0 1-.577.039l-.427-.388a.381.381 0 0 0-.578.038l-.451.576a.497.497 0 0 0 .043.645l1.575 1.51a.38.38 0 0 0 .577-.039l7.483-9.602a.436.436 0 0 0-.076-.609zm-4.892 0l-.57-.444a.434.434 0 0 0-.609.076l-6.39 8.198a.38.38 0 0 1-.577.039l-2.614-2.556a.435.435 0 0 0-.614.007l-.505.516a.435.435 0 0 0 .007.614l3.887 3.8a.38.38 0 0 0 .577-.039l7.483-9.602a.435.435 0 0 0-.075-.609z"></path>
                                                      </svg>
                                                      <span class="list-unstyled_msg">{{$client->message}}</span>
                                                   </div>
                                                </div>
                                                <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                                                   <span class="time-meta pull-right">{{\Carbon\Carbon::parse($client->send_date)->format('m/d/Y') }}
                                                   </span>
                                                </div>
                                             </div>
                                             @else
                                             @if($client->type == '2' && $client->msg_status == '1')
                                             <div class="row">
                                                <div onclick="chatdiv({{ $client->phone }});" class="col-sm-8 col-xs-8 sideBar-name">
                                                   <span class="name-meta">{{ $client->name }}
                                                   </span>
                                                   <br>
                                                   <div class="d-flex flex-row ">
                                                      <span class="list-unstyled_msg">{{$client->message}}</span>
                                                   </div>
                                                </div>
                                                <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                                                   <span class="time-meta pull-right">{{\Carbon\Carbon::parse($client->send_date)->format('m/d/Y') }}
                                                   </span>
                                                </div>
                                             </div>
                                             @elseif($client->type == '2' && $client->msg_status == '0')
                                             <div class="row">
                                                <div onclick="chatdiv({{ $client->phone }});" class="col-sm-8 col-xs-8 sideBar-name">
                                                   <span class="name-meta" style="font-weight:bold;">{{ $client->name }}
                                                   </span>
                                                   <br>
                                                   <div class="d-flex flex-row ">
                                             <span class="list-unstyled_msg" style="font-weight:bold;">{{$client->message}}</span>
                                          </div>
                                       </div>
                                       <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                                          <span class="time-meta pull-right" style="font-weight:bold; color: #ffa217;">{{\Carbon\Carbon::parse($client->send_date)->format('m/d/Y') }}
                                             <div class="numberCircle">{{ $client->count }}</div>
                                          </span>
                                       </div>
                                       </div>
                                             @else
                                             <div class="row">
                                                <div onclick="chatdiv({{ $client->phone }});" class="col-sm-8 col-xs-8 sideBar-name">
                                                   <span class="name-meta">{{ $client->name }}
                                                   </span>
                                                   <br>
                                                   <div class="d-flex flex-row ">
                                                      <span class="list-unstyled_msg">{{$client->message}}</span>
                                                   </div>
                                                </div>
                                                <div class="col-sm-4 col-xs-4 pull-right sideBar-time">
                                                   <span class="time-meta pull-right">{{\Carbon\Carbon::parse($client->send_date)->format('m/d/Y') }}
                                                   </span>
                                                </div>
                                             </div>
                                             @endif
                                             @endif
                                          </div>
                                       </div>
                                    @endforeach
                                 </div>
                                 </div>
                              </div>
                                 <div class="py-5" id="reply_mes" style=" flex: 1; ">
                                    <input name="client_phn" value="" type="hidden" class="form-control">
                                    <div class="heading">
                                       <div class="col-sm-2 col-md-1 col-xs-3 heading-avatar">
                                             <div class="heading-avatar-icon">
                                          </div>
                                       </div>
                                       <div class="col-sm-8 col-xs-7 heading-name">
                                             <a class="heading-name-meta">
                                          </a>
                                       </div>
                                    </div>
                                    <div class="row_top1" id="row_top1" style="height: 458px;overflow-x: hidden; overflow-y: auto; text-align: center; flex: 1;">
                                       <div class="col-md-12" style="position: relative;padding-bottom: 50px;">
                                          <div class="list-unstyled">
                                          </div>
                                       </div>
                                    </div>
                                    <div class="reply" style="bottom: 15px; height: 58px;">
                                       <div class="col-sm-9 col-xs-9 reply-main" style="width:90%;">
                                          <textarea class="form-control" rows="1"  name="message" id="comment"></textarea>
                                       </div>
                                       <div class="col-sm-1 col-xs-1 reply-send">
                                          <i class="fa fa-send fa-2x"  id="sendCustomWhatsappMessage" aria-hidden="true"></i>
                                       </div>
                                    </div>
                                 </div>
                           </div>
                        </div>
                     </div>
                     <!--fin de tabla egresos-->
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">@lang('app.close')</span></button>
                     <h4 class="modal-title" id="myModalLabel"><i class="fa fa-bullhorn"></i>
                        @lang('app.send') @lang('app.new') sms
                     </h4>
                  </div>
                  <div class="modal-body" id="winnew">
                     <form class="form-horizontal" id="sendnewadv">
                        <div class="form-group">
                           <label for="slcrouter"
                              class="col-sm-2 control-label">@lang('app.router')</label>
                           <div class="col-sm-10">
                              <select class="form-control" name="router_id" id="slcrouter"></select>
                           </div>
                        </div>
                        <div class="form-group" id="msfrombx">
                           <label for="msfrom" class="col-sm-2 control-label">Enviad desde</label>
                           <div class="col-sm-10">
                              <select class="form-control" name="msfrom">
                                 <option value="">Select Send From</option>
                                 @if($sms_options['e']==1)
                                 <option value="1">Twilio SMS</option>
                                 @endif
                                 @if($what_options['e']==1)
                                 <option value="2">Twilio Whatsapp SMS</option>
                                 @endif
                                 @if($webox_sms_options['e']==1)
                                 <option value="3">Waboxapp SMS</option>
                                 @endif
                                 @if(!empty($whatsappcloudapi->status))
                                 <option value="4">Whatsapp Cloud API</option>
                                 @endif
                              </select>
                           </div>
                        </div>
                        <div class="form-group" id="sltemplate">
                           <label for="type_temp"
                              class="col-sm-2 control-label">@lang('app.template')</label>
                           <div class="col-sm-10">
                              <select class="form-control" id="type_temp" name="template"></select>
                              <br>
                              <a href="" class="btn btn-success btn-xs" id="btnpreview" target="_blank"><i
                                 class="fa fa-mobile"></i>
                              @lang('app.preview')</a>
                           </div>
                        </div>
                        <div class="form-group" id="showtext">
                           <label for="name_adv"
                              class="col-sm-2 control-label">@lang('app.message')</label>
                           <div class="col-sm-10">
                              <textarea class="form-control" id="message" name="message" rows="3"
                                 maxlength="160"></textarea>
                              <span class="help-block"
                                 id="remaining">@lang('app.160charactersRemaining') </span>
                           </div>
                        </div>
                        <div class="form-group" id="lsclient">
                           <label for="ms" class="control-label col-xs-12 col-sm-2">@lang('app.send')
                           a</label>
                           <div class="col-xs-12 col-sm-10">
                              <select id="ms" class="multiselect" multiple="" name="clients[]"></select>
                           </div>
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default"
                        data-dismiss="modal">@lang('app.close')</button>
                     <button type="button" class="btn btn-primary" id="btnSend"
                        data-loading-text="Enviando..." autocomplete="off"><i
                        class="fa fa-share-square"></i>
                     @lang('app.send')</button>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade" id="info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">@lang('app.close')</span></button>
                     <h4 class="modal-title" id="myModalLabel"><i class="fa fa-bullhorn"></i>
                        @lang('app.listOfSentSMS')
                     </h4>
                  </div>
                  <div class="modal-body" id="reloadsms">
                     <div class="table-responsive">
                        <table id="groupsend-table" class="table table-bordered table-hover">
                           <thead>
                              <tr>
                                 <th>@lang('app.client')</th>
                                 <th>@lang('app.telephone')</th>
                                 <th>@lang('app.state')</th>
                              </tr>
                           </thead>
                        </table>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default"
                        data-dismiss="modal">@lang('app.close')</button>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade" id="answer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">@lang('app.close')</span></button>
                     <h4 class="modal-title" id="myModalLabel"><i class="fa fa-paper-plane"></i>
                        Responder Sms
                     </h4>
                  </div>
                  <div class="modal-body" id="">
                     <form class="form-horizontal" id="formanswer">
                        <div class="form-group">
                           <label class="col-sm-2 control-label">@lang('app.client')</label>
                           <div class="col-sm-10">
                              <p class="form-control-static" id="answerclient"></p>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="answerphone"
                              class="col-sm-2 control-label">@lang('app.number')</label>
                           <div class="col-sm-10">
                              <input class="form-control" name="phone" id="answerphone" type="text"
                                 readonly>
                              <span id="Blockmessage" class="help-block"></span>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="inputPassword3"
                              class="col-sm-2 control-label">@lang('app.message')</label>
                           <div class="col-sm-10">
                              <textarea class="form-control" rows="3" name="message"
                                 maxlength="160"></textarea>
                           </div>
                        </div>
                     </form>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                           data-dismiss="modal">@lang('app.close')</button>
                        <button type="button" class="btn btn-primary" id="btnSendanswer"
                           data-loading-text="Enviando..." autocomplete="off"><i
                           class="fa fa-share-square"></i>
                        @lang('app.send')</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal fade" id="openanswer1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">@lang('app.close')</span></button>
                     <h4 class="modal-title" id="myModalLabel"><i class="fa fa-envelope-open-o"></i>
                        @lang('app.open') Sms
                     </h4>
                  </div>
                  <div class="modal-body" id="">
                     <form class="form-horizontal">
                        <div class="form-group">
                           <label class="col-sm-2 control-label">@lang('app.client')</label>
                           <div class="col-sm-10">
                              <p class="form-control-static" id="anscliopen"></p>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="answerphone"
                              class="col-sm-2 control-label">@lang('app.number')</label>
                           <div class="col-sm-10">
                              <p class="form-control-static" id="ansphopen"></p>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="inputPassword3"
                              class="col-sm-2 control-label">@lang('app.message')</label>
                           <div class="col-sm-10">
                              <p class="form-control-static" id="ansmessaopen"></p>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="answerphone"
                              class="col-sm-2 control-label">@lang('app.date')</label>
                           <div class="col-sm-10">
                              <p class="form-control-static" id="Blockansdate"></p>
                           </div>
                        </div>
                     </form>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                           data-dismiss="modal">@lang('app.close')</button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         @include('layouts.modals')
      </div>
   </div>
</div>
<input id="val" type="hidden" name="register" value="">
@endsection
@section('scripts')
<script src="{{asset('assets/js/Loading/js/jquery.loadingModal.min.js')}}"></script>
<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-multiselect.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-multiselect-collapsible-groups.js')}}"></script>
<script src="{{asset('assets/js/rocket/sms-core.js')}}"></script>
@endsection