<?php

$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];

$fechaInicio = substr($fechaInicio,4, 11);
$fechaInicio = date('Y-m-d',strtotime($fechaInicio));


$fechaFin = substr($fechaFin,4, 11);
$fechaFin= date('Y-m-d',strtotime($fechaFin));


$fechaActual=date('Y-m-d');

if($fechaActual <= $fechaFin){
    echo "Certificado Vigente";
}else{
    $file = fopen("../error_log", "a+");
    $date = date('m/d/Y h:i:s a', time());
    fwrite($file, "Error: " .$date. ' Fecha vencimiento del certificado excedida'. PHP_EOL);
    echo "Valide las fechas de vencimiento del certificado";
}