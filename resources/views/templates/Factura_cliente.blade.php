<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    <title>{{@$empresa}} - @lang('app.facturano') {{@$numFactura}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <style>
        body {
            margin: 15px;
            padding: 0;
            background-color: #ffffff;
        }

        body,
        td,
        input,
        select {
            font-family: Tahoma;
            font-size: 1rem;
            color: #000000;
        }

        form {
            margin: 0px;
        }

        a {
            font-size: 14px;
            color: #1E598A;
            padding: 10px;
        }

        a:hover {
            text-decoration: none;
        }

        .textcenter {
            text-align: center;
        }

        .textright {
            text-align: right;
        }

        .wrapper {
            margin: 0 auto;
            padding: 10px 20px 70px 20px;
            width: 600px;
            background-color: #fff;
            border: 1px solid #ccc;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            -o-border-radius: 6px;
            border-radius: 6px;
        }

        .header {
            margin: 0 0 15px 0;
            width: 100%;
        }

        .addressbox {
            height: 100px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            color: #000;
            overflow: hidden;
        }

        table.items {
            width: 100%;
            background-color: #ccc;
            border-spacing: 0;
            border-collapse: separate;
            border-left: 1px solid #ccc;
        }

        table.items tr.title td {
            margin: 0;
            padding: 2px 5px;
            line-height: 16px;
            background-color: #efefef;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
            font-size: 12px;
            font-weight: bold;
        }

        table.items td {
            margin: 0;
            padding: 2px;
            line-height: 15px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
        }

        table.items tr:last-child td {
            border-bottom: 1px solid #ccc;
        }

        table.amount_info {
            width: 100%;
        }

        table.amount_info td{
            border: none;
        }

        table.amount_info tr:last-child td {
            border: none;
        }

        .row {
            margin: 15px 0;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
        }

        .unpaid {
            font-size: 20px;
            color: #f89406;
            font-weight: bold;
        }

        .paid, .paid-account{
            font-size: 20px;
            color: #82af6f;
            font-weight: bold;
        }

        .late {
            font-size: 20px;
            color: #d15b47;
            font-weight: bold;
        }

        .remove {
            font-size: 20px;
            color: #777;
            font-weight: bold;
        }

        .refunded {
            font-size: 20px;
            color: #224488;
            font-weight: bold;
        }

        .cancelled {
            font-size: 16px;
            color: #cccccc;
            font-weight: bold;
        }

        .collections {
            font-size: 16px;
            color: #ffcc00;
            font-weight: bold;
        }

        .creditbox {
            margin: 0 auto 15px auto;
            padding: 10px;
            border: 1px dashed #cc0000;
            font-weight: bold;
            background-color: #FBEEEB;
            text-align: center;
            width: 95%;
            color: #cc0000;
        }

        .ba {
            background-color: #EFEFEF;
        }

        .ml-5 {
            margin-left: 2rem;
        }
    </style>
</head>

<body>
<div style="display: none;">
    <?php
    $status_class = [
        1 => "paid",
        2 => "paid-account",
        3 => "unpaid",
        4 => "late",
        5 => "remove"
    ];

    $status_name = [
        1 => "Pagada",
        2 => "Pagada (con cuenta)",
        3 => "No pagada",
        4 => "Tarde",
        5 => "Retirar"
    ];

    $sub_total = $iva_total = 0;
    ?>
</div>
<div class="wrapper">
    <table class="header">
        <tbody>
        <tr>
            <td nowrap="nowrap" width="50%">

                <p><img src="{{ Request::root() }}/assets/img/logo.png" width="156"></p>
            </td>
            <td align="center" width="50%">
                <font class="{{ $status_class[$status] }}">{{ $status_name[$status] }}</font><br>
                <p><strong>FCN Internet Service prueba</strong></p>
                <p>@lang('app.streetAddress') 000 - Zona - Argentina</p>
                <p>@lang('app.Makethepaymentintheauthorizedbanks').<br>
                    <br>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="row ba">
        <span class="title">@lang('app.facturano') {{$numFactura}}</span><br>
        @lang('app.invoiceDate'): {{$fechaPago}}<br>
        @lang('app.duedate'): {{$vencimiento}}
    </div>

    <table class="">
        <tbody>
        <tr>
            <td width="50%">

                <div class="">

                    <strong>@lang('app.billedTo'):</strong><br>
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
                <td class="textcenter">{{@$Smoneda}} {{@$costo}} {{@$moneda}}</td>
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
                            <p>{{ \Carbon\Carbon::parse($item->period_from)->format("d/m/Y")." to ".\Carbon\Carbon::parse($item->period_to)->format("d/m/Y") }}</p>
                        @endif
                    </td>
                    <td>
                        <table class="amount_info">
                            <tr>
                                <td class="textcenter">{{@$Smoneda}} {{ $item->price }}</td>
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

<script src="{{ Request::root() }}/assets/js/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        window.print();
    });
</script>
</body>
</html>
