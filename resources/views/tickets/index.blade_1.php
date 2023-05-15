@extends('layouts.master')

@section('title','Tickets')

@section('styles')  
@parent
<link rel="stylesheet" href="assets/css/chosen.min.css">
<link rel="stylesheet" href="assets/css/waiting.css">

@stop
@section('content')
<div id="navbar" class="navbar navbar-default">
 <div class="navbar-container" id="navbar-container">
  <button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
   <span class="sr-only">Toggle sidebar</span>
   <span class="icon-bar"></span>
   <span class="icon-bar"></span>
   <span class="icon-bar"></span>
 </button>
 @include('layouts.sidebartopleft')
 <!-- navbarheader right -->
 @include('layouts.navbartopright')
 <!-- navbarheader right -->
</div>
</div>
<div class="main-container" id="main-container">
 <!-- sidebar left menu -->
 @include('layouts.sidebarmenu')
 <!-- sidebar left menu -->
 <div class="main-content">
  <div class="main-content-inner">
   <div class="breadcrumbs" id="breadcrumbs">
    <ul class="breadcrumb">
     <li>
      <i class="ace-icon fa fa-desktop desktop-icon"></i>
      <a href="<?php echo URL::to('portal'); ?>">Escritorio</a>
    </li>
    <li>
      <a href="<?php echo URL::to('portal/tickets'); ?>">Tickets</a>
    </li>
    <li class="active">Listado</li>
  </ul>					
</div>

<div class="page-content">
  <div class="page-header">
   <h1>
    Tickets
    <small>
     <i class="ace-icon fa fa-angle-double-right"></i>
     Listado
   </small>
 </h1>
</div>     
<!--start row-->                        
<div class="row">
 <div class="col-sm-12">
  <!--Inicio de tab simple queues-->
  <button class="btn btn-success newtck" data-toggle="modal" data-target="#add"><i class="icon-plus"></i> Nuevo</button> 

  <div class="row">   
    <div class="col-sm-12">   
      <!--Inicio tabla planes simple queues-->
      <div class="widget-box widget-color-blue2">
       <div class="widget-header">
         <h5 class="widget-title">Mis tickets de soporte</h5>
         <div class="widget-toolbar">
           <div class="widget-menu">
            <a href="#" data-action="settings" data-toggle="dropdown" class="white">
              <i class="ace-icon fa fa-bars"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right dropdown-light-blue dropdown-caret dropdown-closer">
             <li>
              <a href="#" data-toggle="modal" class="newtck" data-target="#add"><i class="fa fa-plus-circle"></i> Abrir nuevo ticket</a>  </li>

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
          <table id="ticket-table" class="table table-bordered table-hover display" width="100%">
           <thead>
            <tr>
             <th>#Ticket</th>
             <th>Sección</th>
             <th>Asunto</th>
             <th>Estado</th>
             <th>Fecha de creación</th>
             <th>Cliente</th>
             <th>Operaciones</th>
           </tr>
         </thead>												
       </table>
     </div>
   </div>
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
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-ticket"></i>
          Abrir nuevo ticket de soporte <i class="fa fa-spinner fa-spin loads"></i></h4>
        </div>
        <div class="modal-body">
          <form class="form-horizontal" role="form" id="ticketform" method="post" enctype="multipart/form-data">
           <div class="form-group">
             <label for="name" class="col-sm-2 control-label">Nombre</label>
             <div class="col-sm-10">
               <input type="text" name="name" class="form-control" value="{{Auth::user()->name}}" id="disabledInput" disabled  maxlength="30">
             </div>
           </div>           
           <div class="form-group">
             <label for="subject" class="col-sm-2 control-label">Asunto</label>
             <div class="col-sm-10">
               <input type="text" name="subject" class="form-control" id="subject" required>
             </div>
           </div>

           <div class="form-group">
             <label for="subject" class="col-sm-2 control-label">Remitente</label>
             <div class="col-sm-10">
               <select class="form-control" name="section">
                <option value="administracion">Administracion - ventas</option>
                <option value="tecnico">Soporte Técnico</option>
              </select>
            </div>
          </div>
          <div class="form-group" id="clt">
           <label for="clients" class="col-sm-2 control-label">Cliente</label>  			
           <div class="col-sm-10">						
            <select class="chosen-select form-control" id="clients" name="client" data-placeholder="Seleccione un cliente">
             <option value=""></option>
           </select>												
         </div>
       </div> 
       <div class="form-group">
         <label for="subject" class="col-sm-2 control-label">Mensaje</label>
         <div class="col-sm-10">
           <textarea class="form-control" name="message" rows="3" required></textarea>
         </div>
       </div>

       <div class="form-group">
         <label for="subject" class="col-sm-2 control-label">Adjunto</label>
         <div class="col-sm-10">
           <input type="file" class="form-control" name="file" id="file">
         </div>
       </div>



     </div>   
     <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      <button type="submit" class="btn btn-primary" id="addbtnticket" data-loading-text="Guardando..." autocomplete="off"><i class="fa fa-floppy-o"></i>
      Guardar</button>
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
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-pencil-square-o"></i> <span id="load2"><i class="fa fa-cog fa-spin"></i> Cargando</span> Ver ticket <i class="fa fa-spinner fa-spin loads"></i></h4>
      </div>
      <div class="modal-body" id="winedit">



        <div id="accordion2" class="accordion-style1 panel-group">			
         <div class="panel panel-default">
          <div class="panel-heading">
           <h4 class="panel-title">
            <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion2" href="#advedit">
              <i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
              <i class="fa fa-pencil"></i>

              &nbsp;Responder
            </a>
          </h4>
        </div>
        <div class="panel-collapse collapse" id="advedit">
         <div class="panel-body">

          <form class="form-horizontal" role="form" id="resticketform" method="post" enctype="multipart/form-data">
           <div class="form-group">
             <label for="name" class="col-sm-2 control-label">Nombre</label>
             <div class="col-sm-10">
               <input type="text" name="name" class="form-control" value="{{Auth::user()->name}}" id="disabledInput" disabled  maxlength="30">
             </div>
           </div>           

           <div class="form-group">
             <label for="subject" class="col-sm-2 control-label">Mensaje</label>
             <div class="col-sm-10">
               <textarea class="form-control" name="message" id="menrep" rows="3" required></textarea>
             </div>
           </div>

           <div class="form-group">
             <label for="subject" class="col-sm-2 control-label">Adjunto</label>
             <div class="col-sm-10">
               <input type="file" class="form-control" name="efile" id="efile">
             </div>
           </div>
           <div class="form-group">
             <div class="col-sm-8">
               <div class="pull-right">
                <a data-toggle="collapse" class="btn btn-default" data-parent="#accordion2" href="#advedit">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="edtbtn">Enviar</button>
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
  <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>

</div>
</div>
</div>
</div>

@include('layouts.modals')	
</div>
</div>
</div>   
@include('layouts.footer')
<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
  <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
</a>


</div>
@section('scripts')
@parent
<script src="{{asset('assets/js/bootbox.min.js')}}"></script>
<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script> 
<script src="{{asset('assets/js/chosen.jquery.min.js')}}"></script> 
<script src="{{asset('assets/js/jquery.waiting.min.js')}}"></script>
<script src="{{asset('assets/js/rocket/tickets-core.js')}}"></script>
@stop
@stop
