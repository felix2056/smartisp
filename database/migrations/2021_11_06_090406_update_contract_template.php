<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateContractTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $contractTemplate = \App\models\Template::where('name', 'contract')->where('filename', 'contract.blade.php')->first();
        $contractTemplate->content = '<div class=WordSection1 style="margin: 10px 50px 10px 50px;">

    <p class=MsoNormal align=center style="text-align:center"><b><span
                    lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">CONTRATO
DE ADHESIÓN POR SERVICIOS DE INTERNET</span></b></p>

    <p class=MsoNormal style="text-align:justify"><span lang=ES-TRAD
                                                        style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
-.1pt">Intervienen en la celebración del presente contrato, por una parte al   
<b>CLIENT_NAME., </b>con<b> C.C. 2190003844001, </b>a quien en adelante se le
denominará como EL SOLICITANTE o CLIENTE;  y por otra la<b> </b>Señora <b>LUIS
PEREZ</b>, en calidad de Representante del Establecimiento <b>COMPANY_NAME</b>,
a quién se le denominará  simplemente como <b>COMPANY_NAME</b>, los mismos que
se someten a las siguientes cláusulas:</span></p>

    <p class=MsoNormal style="text-align:justify"><b><span lang=ES-EC
                                                           style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">2.-</span></b><span
                lang=ES-EC style="font-size:9.0pt;font-family:Arial Narrow,sans-serif"> </span><b><span
                    lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
letter-spacing:-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:
9.0pt;font-family:Arial Narrow,sans-serif"> en virtud de este contrato,
permitirá al Cliente formar parte de una red de cadena de oficinas llamadas
SUCURSAL y usar el nombre y los servicios que presta como: acceso a redes
nacionales e internacionales de Internet, con el propósito de que los
Solicitantes puedan tener acceso a Internet. Corresponderá a </span><b><span
                    lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
letter-spacing:-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:
9.0pt;font-family:Arial Narrow,sans-serif">la provisión del servicio y al
Cliente el de proveer los equipos adecuados para un óptimo aprovechamiento del
mismo.</span></p>

    <p class=MsoNormal style="text-align:justify"><b><span lang=ES-EC
                                                           style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">3.-</span></b><span
                lang=ES-EC style="font-size:9.0pt;font-family:Arial Narrow,sans-serif"> El
Cliente deberá pagar a </span><b><span lang=ES-TRAD style="font-size:9.0pt;
font-family:Arial Narrow,sans-serif;letter-spacing:-.1pt">COMPANY_NAME</span></b><span
                lang=ES-EC style="font-size:9.0pt;font-family:Arial Narrow,sans-serif"> por
el servicio antes descrito, de acuerdo a lo que consta en el ANEXO 2 (comercial),denominado
ORDEN DE SERVICIO, cuyas disposiciones forman parte de este contrato, cantidad
que de acuerdo al consumo y a los impuestos que se causen por la prestación del
servicio objeto de este contrato, se verá reflejada en la <b>correspondiente</b>
Factura que el Cliente se obliga a conocerla y/o consultarla a través de
cualquier medio electrónico de que disponga </span><b><span lang=ES-TRAD
                                                            style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:9.0pt;
font-family:Arial Narrow,sans-serif"> a favor de sus Clientes. El Cliente se
compromete a pagar a </span><b><span lang=ES-TRAD style="font-size:9.0pt;
font-family:Arial Narrow,sans-serif;letter-spacing:-.1pt">COMPANY_NAME</span></b><span
                lang=ES-EC style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">
anticipadamente en forma mensual la tarifa o servicios que haya suscrito según
la o las solicitudes o anexos, así como cualquier otro valor que se derive de
la prestación del servicio contratado en los términos de este instrumento. El
incumplimiento en el pago dará derecho a </span><b><span lang=ES-TRAD
                                                         style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:9.0pt;
font-family:Arial Narrow,sans-serif"> a: cobrar los correspondientes costos
por trámites de cobranza e intereses, a la tasa máxima convencional permitida,
más los recargos por mora vigente, a la fecha del incumplimiento, hasta la
fecha del pago. El Cliente podrá pagar a </span><b><span lang=ES-TRAD
                                                         style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:9.0pt;
font-family:Arial Narrow,sans-serif"> por el servicio materia del presente
contrato, a través de orden de débito de su cuenta corriente, ahorros, o a
través de las tarjetas de crédito con las que opere </span><b><span
                    lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
letter-spacing:-.1pt">COMPANY_NAME</span></b><span lang=ES-EC style="font-size:
9.0pt;font-family:Arial Narrow,sans-serif">.</span><span lang=ES-EC
                                                           style="font-size:9.0pt;font-family:Arial,sans-serif"> </span>
    </p>

    <p class=MsoNormal><span lang=ES-EC style="font-size:9.0pt;font-family:Arial,sans-serif">RESPONSABILIDAD
EL CLIENTE asume la responsabilidad por los actos de sus empleados,
contratistas o subcontratistas por el mal uso que eventualmente diere a los
servicios que se les preste; en especial si se usare los servicios o enlaces
prestados en actividades contrarias a las leyes y regulaciones de
telecomunicaciones. Por su parte EL PROVEEDOR tendrá responsabilidad por la
debida prestación del servicio contratado en las características y estándares
del presente contrato y las señaladas en las Leyes y regulación vigente. </span></p>

    <p class=MsoNormal style="text-align:justify"><span lang=ES-EC
                                                        style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">TRIGÉSIMA: <b>SOLUCIÓN
DE CONTROVERSIAS:</b></span></p>

    <p class=MsoNormal style="text-align:justify"><span lang=ES-EC
                                                        style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">En el evento de
controversias, las partes se someten especial y señaladamente a la jurisdicción
y competencia de los jueces de lo civil de la ciudad de [Guayaquil] o [Quito],
República del Ecuador, renunciando a otro domicilio o fuero, sometiéndose a la
vía judicial verbal sumaria, sin perjuicio de que pueda demandarse en el
domicilio del Cliente. </span></p>

    <p class=MsoNormal style="text-align:justify"><span lang=ES-EC
                                                        style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">TRIGÉSIMA
PRIMERA: <b>ACEPTACIÓN:</b></span></p>

    <p class=MsoNormal style="text-align:justify"><span lang=ES-EC
                                                        style="font-size:9.0pt;font-family:Arial Narrow,sans-serif">EL Cliente acepta
y se obliga a cumplir con todas y cada una <u>de</u> los numerales y
condiciones que anteceden. La aceptación por parte de <b>COMPANY_NAME</b> al
presente documento, estará dado por el servicio que provea al Cliente a través
de su red o de <u>Internet</u>; aceptación que tendrá como respaldo la carta de
aceptación del servicio contratado debidamente firmada por el Cliente. </span></p>

    <p class=MsoNormal><span lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
letter-spacing:-.1pt">Para constancia de lo estipulado en las <u>cláusulas</u>
del presente contrato, las partes firman por duplicado en la ciudad de Lago
Agrio el  <b><span lang=ES-TRAD style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
-.1pt">DATE_REGISTRATION</span></b>.</span></p>

    <table class=MsoTableGrid border=0 cellspacing=0 cellpadding=0
           style="border-collapse:collapse;border:none ;margin-right: 15%; margin-left: 15%;">
        <tr>
            <td width=283 valign=top style="width:212.2pt;padding:0in 5.4pt 0in 5.4pt">
                <p class=MsoNormal><b><span style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
  letter-spacing:-.1pt">SR:  <span style="color:red">CLIENT_NAME<br>
  </span>RUC:  <span style="color:red">DNI_CLIENT</span><br>
  Email:  <span style="color:red">EMAIL_CLIENT</span><br>
  Telefono:  <span style="color:red">PHONE_CLIENT</span><br>
  <br>
  EL CLIENTE</span></b></p>
            </td>
            <td width=283 valign=top style="width:212.2pt;padding:0in 5.4pt 0in 5.4pt">
                <p class=MsoNormal><b><span style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;
  letter-spacing:-.1pt">SR: </span></b><b><span style="font-size:9.0pt;
  font-family:Arial Narrow,sans-serif">COMPANY_NAME</span></b><b><span
                                style="font-size:9.0pt;font-family:Arial Narrow,sans-serif;letter-spacing:
  -.1pt"><br>
  RUC: <span style="color:red">DNI_ISP</span><br>
  Email: <span style="color:red">EMAIL_ISP</span><br>
  Telefono: <span style="color:red">PHONE_ISP</span><br>
  <br>
  REPRESENTANTE LEGAL </span></b></p>
            </td>
        </tr>
    </table>

    <p class=MsoNormal>&nbsp;</p>

</div>';
        $contractTemplate->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
