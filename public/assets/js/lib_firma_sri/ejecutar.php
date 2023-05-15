<?php

include ('app/includes.php');


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ejecutar
 *
 * @author UESR
 */
class ejecutar {

    public $conn;

//put your code here
    public function __construct() {
        $this->conn = true;
        
    }

    //put your code here
    public function validarContraseña($ruta_certificado, $contraseña, $ruta_respuesta) {

        $_SESSION['ruta_factura'] = $ruta_comprobante;
        
        //ejecuta funcion js obtenerComprobanteFirmado_sri en fiddle.js
        
        echo '<script type="text/javascript">',
        'try {
         validar_pwrd("' . $ruta_certificado . '","' . $contraseña . '","' .$ruta_respuesta. '")
         }catch(err) {
         document.getElementById("demo").innerHTML = err.message;
         }',
        '</script>'
        ;
        
        return true;
        //Tiempo limite
    }
    
    public function validarVigencia($ruta_certificado, $contraseña, $ruta_respuesta) {
              
        //ejecuta funcion js obtenerComprobanteFirmado_sri en fiddle.js
        
        echo '<script type="text/javascript">',
        'try {
         fechas_certificado("' . $ruta_certificado . '","' . $contraseña . '","' .$ruta_respuesta. '")
         }catch(err) {
         document.getElementById("demo").innerHTML = err.message;
         }',
        '</script>'
        ;
        return true;
        //Tiempo limite
    }
    
    public function firmarFactura($ruta_factura ,$ruta_certificado, $contraseña, $ruta_respuesta) {
        //ejecuta funcion js obtenerComprobanteFirmado_sri en fiddle.js
        
        echo '<script type="text/javascript">',
        'try {
         obtenerComprobanteFirmado_sri("' . $ruta_certificado . '","' . $contraseña . '","' .$ruta_respuesta. '","' .$ruta_factura.'")
         }catch(err) {
         document.getElementById("demo").innerHTML = err.message;
         }',
        '</script>'
        ;
        return true;
        //Tiempo limite
    }

}
