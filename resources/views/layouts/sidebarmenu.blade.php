@php
    use App\Http\Controllers\PermissionsController;
@endphp

<div id="sidebar" class="sidebar responsive main-menu menu-fixed menu-light menu-accordion    menu-shadow ">
    <div class="main-menu-content ps-container ps-theme-dark ps-active-y"
         data-ps-id="a54019dc-7015-3695-86b4-54d119322d5c">
        <ul class="nav nav-list">

			<?php
			$ver_menu = true;
			$value_li = \Session::get('licencia');
			?>

            <li class="@if(request()->is('dashboard')) active @endif">
                <a href="{{ URL::to('admin') }}">
                    <i class="menu-icon la la-home"></i>
                    <span class="menu-text">@lang('app.desk')</span>
                </a>
                <b class="arrow"></b>
            </li>

            @if(isset($estado_lic))
                @if($estado_lic=='expired')
					<?php
					$ver_menu = false;
					?>
                @endif

            @elseif($value_li=='expired')
				<?php
				$ver_menu = false;
				?>
            @endif

            @if($ver_menu)

                @if($clients)

                    <li class="@if(request()->is('clients') || request()->is('clients/*')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-users"></i>
                            <span class="menu-text"> @lang('app.clients') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>

                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('clients')) active @endif">
                                <a href="{{ URL::to('clients') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('clients/advice')) active @endif">
                                <a href="{{ URL::to('clients/advice') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.emails')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            @if($permissions->maps_client_access)
                                <li class="@if(request()->is('clients/maps/*')) active @endif">
                                    <a href="{{ URL::to('clients/maps') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        @lang('app.customerMap')
                                    </a>
                                    <b class="arrow"></b>
                                </li>
                            @endif

                            @if($permissions->locations_access)
                                <li class="@if(request()->is('clients/Ubicaciones/*') || request()->is('clients/Ubicaciones')) active @endif">
                                    <a href="{{ URL::to('clients/Ubicaciones') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Ubicaciones
                                    </a>
                                    <b class="arrow"></b>
                                </li>
                            @endif

                            <li class="@if(request()->is('clients/day-free')) active @endif">
                                <a href="{{ URL::to('clients/day-free') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    Promesa de pago
                                </a>
                                <b class="arrow"></b>
                            </li>

                        </ul>
                    </li>
                @endif

                @if($plans)
                    <li class="@if(request()->is('plans')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon icon-calendar"></i>
                            <span class="menu-text"> @lang('app.plans') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('plans')) active @endif">
                                <a href="{{ URL::to('plans') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="">
                                <a href="#" data-toggle="modal" data-target="#add">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.add')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                @endif
                @if($routers)

                    <li class="@if(request()->is('routers') || request()->is('networks')) active open @endif">
                        <a href="#" class="dropdown-toggle">

                            <i class="menu-icon la la-sitemap"></i>
                            <span class="menu-text"> @lang('app.networkManagement') </span>
                            <b class="arrow fa fa-angle-down"></b>

                        </a>

                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('routers')) active @endif">
                                <a href="{{ URL::to('routers') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.routers')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('networks')) active @endif">
                                <a href="{{ URL::to('networks') }}" data-toggle="modal">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.IPNetworks')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="">
                                <a href="/monitorizacion?route=dashboard">
                                    <i class="menu-icon la la-dashboard"></i>
                                    <span class="menu-text"> @lang('app.monitoring')</span>
                                </a>
                                <b class="arrow"></b>
                            </li>
                            @if(isset(App\libraries\Helpers::get_api_options('smartolt')['c']) && App\libraries\Helpers::get_api_options('smartolt')['c'] == true)
                                <li class="">
                                    <a href="/smartolt">
                                        <i class="menu-icon la la-dashboard"></i>
                                        <span class="menu-text"> @lang('app.smartolt')</span>
                                    </a>
                                    <b class="arrow"></b>
                                </li>
                            @endif
                        </ul>
                    </li>

                @endif
                @if($users)
                    <li class="@if(request()->is('users')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-user"></i>
                            <span class="menu-text"> @lang('app.adminstrator') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('users')) active @endif">
                                <a href="{{ URL::to('users') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="">
                                <a href="#" data-toggle="modal" class="peref" data-target="#add">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.add')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif

                @if(PermissionsController::hasAnyRole('finanzas'))

                    <li class="@if(request()->is('finance') || request()->is('finance/*')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-calculator"></i>
                            <span class="menu-text"> @lang('app.finance') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>

                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('finance/dashboard')) active @endif">
                                <a href="{{ route('finance.dashboard') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.dashboard')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            <li class="@if(request()->is('finance/transactions')) active @endif">
                                <a href="{{ route('finance.transaction.index') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.transactions')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            <li class="@if(request()->is('finance/invoices')) active @endif">
                                <a href="{{ route('finance.invoice.index') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.bills')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            <li class="@if(request()->is('finance/payments')) active @endif">
                                <a href="{{ route('finance.payments.index')  }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.payments')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <!--Opciones para la factura electronica de Ecuador-->
                            @if(PermissionsController::hasAnyRole('factel')==0)
                                <li class="@if(request()->is('finance/sri/dashboard')) active @endif">
                                    <a href="{{ route('sri.dashboard') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        @lang('app.SRIVouchers')
                                    </a>
                                </li>
                                <li class="@if(request()->is('finance/secuenciales/*')) active @endif">
                                    <a href="{{ route('secuenciales.dashboard') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        @lang('app.SRISequentials')
                                    </a>
                                </li>
                                <li class="@if(request()->is('finance/establecimientos/*') || request()->is('finance/establecimientos')) active @endif">
                                    <a href="{{ route('establecimientos.index') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Establecimientos
                                    </a>
                                </li>
                                <li class="@if(request()->is('finance/ptoEmision/*')) active @endif">
                                    <a href="{{ route('ptoEmision.index') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Puntos de Emision
                                    </a>
                                </li>
                                <!--Opciones para la factura electronica de Colombia-->
                            @elseif(PermissionsController::hasAnyRole('factel')==2)
                                <li class="@if(request()->is('finance/sri/dashboard')) active @endif">
                                    <a href="{{ route('sri.dashboard') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Comprobante DIAN
                                    </a>
                                </li>
                                <li class="@if(request()->is('finance/note/*')) active @endif">
                                    <a href="{{ route('note.dashboard') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Comprobantes electrónicos DIAN
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($clients)

                    <li class="@if(request()->is('inventory/*') || request()->is('inventory/*')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-users"></i>
                            <span class="menu-text"> @lang('app.inventory') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>

                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('inventory/dashboard')) active @endif">
                                <a href="{{ URL::to('inventory/dashboard') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.dashboard')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('inventory/vendors')) active @endif">
                                <a href="{{ URL::to('inventory/vendors') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.vendors')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('inventory/suppliers')) active @endif">
                                <a href="{{ URL::to('inventory/suppliers') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.suppliers')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('inventory/products')) active @endif">
                                <a href="{{ URL::to('inventory/products') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.products')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('inventory/supplier-invoices')) active @endif">
                                <a href="{{ URL::to('inventory/supplier-invoices') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.supplierInvoices')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('inventory/items/*')) active @endif">
                                <a href="{{ URL::to('inventory/items') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.items')
                                </a>
                                <b class="arrow"></b>
                            </li>
                           {{-- <li class="@if(request()->is('clients/advice')) active @endif">
                                <a href="{{ URL::to('clients/advice') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.emails')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            @if($permissions->maps_client_access)
                                <li class="@if(request()->is('clients/maps/*')) active @endif">
                                    <a href="{{ URL::to('clients/maps') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        @lang('app.customerMap')
                                    </a>
                                    <b class="arrow"></b>
                                </li>
                            @endif

                            @if($permissions->locations_access)
                                <li class="@if(request()->is('clients/Ubicaciones/*') || request()->is('clients/Ubicaciones')) active @endif">
                                    <a href="{{ URL::to('clients/Ubicaciones') }}">
                                        <i class="menu-icon fa fa-caret-right"></i>
                                        Ubicaciones
                                    </a>
                                    <b class="arrow"></b>
                                </li>
                            @endif

                            <li class="@if(request()->is('clients/day-free')) active @endif">
                                <a href="{{ URL::to('clients/day-free') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    Promesa de pago
                                </a>
                                <b class="arrow"></b>
                            </li>--}}

                        </ul>
                    </li>
                @endif

                @if($bill)
                    <li class="@if(request()->is('box')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-money"></i>
                            <span class="menu-text"> @lang('app.expenses') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('box')) active @endif">
                                <a href="{{ URL::to('box') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    <span class="menu-text"> @lang('app.smallBox')</span>
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif

                @if($reports)
                    <li class="@if(request()->is('reports') || request()->is('stat')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-calendar"></i>
                            <span class="menu-text"> @lang('app.reports') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('reports')) active @endif">
                                <a href="{{ URL::to('reports') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('stat')) active @endif">
                                <a href="{{ URL::to('stat') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.statistics')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif

                @if($template)
                    <li class="@if(request()->is('templates') || request()->is('visualeditor') || request()->is('htmleditor')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-pencil-square-o"></i>
                            <span class="menu-text"> @lang('app.templates') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('templates')) active @endif">
                                <a href="{{ URL::to('templates') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('visualeditor')) active @endif">
                                <a href="{{ URL::to('visualeditor') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.visualEditor')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('htmleditor')) active @endif">
                                <a href="{{ URL::to('htmleditor') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.htmlEditor')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif

                @if($ticket)
                    <li class="@if(request()->is('tickets') || request()->is('tickets/dashboard')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-ticket"></i>
                            <span class="menu-text"> @lang('app.supportTickets') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>

                        <b class="arrow"></b>

                        <ul class="submenu">
                            <li class="@if(request()->is('tickets/dashboard')) active @endif">
                                <a href="{{URL::to('tickets/dashboard')}}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.dashboard')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            <li class="@if(request()->is('tickets')) active @endif">
                                <a href="{{ URL::to('tickets') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>

                            <li class="">
                                <a href="#" data-toggle="modal" class="newtck" data-target="#add">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.open')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif

                @if($sms)
                    <li class="@if(request()->is('sms')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-commenting"></i>
                            <span class="menu-text"> @lang('app.sms')</span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('sms')) active @endif">
                                <a href="{{URL::to('sms')}}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.list')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="">
                                <a href="#" data-toggle="modal" class="newsms" data-target="#add">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.new')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>

                @endif

                @if($system)
                    <li class="@if(request()->is('config') || request()->is('tools') || request()->is('backups') || request()->is('logs') || request()->is('cron-jobs')) active open @endif">
                        <a href="#" class="dropdown-toggle">
                            <i class="menu-icon la la-cog"></i>
                            <span class="menu-text"> @lang('app.system') </span>
                            <b class="arrow fa fa-angle-down"></b>
                        </a>
                        <b class="arrow"></b>
                        <ul class="submenu">
                            <li class="@if(request()->is('config')) active @endif">
                                <a href="{{ URL::to('config') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.configuration')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('tools')) active @endif">
                                <a href="{{ URL::to('tools') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.tools')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('backups')) active @endif">
                                <a href="{{ URL::to('backups') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.backups')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('logs')) active @endif">
                                <a href="{{ URL::to('logs') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.logs')
                                </a>
                                <b class="arrow"></b>
                            </li>
                            <li class="@if(request()->is('cron-jobs')) active @endif">
                                <a href="{{ URL::to('cron-jobs') }}">
                                    <i class="menu-icon fa fa-caret-right"></i>
                                    @lang('app.cronJobs')
                                </a>
                                <b class="arrow"></b>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif

            <li class="@if(request()->is('license')) active open @endif">
                <a href="#" class="dropdown-toggle">
                    <i class="menu-icon la la-support"></i>
                    <span class="menu-text"> @lang('app.help')
						<span class="badge badge-transparent tooltip-error" id="infolicence">
							<i class="ace-icon fa fa-exclamation-triangle red bigger-130"></i>
						</span>
					</span>

                    <b class="arrow fa fa-angle-down"></b>
                </a>
                <b class="arrow"></b>
                <ul class="submenu">

                    @if(Auth::user()->level=='ad')
                        <li class="@if(request()->is('license')) active @endif">
                            <a href="{{ URL::to('license') }}">
                                <i class="menu-icon fa fa-caret-right"></i>
                                @lang('app.license')
                            </a>
                            <b class="arrow"></b>
                        </li>
                    @endif

                    @if($ver_menu)

                        <li>
                            <a href="https://SmartISP.us/wiki" target="_blank">
                                <i class="menu-icon fa fa-caret-right"></i>
                                @lang('app.wiki')
                            </a>
                            <b class="arrow"></b>
                        </li>

                        <li class="">
                            <a href="#" data-toggle="modal" data-target="#licence">
                                <i class="menu-icon fa fa-caret-right"></i>
                                @lang('app.aboutSmartisp')
                            </a>
                            <b class="arrow"></b>
                        </li>

                        <li class="">
                            <a href="https://t.me/+Xspptsef5gE0ODYx" target="_blank">
                                <i class="menu-icon fa fa-caret-right"></i>
                                @lang('app.news')
                            </a>
                            <b class="arrow"></b>
                        </li>

                        <li class="">
                            <a href="#" data-toggle="modal" data-target="#actualizar_proyecto" id="update_p">
                                <i class="menu-icon fa fa-caret-right"></i>
                                @lang('app.updates')
                            </a>
                            <b class="arrow"></b>
                        </li>
                    @endif

                </ul>
            </li>
        </ul>
        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
            <i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left"
               data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
    </div>
</div>
