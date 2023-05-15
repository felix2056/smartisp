<?php

$firmaFactura = $_POST['respuestaFirmarFactura'];
$validarContraseña = $_POST['respuestaValidarContraseña'];
$validarVigencia = $_POST['respuestaValidarVigencia'];

if (!empty($firmaFactura)) {
    
    $file = fopen("recibe.txt", "w+");
    fwrite($file, base64_decode($firmaFactura[0]) .PHP_EOL);
    fwrite($file, base64_decode($firmaFactura[1]) .PHP_EOL);
    
    $validarComprobante = unserialize(base64_decode($firmaFactura[0]));
    $autorizacionComprobante = unserialize(base64_decode($firmaFactura[1]));
    
    var_dump($validarComprobante);
    var_dump($autorizacionComprobante);
    
    //Put Code
    
    
    
}
if(!empty($validarContraseña)){
      //Put Code
    $file = fopen("recibe.txt", "a+");
    fwrite($file, $validarContraseña .PHP_EOL);
    var_dump($validarContraseña);
}
if(!empty($validarVigencia)){
      $file = fopen("recibe.txt", "w+");
    fwrite($file,$validarVigencia[0] .PHP_EOL);
    fwrite($file, $validarVigencia[1] .PHP_EOL);
    fwrite($file, $validarVigencia[2].PHP_EOL);
    var_dump($validarVigencia);
}

