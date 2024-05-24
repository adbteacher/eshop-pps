<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'autoload.php'; // Autoload all the composer Libraries

/**
 * Configures and returns an instance of PHPMailer.
 * @return PHPMailer Configured PHPMailer instance.
 */
function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    //Server settings
    $mail->isSMTP();
    $mail->Host       = getenv('MAIL_HOST');      // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                     // Enable SMTP authentication
    $mail->Username   = getenv('MAIL_USERNAME');  // SMTP username
    $mail->Password   = getenv('MAIL_PASSWORD');  // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;                      // TCP port to connect to

    //Recipients
    $mail->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));

    return $mail;
}
