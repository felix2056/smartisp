<?php

require_once('../../lib/PHPMailer-5.2.26/PHPMailerAutoload.php');

class sendEmail {

    public function enviarCorreo($tipo, $nombre,$claveAcceso,$email,$host_email,$email_origen,$passEmail,$port) {
    
        $mail = new PHPMailer;

        $mail->isSMTP();                            // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';             // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                     // Enable SMTP authentication
        $mail->Username = 'rodrianrrango@gmail.com';          // SMTP username
        $mail->Password = 'Hopehope74A$#!'; // SMTP password
        $mail->SMTPSecure = 'tls';                  // Enable TLS encryption, `ssl` also accepted.
        $mail->Port = 587;                          // TCP port to connect to
        
        $mail->setFrom($email, 'Facturacion Electronica');

       // $mail->addAddress($email);   // Add a recipient
        $mail->addAddress($email_origen);   // Add a recipient   
        
        $mail->isHTML(true);  // Set email format to HTML

        $bodyContent = "Estimado(a):<br><bold> " . $nombre . "</bold><br> Le informamos que su comprobante electrónico ha sido emitido exitosamente y se encuentra adjunto al presente correo.";


        $mail->Subject = $tipo . ' Facturacion Electronica';
        $mail->Body = $bodyContent;
        $mail->addAttachment('../../comprobantes/'.$claveAcceso.'.pdf');
        $mail->addAttachment('../../comprobantes/'.$claveAcceso.'.xml');
        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
        }
    }

}
