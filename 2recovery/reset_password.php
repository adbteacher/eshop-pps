<?php

/**
 * This script handles password reset requests.
 * It uses PHPMailer for enhanced email delivery capabilities.
 * It verifies user existence and account status, generates a JWT token, and sends a password reset link via email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoload all the composer Libraries
require 'Database.php';        // Database connection
require 'jwt.php';             // JWT handling library
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Email Format.";
        exit;
    }

    // Prepare and execute SQL statement to fetch user data
    $stmt = $pdo->prepare("SELECT id, failed_attempts, lock_until FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the account is temporarily locked
    if ($user) {
        $currentTime = new DateTime();
        if ($user['lock_until'] !== NULL && (new DateTime($user['lock_until']))->getTimestamp() > $currentTime->getTimestamp()) {
            echo "Your account is temporarily locked. Please try again later.";
            exit;
        }

        // Reset failed attempts on successful email verification
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0 WHERE id = :userId");
        $stmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
        $stmt->execute();

        // Generate a JWT for the password reset link
        $payload = [
            'sub' => $user['id'],  // Subject
            'iat' => time(),       // Issued At
            'exp' => time() + 3600 // Expiry Time
        ];
        $token = JWTHandler::createToken($payload);
        $resetLink = "https://eshop-pps-whatever.com/update_password.php?token=" . urlencode($token);

        // Setup PHPMailer
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.example.com';  // Set the SMTP server to send through
            $mail->SMTPAuth   = true;               // Enable SMTP authentication
            $mail->Username   = 'user@example.com'; // SMTP username
            $mail->Password   = 'secret';           // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                // TCP port to connect to

            //Recipients
            $mail->setFrom('no-reply@example.com', 'Mailer');
            $mail->addAddress($email);     // Add a recipient

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Password Reset';
            $mail->Body    = "Hello,<br><br>If you wish to reset your password, please click on the following link:<br><br><a href='" . $resetLink . "'>" . $resetLink . "</a><br><br>This link will expire in 1 hour.<br><br>If you did not request this change, please ignore this email.<br><br>Regards,<br>The " . $_SERVER['HTTP_HOST'] . " team";

            $mail->send();
            echo 'If your account exists, a password reset link has been sent to your email.';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    } else {
        echo "If your account exists, a password reset link will be sent to your email.";
    }
}
