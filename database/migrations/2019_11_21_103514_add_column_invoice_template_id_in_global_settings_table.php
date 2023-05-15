<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInvoiceTemplateIdInGlobalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->integer('invoice_template_id')->nullable();
            $table->foreign('invoice_template_id')->references('id')->on('templates')->onUpdate('cascade')->onDelete(null);

        });

        $template = \App\models\Template::where('type', 'invoice')->first();
        $global = \App\models\GlobalSetting::first();
        $global->invoice_template_id = $template->id;
        $global->save();

        $template = new \App\models\Template();
        $template->name = 'Factura double slip';
        $template->registered = \Carbon\Carbon::now()->format('Y-m-d');
        $template->type = 'invoice';
        $template->system = 0;
        $template->filename = 'Factura_double_slip.blade.php';
        $template->content = '<html>
<head><meta http-equiv=Content-Type content="text/html; charset=UTF-8">
    <style type="text/css">
        <!--
        span.cls_003{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_003{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        span.cls_002{font-family:"DejaVu Serif Condensed Bold",serif;font-size:12px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_002{font-family:"DejaVu Serif Condensed Bold",serif;font-size:12px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_004{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_004{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        span.cls_005{font-family:"DejaVu Serif Condensed Bold",serif;font-size:12px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        div.cls_005{font-family:"DejaVu Serif Condensed Bold",serif;font-size:12px;color:rgb(0,0,0);font-weight:bold;font-style:normal;text-decoration: none}
        span.cls_006{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        div.cls_006{font-family:"DejaVu Serif Condensed",serif;font-size:12px;color:rgb(0,0,0);font-weight:normal;font-style:normal;text-decoration: none}
        -->
    </style>
    <script type="text/javascript" src="24f02ada-0d47-11ea-9d71-0cc47a792c0a_id_24f02ada-0d47-11ea-9d71-0cc47a792c0a_files/wz_jsgraphics.js"></script>
</head>
<body>
<div style="position:absolute;left:50%;margin-left:-297px;top:0px;width:595px;height:841px;overflow:hidden">
    <div style="position:absolute;left:402.68px;top:44.89px" class="cls_003"><span class="cls_003">Lugar y Fecha</span></div>
    <div style="position:absolute;left:505.14px;top:44.89px" class="cls_003"><span class="cls_003">{{ \Carbon\Carbon::now()->format(\'Y.m.d\') }}</span></div>
    <div style="position:absolute;left:47.02px;top:116.66px" class="cls_002"><span class="cls_002">@lang(\'app.clientData\'):</span></div>
    <div style="position:absolute;left:47.02px;top:128.77px" class="cls_002"><span class="cls_002">Sr (es):</span><span class="cls_004">{{ $cliente }}</span></div>
    <div style="position:absolute;left:47.02px;top:140.82px" class="cls_002"><span class="cls_002">TELÉFONO:</span><span class="cls_004"> {{ $telefonoCliente }}</span></div>
    <div style="position:absolute;left:47.02px;top:152.88px" class="cls_002"><span class="cls_002">DIRECCIÓN:</span><span class="cls_004"> {{ $direccionCliente }}</span></div>

    <div style="position:absolute;left:49.22px;top:215.95px" class="cls_004"><span class="cls_004">#</span></div>
    <div style="position:absolute;left: 85.15px;top:215.95px;" class="cls_004"><span class="cls_004">DESCRIPCION</span></div>
    <div style="position:absolute;left: 402.8px;top:215.95px;" class="cls_004"><span class="cls_004">UNIT</span></div>
    <div style="position:absolute;left: 520.3px;top:215.95px;" class="cls_004"><span class="cls_004">TOTAL</span></div>


    @php
        $sub_total = 0;
        $iva_total = 0;
        $top = 227.75;
    @endphp

    @if($invoice_items->count() === 0)
        <div style="position:absolute;left:50.09px;top:227.75px" class="cls_004"><span class="cls_004">1</span></div>
        <div style="position:absolute;left:85.02px;top:227.75px" class="cls_004"><span class="cls_004">@lang(\'app.broadbandInternetService\') WIFI</span></div>
        <div style="position:absolute;left:404.26px;top:227.75px" class="cls_004"><span class="cls_004">11</span></div>
        <div style="position:absolute;left:520.26px;top:227.75px" class="cls_004"><span class="cls_004">{{ $costo }}</span></div>
    @else
        @foreach ($invoice_items as $item)
            @php
                $sub_total += round($item->price * $item->quantity, 2);
                $iva_total += round(($item->price * $item->quantity * $item->iva) / 100, 2);
            @endphp


            <div style="position:absolute;left:50.09px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->quantity }}</span></div>
            <div style="position:absolute;left:85.02px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->description }}</span></div>
            <div style="position:absolute;left:404.26px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->price }}</span></div>
            <div style="position:absolute;left:520.26px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ round($item->price * $item->quantity, 2) + round(($item->price * $item->quantity * $item->iva) / 100, 2) }}</span></div>
            @php
                $top = $top + 12;
            @endphp
        @endforeach
    @endif


    <div style="position:absolute;left:430.04px;top:295.07px" class="cls_005"><span class="cls_005">SUB TOTAL %</span></div>
    <div style="position:absolute;left:520.76px;top:295.07px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total : $costo }}</span></div>
    <div style="position:absolute;left:430.17px;top:307.82px" class="cls_005"><span class="cls_005">DESCUENTO</span></div>
    <div style="position:absolute;left:520.76px;top:307.82px" class="cls_005"><span class="cls_005">0.00</span></div>
    <div style="position:absolute;left:430.80px;top:319.57px" class="cls_005"><span class="cls_005">SUB TOTAL</span></div>
    <div style="position:absolute;left:520.76px;top:319.57px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total : $costo }}</span></div>
    <div style="position:absolute;left:430.73px;top:331.32px" class="cls_005"><span class="cls_005">IVA </span></div>
    <div style="position:absolute;left:520.76px;top:331.32px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $iva_total : $iva }}</span></div>
    <div style="position:absolute;left:430.56px;top:343.07px" class="cls_005"><span class="cls_005">VALOR TOTAL</span></div>
    <div style="position:absolute;left:520.76px;top:343.07px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total + $iva_total : $total }}</span></div>

    <hr style="position:relative;top: 514.36px; border-top: 1px dashed black;" class="cls_003">

    <div style="position:absolute;left:402.68px;top:560.36px" class="cls_003"><span class="cls_003">Lugar y Fecha</span></div>
    <div style="position:absolute;left:505.14px;top:560.36px" class="cls_003"><span class="cls_003">{{ \Carbon\Carbon::now()->format(\'Y.m.d\') }}</span></div>

    <div style="position:absolute;left:47.02px;top:620.12px" class="cls_002"><span class="cls_002">@lang(\'app.clientData\'):</span></div>
    <div style="position:absolute;left:47.02px;top:632.18px" class="cls_002"><span class="cls_002">Sr (es):</span><span class="cls_004">{{ $cliente }}</span></div>
    <div style="position:absolute;left:47.02px;top:644.29px" class="cls_002"><span class="cls_002">TELÉFONO:</span><span class="cls_004"> {{ $telefonoCliente }}</span></div>
    <div style="position:absolute;left:47.02px;top:656.34px" class="cls_002"><span class="cls_002">DIRECCIÓN:</span><span class="cls_004"> {{ $direccionCliente }}</span></div>

    <div style="position:absolute;left:49.22px;top:730.41px" class="cls_004"><span class="cls_004">#</span></div>
    <div style="position:absolute;left: 85.15px;top:730.41px;" class="cls_004"><span class="cls_004">DESCRIPCION</span></div>
    <div style="position:absolute;left: 402.8px;top:730.41px;" class="cls_004"><span class="cls_004">UNIT</span></div>
    <div style="position:absolute;left: 520.3px;top:730.41px;" class="cls_004"><span class="cls_004">TOTAL</span></div>


    @php
        $sub_total = 0;
        $iva_total = 0;
        $top = 742.22;
    @endphp

    @if($invoice_items->count() === 0)
        <div style="position:absolute;left:50.09px;top:742.22px" class="cls_004"><span class="cls_004">1</span></div>
        <div style="position:absolute;left:85.02px;top:742.22px" class="cls_004"><span class="cls_004">@lang(\'app.broadbandInternetService\') WIFI</span></div>
        <div style="position:absolute;left:404.26px;top:742.22px" class="cls_004"><span class="cls_004">11</span></div>
        <div style="position:absolute;left:520.26px;top:742.22px" class="cls_004"><span class="cls_004">{{ $costo }}</span></div>
    @else
        @foreach ($invoice_items as $item)
            @php
                $sub_total += round($item->price * $item->quantity, 2);
                $iva_total += round(($item->price * $item->quantity * $item->iva) / 100, 2);
            @endphp


            <div style="position:absolute;left:50.09px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->quantity }}</span></div>
            <div style="position:absolute;left:85.02px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->description }}</span></div>
            <div style="position:absolute;left:404.26px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ $item->price }}</span></div>
            <div style="position:absolute;left:520.26px;top:{{$top}}px" class="cls_004"><span class="cls_004">{{ round($item->price * $item->quantity, 2) + round(($item->price * $item->quantity * $item->iva) / 100, 2) }}</span></div>
            @php
                $top = $top + 12;
            @endphp
        @endforeach
    @endif

    <div style="position:absolute;left:430.04px;top:815.54px" class="cls_005"><span class="cls_005">SUB TOTAL %</span></div>
    <div style="position:absolute;left:520.76px;top:815.54px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total : $costo }}</span></div>
    <div style="position:absolute;left:430.04px;top:827.29px" class="cls_005"><span class="cls_005">DESCUENTO</span></div>
    <div style="position:absolute;left:520.76px;top:827.29px" class="cls_005"><span class="cls_005">0.00</span></div>
    <div style="position:absolute;left:430.04px;top:839.04px" class="cls_005"><span class="cls_005">SUB TOTAL</span></div>
    <div style="position:absolute;left:520.76px;top:839.04px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total : $costo }}</span></div>
    <div style="position:absolute;left:430.73px;top:851.79px" class="cls_005"><span class="cls_005">IVA</span></div>
    <div style="position:absolute;left:520.76px;top:851.79px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $iva_total : $iva }}</span></div>
    <div style="position:absolute;left:430.56px;top:863.54px" class="cls_005"><span class="cls_005">VALOR TOTAL</span></div>
    <div style="position:absolute;left:520.76px;top:863.54px" class="cls_005"><span class="cls_005">{{ $invoice_items->count() > 0 ? $sub_total + $iva_total : $total }}</span></div>

</div>
<script src="{{ Request::root() }}/assets/js/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        window.print();
    });
</script>
</body>
</html>
';
        $template->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_settings', function (Blueprint $table) {
            //
        });
    }
}
