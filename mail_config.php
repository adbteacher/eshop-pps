<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php'; // Autoload all the composer Libraries

/**
 * Configures and returns an instance of PHPMailer.
 * @return PHPMailer Configured PHPMailer instance.
 */
function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.example.com';  // Set the SMTP server to send through
    $mail->SMTPAuth   = true;               // Enable SMTP authentication
    $mail->Username   = 'user@example.com'; // SMTP username
    $mail->Password   = 'secret';           // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;                // TCP port to connect to

    //Recipients
    $mail->setFrom('no-reply@example.com', 'Mailer');

    return $mail;
}
