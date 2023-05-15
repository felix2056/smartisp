<?php

$mensaje = $_POST['mensaje'];


$file = fopen("../facturaFirmada.xml", "w");

fwrite($file, $mensaje . PHP_EOL);

fclose($file);