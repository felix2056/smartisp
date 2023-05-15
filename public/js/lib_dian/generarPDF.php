<?php

require_once('lib/fpdf/fpdf.php');
require_once('sendEmail.php');
include "lib/phpqrcode/qrlib.php"; 

/**
 * Description of generarPDF
 *
 * @author USER
 */
class PDF extends FPDF
{
    protected $B = 0;
    protected $I = 0;
    protected $U = 0;
    protected $HREF = '';
    function lineColor($dato1,$dato2,$dato3,$alt1=7,$alt2=7)
    {
        $this->MultiCell(55,$alt1,utf8_decode($dato1),1,'L',1);
        $this->setY($this->getY()-7);
        $this->setX(55);
        $this->MultiCell(145,$alt2,utf8_decode($dato2),1,'L',1);
    }
    function lineNormal($datos)
    {
        $conta=1;
        foreach ($datos as $dato) 
        {
            $this->Cell(95,6,utf8_decode($dato[0]),$dato[1],0,'L',$dato[2]);
            if ($conta%2==0) {
                $this->Ln();
            }
            $conta++;
        }
    }
    function WriteHTML($html){
        // Intérprete de HTML
        $html = str_replace("\n",' ',$html);
        $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e){
            if($i%2==0){
                // Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,$e);
            }else{
                // Etiqueta
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else{
                    // Extraer atributos
                    $a2 = explode(' ',$e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach($a2 as $v){
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }
    function OpenTag($tag, $attr){
        // Etiqueta de apertura
        if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,true);
        if($tag=='A')
        $this->HREF = $attr['HREF'];
        if($tag=='BR')
        $this->Ln(5);
    }
    function CloseTag($tag){
        // Etiqueta de cierre
        if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
        if($tag=='A')
        $this->HREF = '';
    }

    function SetStyle($tag, $enable){
        // Modificar estilo y escoger la fuente correspondiente
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach(array('B', 'I', 'U') as $s){
        if($this->$s>0)
        $style .= $s;
        }
        $this->SetFont('',$style);
    }
    function PutLink($URL, $txt){
        // Escribir un hiper-enlace
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
}
//Se genera el PDF
$pdf = new PDF();
$pdf->SetFont('Arial','',14);
$pdf->AddPage();
$pdf->SetTextColor(0,74,247);
$pdf->SetDrawColor(0,74,247);
$pdf->SetLineWidth(.2);
$pdf->image('../lib_firma_sri/src/services/uploads/Logo.jpg', null, null, 45, 20);
$pdf->SetY($pdf->GetY()-22);
// Header
$pdf->SetFont('Times','B',10);
$pdf->Cell(0,10,utf8_decode('Representación Gráfica'),'LTR',0,'C',false);$pdf->Ln();
$pdf->SetFont('Times','B',14);
$pdf->Cell(0,10,utf8_decode('FACTURA ELECTRÓNICA DE VENTA'),'LRB',0,'C',false);$pdf->Ln();
// Color and font restoration
$pdf->SetFillColor(240,240,240);
$pdf->SetTextColor(0);
$pdf->SetFont('');
$pdf->SetFont('Times','',9);
$pdf->Cell(0,10,utf8_decode('CUFE: '.$_POST['cufe']),'LRB',0,'C',false);$pdf->Ln();
$pdf->lineColor('Datos del Documento','Número de Factura: '.$_POST['prefix'].$_POST['number'],14,7);        
$datos=[
    ['Fecha de Emisión: '.$_POST['date'],'L',false],
    ['Medio de Pago: Acuerdo mutuo','R',false]
];
$pdf->lineNormal($datos);

$pdf->lineColor('Datos del Emisor',$_POST['typedocEmisor'].': '.$_POST['identificationEmisor'].' Razón Social: '.$_POST['nameEmisor'],14,7);        
$datos=[
    ['Nombre Comercial: '.$_POST['tradename'],'LT',false],
    ['Departamento/Municipio/Dirección','TR',false],
    ['Tipo de Contribuyente: '.$_POST['typetaxpayerEmisor'],'L',false],
    [$_POST['directionEmisor'],'R',false],
    ['Correo: '.$_POST['emailEmisor'],'LB',false],
    ['Teléfono: '.$_POST['phoneEmisor'],'RB',false]
];
$pdf->lineNormal($datos);
$pdf->lineColor('Datos del Adquiriente',$_POST['typedocAdquiriente'].': '.$_POST['identificationAdquiriente'].' Razón Social: '.$_POST['nameAdquiriente'],14,7);
$datos=[
    ['Nombre Comercial: '.$_POST['taxnameAdquiriente'],'LT',false],
    ['Departamento/Municipio/Dirección','TR',false],
    ['Tipo de Contribuyente: '.$_POST['typetaxpayerAdquiriente'],'L',false],
    [$_POST['directionAdquiriente'],'R',false],
    ['Correo: '.$_POST['emailAdquiriente'],'LB',false],
    ['Teléfono: '.$_POST['phoneAdquiriente'],'RB',false]
];
$pdf->lineNormal($datos);
$pdf->Cell(190,6,utf8_decode('Detalles de Productos'),1,1,'L',true);
//detalle de la factura
//$pdf->SetXY(10, 10);
$pdf->Cell(20, 10, utf8_decode("Código"), 1, 0, "C", false);
$pdf->Cell(55, 10, utf8_decode("Descripción"), 1, 0, "C", false);
$pdf->Cell(20, 10, utf8_decode("Cantidad"), 1, 0, "C", false);
$pdf->Cell(25, 10, utf8_decode("Precio"), 1, 0, "C", false);
$pdf->Cell(25, 10, utf8_decode("Subtotal"), 1, 0, "C", false);
$pdf->Cell(20, 10, utf8_decode("Iva"), 1, 0, "C", false);
$pdf->Cell(25, 10, utf8_decode("Total"), 1, 0, "C", false);
$pdf->Ln();
if ($_POST['detalle']!='') {
    foreach ($_POST['detalle'] as $d) {
        $pdf->Cell(20, 8, $d['cod'], 1, 0, "C", false);
        $pdf->Cell(55, 8, $d['description'], 1, 0, "C", false);
        $pdf->Cell(20, 8, $d['quantity'], 1, 0, "C", false);
        $pdf->Cell(25, 8, $d['price'], 1, 0, "C", false);
        $pdf->Cell(25, 8, $d['quantity']*$d['price'], 1, 0, "C", false);
        $pdf->Cell(20, 8, round(($d['iva']/100)*($d['quantity']*$d['price']),2), 1, 0, "C", false);
        $pdf->Cell(25, 8, $d['total'], 1, 0, "C", false);
        $pdf->Ln();
    }
}
$pdf->Cell(190,6,utf8_decode('Datos Totales'),1,1,'L',true);
$pdf->Ln();
//Generar código QR
$newTZ = new DateTimeZone("America/Bogota");
$GMT = new DateTimeZone("GMT");
//$date = new DateTime($pdf->factura->Horaemision, $GMT );
//$date->setTimezone( $newTZ );
$PNG_WEB_DIR = 'lib/phpqrcode/temp/';
$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.$PNG_WEB_DIR.DIRECTORY_SEPARATOR;
$filename = $PNG_TEMP_DIR.'test'.md5($_POST['cufe']).'.png';
QRcode::png($_POST['qr'],$filename, 'L', 4, 2); 
$pdf->Image($PNG_WEB_DIR.basename($filename),10,$pdf->GetY()-3,40,40); 

$pdf->SetXY(105,$pdf->GetY());
$pdf->Cell(40,6,utf8_decode('MONEDA'),7,0,'L',false);
$pdf->Cell(40,6,utf8_decode($_POST['money']),7,0,'L',false);
$pdf->Ln();
$pdf->SetXY(105,$pdf->GetY());
$pdf->Cell(40,6,utf8_decode('Subtotal (=)'),7,0,'L',false);
$pdf->Cell(40,6,utf8_decode('$ '.$_POST['subtotal']),7,0,'L',false);
$pdf->Ln();
$pdf->SetXY(105,$pdf->GetY());
$pdf->Cell(40,6,utf8_decode('Total impuesto(+)'),7,0,'L',false);
$pdf->Cell(40,6,utf8_decode('$ '.$_POST['iva']),7,0,'L',false);
$pdf->Ln();
$pdf->SetXY(105,$pdf->GetY());
$pdf->Cell(40,6,utf8_decode('Valor total (=)'),7,0,'L',false);
$pdf->Cell(40,6,utf8_decode('$ '.$_POST['total']),7,0,'L',false);
$pdf->Ln();$pdf->Ln();$pdf->Ln();
//Pie de página
$pdf->Cell(52,6,utf8_decode('Numero de Autorización: '.$_POST['resolution_number']),7,0,'L',false);
$pdf->Cell(52,6,utf8_decode('Rango Autorizado: Desde: '.$_POST['resolution_desde']),7,0,'L',false);
$pdf->Cell(52,6,utf8_decode('Hasta: '.$_POST['resolution_hasta']),7,0,'L',false);
$pdf->Cell(30,6,utf8_decode('Vigencia: '.$_POST['resolution_date']),7,0,'L',false);
$pdf->Ln();$pdf->Ln();
$pdf->SetFont('Times','',18);
if ($_POST['typeoperation_cod']=='2') {
    $pdf->Cell(0,20,utf8_decode('ESTA FACTURA ES DE PRUEBA Y NO TIENE VALIDEZ FISCAL'),7,0,'C',false);
}
$pdf->Output('comprobantes_colombia/'.$_POST['filename'].'.pdf', 'F');
//Enviamos el correo con el XML y el PDF
$email = new sendEmail();
$email->enviarCorreo('Factura', 'Comprador SAS', $_POST['filename'],$_POST['correo'],$_POST['host_email'],$_POST['email_origen'],$_POST['passEmail'],$_POST['port']);