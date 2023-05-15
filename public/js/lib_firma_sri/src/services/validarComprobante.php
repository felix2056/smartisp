<?php

session_start();
require_once('../../lib/nusoap.php');

header("Content-Type: text/plain");

$content = file_get_contents("../../facturaFirmada.xml");
$mensaje = base64_encode($content);

$claveAcceso = $_POST['claveAcceso'];
$service = $_POST['service'];

//Conexion BD

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
$servicio = "https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl"; //url del servicio
$parametros = array(); //parametros de la llamada
$parametros['xml'] = $mensaje;

$client = new nusoap_client($servicio);


$client->soap_defencoding = 'utf-8';


$result = $client->call("validarComprobante", $parametros, "http://ec.gob.sri.ws.recepcion");
$response = array();


//var_dump($client->getError());die;


$_SESSION['validarComprobante'] = $result;

if ($client->fault) {

    ;
    echo serialize($result);
} else {
    $error = $client->getError();
    if ($error) {

        echo serialize($error);
    } else {
        if ($result['estado'] == 'RECIBIDA') {
            $estado = $result['estado'];
            $id_factura = $id_factura;
            $id_error = 0;
            $mensaje = 'Comprobante Recibido';
            $informacionAdicional = 'Pendiente de autorizacion';
            $tipo = 'MENSAJE';

            $sqlCount = "select * from sri where id_factura ='$id_factura'";
            $rowcount = 0;


            foreach ($conn->query($sqlCount) as $row) {
                $rowcount = mysqli_num_rows($conn->query($sqlCount));
            }

                $sql = "INSERT INTO `sri` (`id_factura`, `id_error`, `mensaje`, `informacionAdicional`, `tipo`, `claveAcceso`, `estado`) VALUES ($id_factura, '$id_error', '$mensaje', \"$informacionAdicional\", '$tipo', '$claveAcceso','$estado');";


                if ($conn->query($sql) === TRUE) {
                    echo "";
                } else {
                    die("Error created record: " . $conn->error);
                }

        } else {
            $estado = $result['estado'];
            $id_factura = $id_factura;
            $id_error = $result["comprobantes"]["comprobante"]["mensajes"]["mensaje"]["identificador"];
            $mensaje = $result["comprobantes"]["comprobante"]["mensajes"]["mensaje"]["mensaje"];
            $informacionAdicional = $result["comprobantes"]["comprobante"]["mensajes"]["mensaje"]["informacionAdicional"];
            $tipo = $result["comprobantes"]["comprobante"]["mensajes"]["mensaje"]["tipo"];

            $sqlCount = "select * from sri where id_factura ='$id_factura'";
            $rowcount = 0;


            foreach ($conn->query($sqlCount) as $row) {
                $rowcount = mysqli_num_rows($conn->query($sqlCount));
            }

                $sql = "INSERT INTO `sri` (`id_factura`, `id_error`, `mensaje`, `informacionAdicional`, `tipo`, `claveAcceso`, `estado`) VALUES ($id_factura, '$id_error', '$mensaje', \"$informacionAdicional\", '$tipo', '$claveAcceso','$estado');";
                if ($conn->query($sql) === TRUE) {
                    echo "";
                } else {
                    die("Error created record: " . $conn->error);
                }

            echo $estado;
        }
        echo serialize($result);
    }
}




