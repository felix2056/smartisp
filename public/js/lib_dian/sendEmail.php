<?php

require_once('lib/PHPMailer-5.2.26/PHPMailerAutoload.php');

class sendEmail {

    public function enviarCorreo($tipo, $nombre,$filename,$email,$host_email,$email_origen,$passEmail,$port) {
    
        $mail = new PHPMailer;

     $mail->IsSMTP(); // enable SMTP

        $mail->SMTPAuth = true;                     // Enable SMTP authentication
        $mail->Host = $host_email;
        $mail->Username = $email_origen;          // SMTP username
        $mail->Password = $passEmail; // SMTP password
        $mail->SMTPSecure = 'ssl';                  // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $port;                          // TCP port to connect to
        
        
        $mail->setFrom($email, 'Facturacion Electronica');

        $mail->addAddress($email);   // Add a recipient
        $mail->addAddress($email_origen);   // Add a recipient   
        
        $mail->isHTML(true);  // Set email format to HTML

        $bodyContent = "Estimado(a):<br><bold> " . $nombre . "</bold><br> Le informamos que su comprobante electrónico ha sido emitido exitosamente y se encuentra adjunto al presente correo.";


        $mail->Subject = $tipo . ' Facturacion Electronica';
        $mail->Body = $bodyContent;
        $mail->addAttachment('comprobantes/'.$filename.'.pdf');
        $mail->addAttachment('comprobantes/'.$filename.'.xml');
        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
        }
    }

}
