<?php

/**
 * This script handles password reset requests, logging each attempt, and uses PHPMailer for enhanced email delivery.
 * It checks user existence, account status, generates a JWT token, and sends a password reset link via email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../autoload.php';
require 'jwt.php';             // JWT handling library
require '../mail_config.php';  // PHPMailer configuration
require 'csrf.php';            // CSRF token handling

session_start();

// Redirect to login page if user is not authenticated
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php");
    exit;
}

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
    $csrfToken = $_POST['csrf_token'];
    if (!validateCsrfToken($csrfToken)) {
        echo "Invalid CSRF token.";
        exit;
    }

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
            $mail->Body    = "Hello,<br><br>If you wish to reset your password, please click on the following link:<br><br><a href='" . htmlspecialchars($resetLink) . "'>" . htmlspecialchars($resetLink) . "</a><br><br>This link will expire in 1 hour.<br><br>If you did not request this change, please ignore this email.<br><br>Regards,<br>The " . htmlspecialchars($_SERVER['HTTP_HOST']) . " team";

            $mail->send();
            $message = 'If your account exists, a password reset link has been sent to your email.';
            logPasswordResetAttempt($pdo, $user['id'], $email, $ipAddress, true);
        } catch (Exception $e) {
            $message = 'Message could not be sent. Mailer Error: ' . htmlspecialchars($mail->ErrorInfo);
            logPasswordResetAttempt($pdo, $user['id'], $email, $ipAddress, false);
        }
    } else {
        $message = "If your account exists, a password reset link will be sent to your email.";
        logPasswordResetAttempt($pdo, null, $email, $ipAddress, false);
    }
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
        }

        .section-title {
            color: #007bff;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <?php include "../nav.php"; ?>

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="section">
                    <h1 class="text-center mb-4"> <i class="fas fa-key"> </i> Password Reset</h2>
                        <?php if (isset($message)) : ?>
                            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; ?>

</body>

</html>