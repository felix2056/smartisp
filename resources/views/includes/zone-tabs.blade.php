<div class="page-header">

    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                <li class="@if(\Route::current()->getName() == 'profiles.index') active @endif"><a href="{{ route('profiles.index') }}">Zonas</a></li>

                @if($splitter)
                    <li class="@if(\Route::current()->getName() == 'odb.index') active @endif"><a href="{{ route('odb.index') }}">NAPs</a></li>
                @endif

                @if($onu_cpe)
                    <li class="@if(\Route::current()->getName() == 'onuType.index') active @endif"><a href="{{ route('onuType.index') }}">ONUs/CPE</a></li>
                @endif
            </ul>
        </div>
    </nav>
</div>
