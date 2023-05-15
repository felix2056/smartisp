<div class="wrapper">
@php
    $status_class = [
        1 => 'paid',
        2 => 'paid-account',
        3 => 'unpaid',
        4 => 'late',
        5 => 'remove'
    ];

    $status_name = [
        1 => 'Pagada',
        2 => 'Pagada (con cuenta)',
        3 => 'No pagada',
        4 => 'Tarde',
        5 => 'Retirar'
    ];

    $sub_total = $iva_total = 0;
@endphp

<table class="header">
        <tbody>
            <tr>
                <td nowrap="nowrap" width="50%">

                    <p><img src="{{asset('assets/img/logo.png')}}" width="156"></p>
                </td>
                <td align="center" width="50%">
                    <font class="{{ $status_class[$status] }}">{{ $status_name[$status] }}</font><br>
                    <p><strong>FCN Internet Service Provider</strong></p>
                    <p>@lang('app.streetAddress') 000 - Zona - Argentina</p>
                    <p>@lang('app.Makethepaymentintheauthorizedbanks').<br>
                        <br>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="row ba">
        <span class="title">@lang('app.facturano') {{$numFactura}}</span><br>
        Fecha de la Factura: {{$fechaPago}}<br>
        Fecha de Vencimiento: {{$vencimiento}}
    </div>

    <table class="">
        <tbody>
            <tr>
                <td width="50%">

                    <div class="">

                        <strong>Facturado a:</strong><br>
                        {{$cliente}}<br>
                        {{$direccionCliente}}<br>
                        {{$telefonoCliente}}<br>
                        {{$emailCliente}}

                    </div>

                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <table class="items">
        <tbody>
            <tr class="title textcenter">
                <td width="70%">@lang('app.description')</td>
                <td width="30%">@lang('app.importe')</td>
            </tr>
            @if ($invoice_items->count() === 0)
            <tr>
                <td>
                    <strong>@lang('app.broadbandInternetService') WIFI</strong>
                    <p>
                        @lang('app.billingPeriodOf') {{@$start_date}} @lang('app.toThe') {{@$vencimiento}}
                    </p>
                    <p>
                        @lang('app.dateOfSuspensionofservice'): {{@$hastafecha}}
                    </p>
                    <p>
                        @lang('app.internetPlan'): {{@$plan}}
                    </p>
                    <p>
                        @lang('app.downloadSpeed'): {{@$descarga}} kbps
                    </p>
                    <p>
                        @lang('app.uploadSpeed'): {{@$subida}} kbps
                    </p>
                </td>
                <td class="textcenter">{{@$Smoneda}}{{@$costo}} {{@$moneda}}</td>
            </tr>
            @else
            @foreach ($invoice_items as $item)
            @php
                $sub_total += round($item->price * $item->quantity, 2);
                $iva_total += round(($item->price * $item->quantity * $item->iva) / 100, 2);
            @endphp
            <tr>
                <td>
                    <p>{{ $item->description }}</p>
                    @if ($item->period_from)
                    <p>{{ \Carbon\Carbon::parse($item->period_from)->format('d/m/Y').' to '.\Carbon\Carbon::parse($item->period_to)->format('d/m/Y') }}</p>
                    @endif
                </td>
                <td>
                    <table class="amount_info">
                        <tr>
                            <td>@lang('app.unitPrice'):</td>
                            <td>{{@$Smoneda}} {{ $item->price }}</td>
                        </tr>
                        <tr>
                            <td>@lang('app.quantity'):</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                        <tr>
                            <td>IVA ({{ $item->iva.' %' }}):</td>
                            <td>{{@$Smoneda}} {{ ($item->price * $item->quantity * $item->iva) / 100}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endforeach
            @endif

            <tr class="title">
                <td class="textright">Sub @lang('app.total'):</td>
                <td class="textcenter">{{@$Smoneda}}{{ $invoice_items->count() > 0 ? $sub_total : $costo }} {{@$moneda}}</td>
            </tr>
            <tr class="title">
                <td class="textright">@lang('app.importe') IVA:</td>
                <td class="textcenter">{{@$Smoneda}}{{ $invoice_items->count() > 0 ? $iva_total : $iva }} {{@$moneda}}</td>
            </tr>
            <tr class="title">
                <td class="textright">@lang('app.total'):</td>
                <td class="textcenter">{{@$Smoneda}}{{ $invoice_items->count() > 0 ? $sub_total + $iva_total : $total}} {{@$moneda}}</td>
            </tr>
        </tbody>
    </table>
</div>

