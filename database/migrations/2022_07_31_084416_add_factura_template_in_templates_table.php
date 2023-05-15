<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacturaTemplateInTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $template = new \App\models\Template();
        $template->name = 'Factura cliente second';
        $template->registered = \Carbon\Carbon::now()->format('Y-m-d');
        $template->type = 'invoice';
        $template->system = 0;
        $template->filename = 'factura_cliente_second.blade.php';
        $template->content = '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{@$empresa}} - @lang(\'app.facturano\') {{@$numFactura}}</title>
    <style>
        body {
            font-family: \'Courier New\', Courier, monospace;
        }

        p {
            margin: 0;
        }

        .text-center {
            text-align: center;
        }





        .font-bold {
            font-weight: bold;
        }

        .P13,
        .P7 {
            margin-bottom: 10px;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .line-dot {
            border-bottom: 2px dotted black;
        }

        table {
            width: 100%;
        }

        .p18,
        .P17,
        .P23 {
            padding-top: 10px;
        }

        .P23,
        .P19,
        .p18,
        .P16,
        .p22 {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
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
<section id="main-sec">
    <p class="P1 text-center font-bold">{{ $global->company }}</p>
    <p class="P2 text-center font-bold">{{ $global->email }}</p>
    <p class="P3 text-center">{{ $global->street }}</p>
    <p class="P4 text-center">{{ $global->state }}, {{ $global->country }}</p>
    <p class="P8">@lang(\'app.client\') : {{$cliente}}</p>
    <p class="P9">@lang(\'app.clientDni\') : {{ $vatNumber }}</p>
    <p class="P10">@lang(\'app.expirtaion\') : {{ $vencimiento }}</p>
    <p class="P11">@lang(\'app.phone\') : {{ $telefonoCliente }}</p>
    <p class="P12">@lang(\'app.vendedor\') : ADMINISTRATOR</p>
    <p class="P14 text-center font-bold">@lang(\'app.invoice\')</p>
    <p class="P15 d-flex"><span>@lang(\'app.facturano\') : {{ $numFactura }}</span></p>
    <p class="P16 d-flex"><span>@lang(\'app.invoiceDate\') : {{$fechaPago}}</span></p>
    <p class="line-dot"></p>

    <table class="P17">
        @foreach ($invoice_items as $item)
            @php
                $sub_total += round($item->price * $item->quantity, 2);
                $iva_total += round(($item->price * $item->quantity * $item->iva) / 100, 2);
            @endphp
            <tr>
                <td style="width: 30%;">1.0</td>
                <td>x {{ $item->price }}</td>
                <td>{{ $item->plan->name }}</td>
                <td style="text-align: right;">{{ $item->price }}</td>
            </tr>
        @endforeach
    </table>

    <p class="line-dot"></p>
    {{--<P class="P19 d-flex">
        <span>SUBTTL</span>
        <span>{{ $sub_total }}</span>
    </P>--}}

    <table class="p20">
        <tr>
            <td style="width: 30%;">SUBTTL :</td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{ $sub_total }}</td>
        </tr>
    </table>

    <p class="line-dot"></p>

    <table class="p20">
        <tr>
            <td style="width: 30%;">EXENTO :</td>
            <td style="width: 20%;">0.00</td>
            <td>IVA :</td>
            <td style="text-align: right;">{{ $iva_total }}</td>
        </tr>
    </table>


    {{--<table class="p21">
        <tr>
            <td style="width: 30%;">BI G15,00%</td>
            <td style="width: 20%;">304,77</td>
            <td>IVA G16,00%</td>
            <td style="text-align: right;">48.76</td>
        </tr>
    </table>--}}

    {{--<p class="line-dot"></p>

    <table class="p22">
        <tr>
            <td style="width: 30%;">DOLAR</td>
            <td style="width: 20%;">55.00</td>
            <td>IGT (3.00%)</td>
            <td style="text-align: right;">1.65</td>
        </tr>
    </table>--}}

    <p class="line-dot"></p>
    {{--<P style="padding-top: 10px; font-weight: bold; display: -webkit-box; flex-wrap: wrap; justify-content: space-between;">
        <span>TOTAL</span>
        <span>{{ $sub_total + $iva_total }}</span>
    </P>--}}

    <table class="p20">
        <tr>
            <td style="width: 30%;">TOTAL :</td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{ $sub_total + $iva_total }}</td>
        </tr>
    </table>

    {{--<P class="P24 font-bold d-flex">--}}
        {{--<span>NH</span>--}}
        {{--<span>Z1F00251600</span>--}}
    {{--</P>--}}
</section>
</body>

</html>';
        $template->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            //
        });
    }
}
