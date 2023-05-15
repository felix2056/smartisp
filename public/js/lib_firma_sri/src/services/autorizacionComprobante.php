<?php

session_start();
require_once('../../lib/nusoap.php');
require_once('class/generarPDF.php');

$claveAcceso = $_POST['claveAcceso'];
$service = $_POST['service'];
$host_email = $_POST['host_email'];
$email = $_POST['email'];
$passEmail = $_POST['passEmail'];
$port = $_POST['port'];


//DATA BD

$host_bd = $_POST['host_bd'];
$pass_bd = $_POST['pass_bd'];
$user_bd = $_POST['user_bd'];
$database = $_POST['database'];
$port_bd = $_POST['port_bd'];
$id_factura = $_POST['id_factura'];

$conn = new mysqli($host_bd, $user_bd, $pass_bd, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



//EndPoint
$servicio = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl"; //url del servicio
$parametros = array(); //parametros de la llamada
$parametros['claveAccesoComprobante'] = $claveAcceso;

$client = new nusoap_client($servicio);


$error = $client->getError();



$client->soap_defencoding = 'utf-8';


$result = $client->call("autorizacionComprobante", $parametros, "http://ec.gob.sri.ws.autorizacion");
$_SESSION['autorizacionComprobante'] = $result;
$response = array();



if ($client->fault) {
    echo serialize($result);
    $result = serialize($result);
    $estado = 'ERROR';
} else {
    $error = $client->getError();
    if ($error) {
        echo serialize($error);
        $result = serialize($result);
        $estado = 'ERROR';
    } else {
        // echo serialize($result);
        if ($result['autorizaciones']['autorizacion']['estado'] != 'AUTORIZADO') {
            $resultado = serialize($result);
            $estado = $result['autorizaciones']['autorizacion']['estado'];

            $id_factura = $id_factura;
            $id_error = $result['autorizaciones']['autorizacion']["mensajes"]["mensaje"]["identificador"];
            
            $mensaje = $result['autorizaciones']['autorizacion']["mensajes"]["mensaje"][1]["mensaje"];
            $informacionAdicional = $result['autorizaciones']['autorizacion']["mensajes"]["mensaje"][0]["informacionAdicional"];
            
            $tipo = $result['autorizaciones']['autorizacion']["mensajes"]["mensaje"][0]["tipo"];

            $sqlCount = "select * from sri where id_factura ='$id_factura'";
            $rowcount = 0;


            foreach ($conn->query($sqlCount) as $row) {
                $rowcount = mysqli_num_rows($conn->query($sqlCount));
            }
        
                $sql = "INSERT INTO `sri` (`id_factura`, `id_error`, `mensaje`, `informacionAdicional`, `tipo`, `claveAcceso`, `estado`) VALUES ($id_factura, '$id_error', '$mensaje', '$informacionAdicional', '$tipo', '$claveAcceso','$estado');";
                if ($conn->query($sql) === TRUE) {
                    echo "";
                } else {
                    die("Error created record: " . $conn->error);
                }
            
        } else {
            $resultado = serialize($result);
            $estado = $result['autorizaciones']['autorizacion']['estado'];
            
            $sqlCount = "select * from sri where id_factura ='$id_factura'";
            $rowcount = 0;

            $id_factura = $id_factura;
            $id_error = 0;
            $mensaje = 'Comprobante emitido con exito';
            $informacionAdicional = 'COMPROBANTE AUTORIZADO';
            $tipo = 'MENSAJE';

            foreach ($conn->query($sqlCount) as $row) {
                $rowcount = mysqli_num_rows($conn->query($sqlCount));
            }
     
                $sql = "INSERT INTO `sri` (`id_factura`, `id_error`, `mensaje`, `informacionAdicional`, `tipo`, `claveAcceso`, `estado`) VALUES ($id_factura, '$id_error', '$mensaje', '$informacionAdicional', '$tipo', '$claveAcceso','$estado');";
                if ($conn->query($sql) === TRUE) {
                    echo "";
                } else {
                    die("Error created record: " . $conn->error);
                }
            
            
            
            if (!empty($result['autorizaciones']['autorizacion']['comprobante'])) {

                $file_comprobante = fopen('../../comprobantes/' . $claveAcceso . ".xml", "w");
                $comprobante = $client->responseData;
                $fechaAutorizacion = $result['autorizaciones']['autorizacion']['fechaAutorizacion'];

                $simplexml = simplexml_load_string($comprobante);
                $dom = new DOMDocument('1.0');
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $xml = str_replace(['&lt;', '&gt;'], ['<', '>'], $comprobante);

                fwrite($file_comprobante, $xml . PHP_EOL);
                fclose($file_comprobante);
                $xml_con_firma = '../../comprobantes/' . $claveAcceso . ".xml";


                $dataComprobante = simplexml_load_string(utf8_encode($result['autorizaciones']['autorizacion']['comprobante']));
                if ($dataComprobante->infoFactura) {
                    //     var_dump($dataComprobante->infoFactura);

                    $facturaPDF = new generarPDF();
                    $facturaPDF->facturaPDF($dataComprobante, $claveAcceso, $host_email, $email, $passEmail, $port, $fechaAutorizacion);
                }
                if ($dataComprobante->infoNotaCredito) {
                    //     var_dump($dataComprobante->infoFactura);
                    $facturaPDF = new generarPDF();
                    $facturaPDF->notaCreditoPDF($dataComprobante, $claveAcceso);
                }
                if ($dataComprobante->infoCompRetencion) {
                    //     var_dump($dataComprobante->infoFactura);
                    $facturaPDF = new generarPDF();
                    $facturaPDF->comprobanteRetencionPDF($dataComprobante, $claveAcceso);
                }
                if ($dataComprobante->infoGuiaRemision) {
                    //     var_dump($dataComprobante->infoFactura);
                    $facturaPDF = new generarPDF();
                    $facturaPDF->guiaRemisionPDF($dataComprobante, $claveAcceso);
                }

                if ($dataComprobante->infoNotaDebito) {
                    //     var_dump($dataComprobante->infoFactura);
                    $facturaPDF = new generarPDF();
                    $facturaPDF->notaDebitoPDF($dataComprobante, $claveAcceso);
                }
            }
        }
        echo serialize($result);
    }
}






