<div id="sidebar" class="sidebar responsive main-menu menu-fixed menu-light menu-accordion    menu-shadow ">
    <div class="main-menu-content ps-container ps-theme-dark ps-active-y"
         data-ps-id="a54019dc-7015-3695-86b4-54d119322d5c">

        <ul class="nav nav-list">
            <li @if(strpos(\Request::route()->getName(),'cashdesk.dashboard') !== false ) class="active" @endif>
                <a href="{{ route('cashdesk.dashboard') }}">
                    <i class="menu-icon fa fa-desktop"></i>
                    <span class="menu-text"> @lang('app.desk') </span>
                </a>

                <b class="arrow"></b>
            </li>
            <li @if(strpos(\Request::route()->getName(),'cashdesk.history') !== false ) class="active" @endif>
                <a href="{{ route('cashdesk.history') }}">
                    <i class="menu-icon fa fa-history"></i>
                    <span class="menu-text"> @lang('app.history') </span>
                </a>
            </li>
            <li @if(strpos(\Request::route()->getName(),'search.client') !== false ) class="active" @endif>
                <a href="{{ route('cashdesk.search.client') }}">
                    <i class="menu-icon fa fa-search"></i>
                    <span class="menu-text"> @lang('app.searchClient') </span>
                </a>
                <b class="arrow"></b>
            </li>

            {{--<li @if(strpos(\Request::route()->getName(),'portal.bill') !== false ) class="active" @endif>--}}
                {{--<a href="{{URL::to('portal/bills')}}">--}}
                    {{--<i class="menu-icon fa fa-list-alt"></i>--}}
                    {{--<span class="menu-text">--}}
								{{--@lang('app.bills')--}}
								{{--<span id="bills" class="badge badge-primary"></span>--}}
							{{--</span>--}}
                {{--</a>--}}

                {{--<b class="arrow"></b>--}}
            {{--</li>--}}

            {{--<li @if(strpos(\Request::route()->getName(),'portal.tickets.index') !== false ) class="active" @endif>--}}
                {{--<a href="{{URL::to('portal/tickets')}}">--}}
                    {{--<i class="menu-icon fa fa-ticket"></i>--}}

                    {{--<span class="menu-text">--}}
								{{--@lang('app.supportTickets')--}}

								{{--<span id="tickets" class="badge badge-primary"></span>--}}
							{{--</span>--}}
                {{--</a>--}}

                {{--<b class="arrow"></b>--}}
            {{--</li>--}}


        </ul><!-- /.nav-list -->
        <div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
            <i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left"
               data-icon2="ace-icon fa fa-angle-double-right"></i>
        </div>
    </div>
</div>
