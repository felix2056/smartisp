<?php

include ('ejecutar.php');

$ruta_factura= base64_encode('http://localhost/firma_sri_nuevo/factura.xml');
$ruta_certificado= base64_encode('http://localhost/firma_sri_nuevo/aire.p12');
$contraseña= base64_encode('Malawi77584');
$ruta_respuesta= base64_encode('http://localhost/firma_sri_nuevo/recibe.php');

$ejecutar = new ejecutar();
$domain_dir = $_SERVER['SERVER_NAME'];

//ValidarContraseña
header ("Location: /firma_sri_nuevo/app/validarContraseña.php?ruta_certificado=".$ruta_certificado."&contraseña=".$contraseña."&ruta_respuesta=".$ruta_respuesta); 

//validarVigencia
header ("Location: /firma_sri_nuevo/app/validarVigencia.php?ruta_certificado=".$ruta_certificado."&contraseña=".$contraseña."&ruta_respuesta=".$ruta_respuesta); 

//firmarFactura
header ("Location: /firma_sri_nuevo/app/firmarFactura.php?ruta_factura=".$ruta_factura."&ruta_certificado=".$ruta_certificado."&contraseña=".$contraseña."&ruta_respuesta=".$ruta_respuesta); 




