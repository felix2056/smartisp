
<div class="navbar-buttons navbar-header pull-right" role="navigation">
	<ul class="nav ace-nav">

		<li class="purple" id="notifier">
			<a data-toggle="dropdown" class="dropdown-toggle" href="#">
				<i class="ace-icon fa fa-bell icon-animated-bell"></i>
				<span id="numNoti" class="badge badge-important"></span>
			</a>

			<ul class="dropdown-menu-right dropdown-navbar navbar-pink dropdown-menu dropdown-caret dropdown-close">
				<li id="numNotifi" class="dropdown-header">

					<i class="ace-icon fa fa-exclamation-triangle"></i>

				</li>

				<li class="dropdown-content">
					<ul class="dropdown-menu dropdown-navbar navbar-pink">
						<li>
							<a href="{{URL::to('tickets')}}">
								<div class="clearfix">
									<span class="pull-left">
										<i class="btn btn-xs no-hover btn-success fa fa-ticket"></i>
										@lang('app.tickets')
									</span>
									<span id="numTickets" class="pull-right badge badge-success"></span>
								</div>
							</a>
						</li>
						<li>
							<a href="{{URL::to('routers')}}">
								<div class="clearfix">
									<span class="pull-left">
										<i class="btn btn-xs no-hover btn-danger fa fa-hdd-o"></i>
										@lang('app.routers')
									</span>
									<span id="numRouters" class="pull-right badge badge-danger"></span>
								</div>
							</a>
						</li>
						<li>
							<a href="{{URL::to('sms')}}#whatsapp-chat-tab">
								<div class="clearfix">
									<span class="pull-left">
										<i class="btn btn-xs no-hover btn-info fa fa-envelope-o"></i>
										@lang('app.sms')
									</span>
									<span id="numSms" class="pull-right badge badge-danger"></span>
								</div>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</li>

		<style type="text/css">
			.comprar_text {
				font-weight: bold;
				color: #fff !important;
				border-radius: 5px;
				cursor: pointer;

				background: #537e93 !important;
				font-weight: bold;
				padding: 10px;
			}
		</style>
		<li class="light-blue" style="width: 63px">
			<div style="position: absolute;left: 12px">


				<a class="comprar_text" title="Reset chache" id="btn_reini"><i class="la la-refresh"></i></a>
			</div>
		</li>


		<li class="light-blue">
			<a data-toggle="dropdown" href="#" class="dropdown-toggle">
				@if(Auth::user()->photo =='none')
				<img class="img-circle" src="/assets/images/Admin.png" />
				@else
				<img class="img-circle" alt="" src="/assets/avatars/{{Auth::user()->photo}}" width="36" height="37" />
				@endif
				<span class="user-info">
					<div class="welcome_user">@lang('app.hello'),
						<strong>{{Auth::user()->username}}</strong></div>
					</span>
					<i class="ace-icon fa fa-caret-down"></i>
				</a>
				<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
					<li>
						<a href="{{URL::to('myprofile')}}">
							<i class="ace-icon fa fa-user"></i>
							@lang('app.myProfile')
						</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="{{URL::to('logout')}}">
							<i class="ace-icon fa fa-power-off"></i>
							@lang('app.logout')
						</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>

	@section('custom-js')

	<script type="text/javascript">
		function msg(msg,type)
		{
            if(type=='success'){
                var clase = 'gritter-success';
                var tit = '{{__('app.registered')}}';
                var img = 'assets/img/ok.png';
                var stincky = false;
            }
            if(type=='error'){
                var clase = 'gritter-error';
                var tit = '{{ __('app.error') }}';
                var img = 'assets/img/error.png';
                var stincky = false;
            }
            if(type=='debug'){
                var clase = 'gritter-error gritter-center';
                var tit = '{{__('app.internalError')}} (Debug - mode)';
                var img = '';
                var stincky = false;
            }
            if(type=='info'){
                var clase = 'gritter-info';
                var tit = '{{ __('app.information') }}';
                var img = 'assets/img/info.png';
                var stincky = false;
            }
            if(type=='mkerror'){
                var clase = 'gritter-error';
                var tit = '{{ __('app.errorFromMikrotik') }}';
                var img = '';
                var stincky = false;
            }

            if(type=='system'){
                var clase = 'gritter-light gritter-center';
                var tit = '{{ __('app.systemInformation') }}';
                var img = '';
                var stincky = false;
            }

			$.gritter.add({
	// (string | mandatory) the heading of the notification
	title: tit,
	// (string | mandatory) the text inside the notification
	text: msg,
	image: img, //in Ace demo dist will be replaced by correct assets path
	sticky: stincky,
	class_name: clase
});
		}
		$(document).on("click", '#btn_reini', function (event) {

			$("#loadinfo2").show();
			$.ajax({
				"url":"verify_reinicio_ok",
				"type":"POST",
				"data":{},
				"dataType":"json"

			}).done(function(data){

				if(data.status=='200'){

					msg('{{ __('app.clearedCacheSuccessfully') }}', 'success');

				}else{
					msg('{{ __('app.deletingCachePleaseTry') }}', 'error');

				}
				$("#loadinfo2").hide();
			});
		});
	</script>
	@endsection
