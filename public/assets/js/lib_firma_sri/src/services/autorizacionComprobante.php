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
echo "Connected successfully";




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

$file = fopen("../../log.txt", "a+");
fwrite($file, "Servicio: " . $service . PHP_EOL);
fwrite($file, "Clave Acceso: " . $claveAcceso . PHP_EOL);




if ($client->fault) {
    echo serialize($result);
    $result = serialize($result);
    $estado = 'ERROR';
    $sql = "UPDATE `bill_customers` SET `estado`= '10' WHERE  `id`=$id_factura;";
                if ($conn->query($sql) === TRUE) {
                    echo "Record updated successfully";
                } else {
                    echo "Error created record: " . $conn->error;
                }
    
} else {
    $error = $client->getError();
    
    if ($error) {
        echo serialize($error);
        $result = serialize($result);
        $estado = 'ERROR';
        $sql = "UPDATE `bill_customers` SET `estado`= '10' WHERE  `id`=$id_factura;";
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error created record: " . $conn->error;
        }
        
    } else {
       // echo serialize($result);
        if ($result['autorizaciones']['autorizacion']['estado'] != 'AUTORIZADO') {
            $resultado = serialize($result);
            $estado = $result['autorizaciones']['autorizacion']['estado'];

            $sql = "UPDATE `bill_customers` SET `estado`= '10' WHERE  `id`=$id_factura;";
            if ($conn->query($sql) === TRUE) {
                echo "Record updated successfully";
            } else {
                echo "Error created record: " . $conn->error;
            }
            
        } else {
            $resultado = serialize($result);
            $estado = $result['autorizaciones']['autorizacion']['estado'];
            $sql = "UPDATE `bill_customers` SET `estado`= '9' WHERE  `id`=$id_factura;";
            if ($conn->query($sql) === TRUE) {
                echo "Record updated successfully";
            } else {
                echo "Error created record: " . $conn->error;
            }
            if (!empty($result['autorizaciones']['autorizacion']['comprobante'])) {
                $file_comprobante = fopen('../../comprobantes/' . $claveAcceso . ".xml", "w");
                $comprobante = $client->responseData;


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
                    $facturaPDF->facturaPDF($dataComprobante, $claveAcceso, $host_email, $email, $passEmail, $port);
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
    }
}
fwrite($file, "\n__________________________________________________________________\n" . PHP_EOL);
fclose($file);





