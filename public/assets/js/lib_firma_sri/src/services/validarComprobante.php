<?php
session_start();
require_once('../../lib/nusoap.php');

header("Content-Type: text/plain");

$content = file_get_contents("../../facturaFirmada.xml");
$mensaje = base64_encode($content);

$claveAcceso = $_POST['claveAcceso'];
$service = $_POST['service'];

//EndPoint
$servicio = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl"; //url del servicio
$parametros = array(); //parametros de la llamada
$parametros['xml'] = $mensaje;

$client = new nusoap_client($servicio);


$client->soap_defencoding = 'utf-8';


$result = $client->call("validarComprobante", $parametros, "http://ec.gob.sri.ws.recepcion");
$response = array();

$file = fopen("../../log.txt", "a+");
fwrite($file, "Servicio: " . $service . PHP_EOL);
fwrite($file, "Clave Acceso: " . $claveAcceso . PHP_EOL);

//var_dump($client->getError());die;


$_SESSION['validarComprobante']=$result;

if ($client->fault) {  
    
    $file_error = fopen('../../errores/'.$claveAcceso.".txt", "w");
    fwrite($file_error, "Servicio: " . $service . PHP_EOL);
    fwrite($file_error, "Clave Acceso: " . $claveAcceso . PHP_EOL);
    fwrite($file_error, "Respuesta: " . print_r($result,true) . PHP_EOL);
    fwrite($file_error, "\n__________________________________________________________________\n". PHP_EOL);
    fclose($file_error);
    fwrite($file, "Respuesta: " . print_r($result,true) . PHP_EOL);
    echo serialize($result);
    
} else {
    $error = $client->getError();
    if ($error) {
        fwrite($file, "Respuesta: " . print_r($error,true) . PHP_EOL);
        $file_error = fopen('../../errores/'.$claveAcceso.".txt", "w");
        fwrite($file_error, "Servicio: " . $service . PHP_EOL);
        fwrite($file_error, "Clave Acceso: " . $claveAcceso . PHP_EOL);
        fwrite($file_error, "Respuesta: " . print_r($error,true) . PHP_EOL);
        fwrite($file_error, "\n__________________________________________________________________\n". PHP_EOL);
        fclose($file_error);
        echo serialize($error);
    } else {
        if ($result['estado']=='RECIBIDA'){
            fwrite($file, "Respuesta: " . print_r($result,true) . PHP_EOL);
        }else {
            fwrite($file, "Respuesta: " . print_r($result,true) . PHP_EOL);
            $file_error = fopen('../../errores/'.$claveAcceso.".txt", "w");
            fwrite($file_error, "Servicio: " . $service . PHP_EOL);
            fwrite($file_error, "Clave Acceso: " . $claveAcceso . PHP_EOL);            
            fwrite($file_error, "Respuesta: " . print_r($result,true) . PHP_EOL);
            fwrite($file_error, "\n__________________________________________________________________\n". PHP_EOL);
            fclose($file_error);
        }
        echo serialize($result);
        
    }
}
fwrite($file, "\n__________________________________________________________________\n". PHP_EOL);
fclose($file);




