
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
							<a href="{{URL::to('portal/tickets')}}">
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
							<a href="{{URL::to('portal/bills')}}">
								<div class="clearfix">
									<span class="pull-left">
										<i class="btn btn-xs no-hover btn-info fa fa-list-alt"></i>
										@lang('app.bills')
									</span>
									<span id="numBills" class="pull-right badge badge-info"></span>
								</div>
							</a>
						</li>
					</ul>
				</li>


			</ul>
		</li>
		<li class="light-blue">
			<a data-toggle="dropdown" href="#" class="dropdown-toggle">
				{{--@if($photo =='')--}}
				<img class="img-circle" src="{{asset('assets/avatars/user.jpg')}}" />
				{{--@else--}}
				{{--<img class="img-circle" alt="" src="<?php echo asset('assets/avatars/'.$photo); ?>" width="36" height="37" />--}}
				{{--@endif--}}
				<span class="user-info">
					<small>Bienvenido,</small>
					{{auth()->guard('cashdesk')->user()->name}}
				</span>

				<i class="ace-icon fa fa-caret-down"></i>
			</a>

			<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">


				<li>
					<a href="{{URL::to('cashdesk/myprofile')}}">
						<i class="ace-icon fa fa-user"></i>
						Mi perfil
					</a>
				</li>

				<li class="divider"></li>

				<li>
					<a href="{{URL::to('cashdesk/logout')}}">
						<i class="ace-icon fa fa-power-off"></i>
						Salir
					</a>
				</li>
			</ul>
		</li>
	</ul>
</div>
