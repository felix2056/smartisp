<?php

$certificado_p12 = file_get_contents("jessica_elizabeth_vera_arevalo.p12");
if (openssl_pkcs12_read($certificado_p12, $pkcs12, 'Jes120396')) {
$certificado = $pkcs12["extracerts"][1];
$certificado = str_replace('-----BEGIN CERTIFICATE-----', '', $certificado);
$certificado = str_replace('-----END CERTIFICATE-----', '', $certificado);
$certificado=trim($certificado);
echo $certificado;
}
