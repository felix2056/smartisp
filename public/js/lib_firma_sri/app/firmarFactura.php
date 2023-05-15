<?php
session_start();
$_SESSION['ruta_factura'] = base64_decode($_GET['ruta_factura']);
$ruta_certificado = base64_decode($_GET['ruta_certificado']);
$contraseña = base64_decode($_GET['contraseña']);
$ruta_respuesta = base64_decode($_GET['ruta_respuesta']);

?>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
    <div id="response">

    </div>

</body>
<script src="../js/fiddle.js"></script>
<script src="../js/forge.min.js"></script>
<script src="../js/moment.min.js"></script>
<script src="../js/buffer.js"></script>

<script>
    
    $(document).ready(function ()
    {
        resultados = obtenerComprobanteFirmado_sri('<?php echo $ruta_certificado; ?>','<?php echo $contraseña; ?>','<?php echo $ruta_respuesta; ?>');
    })

</script>    

