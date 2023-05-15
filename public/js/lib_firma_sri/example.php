<?php

include ('ejecutar.php');


$firmaFactura = $_POST['respuestaFirmarFactura'];
$validarContraseña = $_POST['respuestaValidarContraseña'];
$validarVigencia = $_POST['respuestaValidarVigencia'];


if(!empty($validarContraseña)){
      //Put Code
    
    $file = fopen("recibe.txt", "a+");
    fwrite($file, $validarContraseña .PHP_EOL);
    var_dump($validarContraseña);
    
      
}
if(!empty($validarVigencia)){

  //Put Code
    
    $file = fopen("recibe.txt", "w+");
    fwrite($file,$validarVigencia[0] .PHP_EOL);
    fwrite($file, $validarVigencia[1] .PHP_EOL);
    fwrite($file, $validarVigencia[2].PHP_EOL);
    var_dump($validarVigencia);
  
  //Put Code  
}

if (!empty($firmaFactura)) {
    
    $validarComprobante = $firmaFactura[0];
    $autorizacionComprobante = $firmaFactura[1];
    
    var_dump($validarComprobante);
    var_dump($autorizacionComprobante);
    
    //Put Code
    
}




$ruta_factura= '/js/lib_firma_sri/factura.xml';
$ruta_certificado= '/js/lib_firma_sri/angel_martin_pisculla_coque.p12';
$contraseña= 'Alejandro2014';
$ruta_respuesta= '/js/lib_firma_sri/example.php';

$ejecutar = new ejecutar();
$domain_dir = $_SERVER['SERVER_NAME'];

//Validar Contraseña del certificado
    //$ejecutar->validarContraseña($ruta_certificado,$contraseña,$ruta_respuesta);


//Validar Vigencia del certificado
    //$ejecutar->validarVigencia($ruta_certificado,$contraseña,$ruta_respuesta);


//Firmar Factura y enviar a SRI
    $ejecutar->firmarFactura($ruta_factura,$ruta_certificado,$contraseña,$ruta_respuesta);





