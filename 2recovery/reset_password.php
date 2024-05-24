<?php

/**
 * This script handles password reset requests, logging each attempt, and uses PHPMailer for enhanced email delivery.
 * It checks user existence, account status, generates a JWT token, and sends a password reset link via email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoload all the composer Libraries
require 'Database.php';        // Database connection
require 'jwt.php';             // JWT handling library
require '../mail_config.php';     // PHPMailer configuration

session_start();

function logPasswordResetAttempt(PDO $pdo, ?int $userId, string $email, string $ipAddress, bool $isSuccessful): void
{
    $stmt = $pdo->prepare("INSERT INTO pps_logs_recovery (lor_user, lor_email, lor_ip, lor_datetime, lor_attempt) VALUES (:userId, :email, :ipAddress, NOW(), :isSuccessful)");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':ipAddress', $ipAddress, PDO::PARAM_STR);
    $stmt->bindParam(':isSuccessful', $isSuccessful, PDO::PARAM_BOOL);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $ipAddress = $_SERVER['REMOTE_ADDR']; // Capture the IP address of the client making the request

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
            logPasswordResetAttempt($pdo, $user['id'], $email, $ipAddress, false);
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
        $resetLink = dirname($_SERVER['PHP_SELF']) . "/update_password.php?token=" . urlencode($token);

        // Setup PHPMailer
        $mail = getMailer();
        try {
            $mail->addAddress($email);     // Add a recipient

            // Content
            $mail->isHTML(true);           // Set email format to HTML
            $mail->Subject = 'Password Reset';
            $mail->Body    = "Hello,<br><br>If you wish to reset your password, please click on the following link:<br><br><a href='" . $resetLink . "'>" . $resetLink . "</a><br><br>This link will expire in 1 hour.<br><br>If you did not request this change, please ignore this email.<br><br>Regards,<br>The " . $_SERVER['HTTP_HOST'] . " team";

            $mail->send();
            echo 'If your account exists, a password reset link has been sent to your email.';
            logPasswordResetAttempt($pdo, $user['id'], $email, $ipAddress, true);
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            logPasswordResetAttempt($pdo, $user['id'], $email, $ipAddress, false);
        }
    } else {
        echo "If your account exists, a password reset link will be sent to your email.";
        logPasswordResetAttempt($pdo, null, $email, $ipAddress, false);
    }
}
