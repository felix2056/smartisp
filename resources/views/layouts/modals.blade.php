<!--About-->
<div class="modal fade" id="licence" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					SmartISP
				</h4>
			</div>
			<div class="modal-body">
				<center><i class="icon-feed fa-5x"></i> <h1>SmartISP  {{ $v }}</h1></center>
				<br>
				<ul class="list-unstyled spaced">
					<li>
						<i class="ace-icon fa fa-caret-right blue"></i>
						@lang('app.version'): {{ $v }}
					</li>
					<li>
						<i class="ace-icon fa fa-caret-right blue"></i>
						@lang('app.license'): @if($lv=='Dev')
						<span class="label label-success">Developer</span>
						@endif

					</li>
					<li>
						<i class="ace-icon fa fa-caret-right blue"></i>
						@if($st=='ac')
						@lang('app.productStatus'): <span class="label label-success">@lang('app.activated')</span>
						@else
						@lang('app.productStatus'): <span class="label label-danger">No @lang('app.activated')</span>
						@endif

					</li>
				</ul>
				<br>
				<center><p>Copyright © 2018 -{{ Carbon\Carbon::now()->year }} SmartISP </p></center>
			</div>
		</div>
	</div>
</div>
<!--end about-->



<div class="modal fade" id="actualizar_proyecto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					SmartISP Update
				</h4>
			</div>
			<div class="modal-body">

				<div id="texto_update" style="font-size: 30px;text-align: center;">
					@lang('app.lookingForUpdate')
				</div>
				<center><i class="fa fa-spinner fa-pulse fa-2x fa-fw" id="loadinfo2" style="display: block;"></i></center>

				<br>
				<center>

					<ul class="list-unstyled spaced">
						<li id="oldver" style="display: none;">
							<i class="ace-icon fa fa-caret-right blue"></i>
							@lang('app.currentVersion'): <span id="ver_Actual"></span>
						</li>
						<li id="snewver" style="display: none;">
							<i class="ace-icon fa fa-caret-right green"></i>
							@lang('app.availableVersion'): <span id="newver"></span>
						</li>
					</ul>



				</center>
				<br>
				<div style="text-align: center;">
					<button type="button" class="btn btn-success" style="display: none;float: unset;" id="btn_update">@lang('app.toUpdate')</button>
				</div>
				<br>

				<center><p>Copyright © 2018 -{{ Carbon\Carbon::now()->year }} SmartISP </p></center>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="change_line_color" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div id="my_color_picker"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="change_marker_icon" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">
					Change Marker Icon
				</h4>
			</div>
			<div class="modal-body">
				<h4 id="myModalLabel">
					Icons
				</h4>
				<div class="icons">
					<ul>

					</ul>
				</div>
				<div class="color-picker-container">
					<h4 id="myModalLabel">
						Color
					</h4>
					<div id="marker_icon_color_picker"></div>
					<input type="hidden" name="marker-icon-color" value="">
				</div>
			</div>
			<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="update-marker-icon">@lang('app.toUpdate')</button>
			</div>
		</div>
	</div>
</div>