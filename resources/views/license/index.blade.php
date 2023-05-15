@extends('layouts.master')

@section('title','Licencia')

@section('content')
	<div class="main-content">
		<div class="main-content-inner">
			<div class="breadcrumbs" id="breadcrumbs">
				<ul class="breadcrumb">
					<li>
						<i class="ace-icon fa fa-home home-icon"></i>
						<a href="{{ URL::to('admin') }}">@lang('app.desk')</a>
					</li>
					<li class="active">Licencia</li>
				</ul>
			</div>


			<div class="page-content">
				<div class="page-header">
					<h1>
						Licencia
					</h1>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="tabbable">
							<ul class="nav nav-tabs padding-18 tab-size-bigger" id="myTab">
								<li class="active">
									<a data-toggle="tab" href="#faq-tab-1">
										<i class="blue ace-icon fa fa-certificate bigger-120"></i>
										Licencia
									</a>
								</li>
							</ul>
							<div class="tab-content no-border padding-24">


								<div id="faq-list-1" class="panel-group accordion-style1 accordion-style2">
									<div class="panel panel-default">

										<div class="panel-heading">
											<a href="#faq-1-4" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
												<i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
												<i class="ace-icon fa fa-certificate bigger-130"></i>
												&nbsp; Activar o actualizar licencia   	&nbsp;	&nbsp;
												<span id="estado_lic">
													<?php if($st=='ac'){ echo'<span class="label label-success"> Activado </span>'; } elseif($st=='ex') { echo '<span class="label label-warning">Vencido</span>';} else { echo '<span class="label label-danger">No activado</span>'; }//endif ?>

												</span>
											</a>
										</div>

										<div class="panel-collapse collapse" id="faq-1-4">
											<div class="panel-body">

												<div class="form-group">
													<label for="exampleInputEmail1"><strong>Digite licencia :</strong></label>
													<input type="text" name="licencia" class="form-control" style="" id="licencia" value="<?php if($license_id!='0'){ echo $license_id;} ?>" placeholder="Licencia"></div>

													<button type="submit" id="btnActivelicensia" class="btn btn-primary">Activar</button>
												</div>
											</div>

										</div>
									</div>




									<div id="faq-list-1" class="panel-group accordion-style1 accordion-style2">
										<div class="panel panel-default">
											<div class="panel-heading">
												<a href="#faq-1-2" data-parent="#faq-list-1" data-toggle="collapse" class="accordion-toggle collapsed">
													<i class="ace-icon fa fa-chevron-left pull-right" data-icon-hide="ace-icon fa fa-chevron-down" data-icon-show="ace-icon fa fa-chevron-left"></i>
													<i class="ace-icon fa fa-bars bigger-130"></i>
													&nbsp; Detalles de licencia
												</a>
											</div>

											<div class="panel-collapse collapse" id="faq-1-2">
												<div class="panel-body">
													<i class="fa fa-spinner fa-pulse fa-2x fa-fw" id="loadinfo"></i>
													<ul class="list-unstyled spaced lsdt">


														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Producto: <b class="green" id="productname"></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Tipo de licencia: <b class="green" id="versionname"></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Sistema operativo: <b class="green" id="plataform"> <?php echo $platform; ?></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Versión del sistema: <b class="green" id="v"><?php echo $v; ?></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Validez de la licencia: <b class="green" id="expires"></b>
														</li>

														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Licencia valida solo para: <b class="green" id="numpc"></b>
														</li>



														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Registrado a nombre de: <b class="green" id="registered"></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Email de usuario: <b class="green" id="emailreg"></b>
														</li>
														<li>
															<i class="ace-icon fa fa-caret-right blue"></i>
															Última actualización: <b class="green" id="updated"> <?php  echo date("d/m/Y", strtotime($updated)); ?> </b>
														</li>




														<li>

															<i class="ace-icon fa fa-caret-right blue"></i><span id="st">
																Estado del Producto: <?php if($st=='ac'){ echo'<span class="label label-success"> Activado </span>'; } elseif($st=='ex') { echo '<span class="label label-warning">Vencido</span>';} else { echo '<span class="label label-danger">No activado</span>'; }//endif ?></span>
															</li>


															<li>
																<i class="ace-icon fa fa-caret-right blue"></i>
																@lang('app.clients'):<b class="green" id="cli"> 0 </b>
															</li>
															<li>
																<i class="ace-icon fa fa-caret-right blue"></i>
																Routers:<b class="green" id="rou"> Ilimitado </b>
															</li>
															<li>
																<i class="ace-icon fa fa-caret-right blue"></i>
																Actualizaciones:<b class="green" id="upd"> Si </b>
															</li>
															<li>
																<i class="ace-icon fa fa-caret-right blue"></i>
																Módulos extra:<b class="green" id="mod"> Si </b>
															</li>
														</ul>
													</div>
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
@endsection

@section('scripts')
	<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
	<script src="{{asset('assets/js/rocket/license-core.js')}}"></script>
@endsection
