<?php

/**
 * This script handles password reset requests, logging each attempt, and uses PHPMailer for enhanced email delivery.
 * It checks user existence, account status, generates a JWT token, and sends a password reset link via email.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../autoload.php'; // Autoload necessary classes and Database connection
require 'jwt.php';              // JWT handling library
require '../mail_config.php';   // PHPMailer configuration
require 'csrf.php';             // CSRF token handling

session_start();

// Redirect to login page if user is not authenticated
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php");
    exit;
}

$pdo = database::LoadDatabase();
$userEmail = $_SESSION['UserEmail'];

function logPasswordResetAttempt(PDO $pdo, ?int $userId, string $ipAddress, bool $isSuccessful): void
{
    $stmt = $pdo->prepare("INSERT INTO pps_logs_recovery (lor_user, lor_ip, lor_datetime, lor_attempt) VALUES (:userId, :ipAddress, NOW(), :isSuccessful)");
    if ($userId === null) {
        $stmt->bindValue(':userId', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    }
    $stmt->bindParam(':ipAddress', $ipAddress, PDO::PARAM_STR);
    $stmt->bindParam(':isSuccessful', $isSuccessful, PDO::PARAM_BOOL);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrfToken = $_POST['csrf_token'];
    if (!validateCsrfToken($csrfToken)) {
        echo "Token CSRF no válido.";
        exit;
    }

    $email = $userEmail; // Use the email from the session
    $ipAddress = $_SERVER['REMOTE_ADDR']; // Capture the IP address of the client making the request

    // Prepare and execute SQL statement to fetch user data
    $stmt = $pdo->prepare("SELECT usu_id, lor_attempt, lor_lock_until FROM pps_users LEFT JOIN pps_logs_recovery ON pps_users.usu_id = pps_logs_recovery.lor_user WHERE usu_email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the account is temporarily locked
    if ($user) {
        $currentTime = new DateTime();
        if ($user['lor_lock_until'] !== NULL && (new DateTime($user['lor_lock_until']))->getTimestamp() > $currentTime->getTimestamp()) {
            echo "Su cuenta está bloqueada temporalmente. Por favor, inténtelo de nuevo más tarde.";
            logPasswordResetAttempt($pdo, $user['usu_id'], $ipAddress, false);
            exit;
        }

        // Reset failed attempts on successful email verification
        $stmt = $pdo->prepare("UPDATE pps_logs_recovery SET lor_attempt = 0 WHERE lor_user = :userId");
        $stmt->bindParam(':userId', $user['usu_id'], PDO::PARAM_INT);
        $stmt->execute();

        // Generate a JWT for the password reset link
        $payload = [
            'sub' => $user['usu_id'],  // Subject
            'iat' => time(),           // Issued At
            'exp' => time() + 3600     // Expiry Time
        ];
        $token = JWTHandler::createToken($payload);
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/update_password.php?token=" . urlencode($token);

        // Setup PHPMailer
        $mail = getMailer();
        try {
            $mail->addAddress($email);     // Add a recipient

            // Content
            $mail->isHTML(true);           // Set email format to HTML
            $mail->Subject = 'Restablecimiento de contraseña';
            $mail->Body    = "Hola,<br><br>Si deseas restablecer tu contraseña, por favor, haga click en el siguiente enlace:<br><br><a href='" . htmlspecialchars($resetLink) . "'>" . htmlspecialchars($resetLink) . "</a><br><br>This link will expire in 1 hour.<br><br>If you did not request this change, please ignore this email.<br><br>Regards,<br>The " . htmlspecialchars($_SERVER['HTTP_HOST']) . " team";

            $mail->send();
            $message = 'Si su cuenta existe, se enviará un enlace para restablecer la contraseña a su correo electrónico.';
            logPasswordResetAttempt($pdo, $user['usu_id'], $ipAddress, true);
        } catch (Exception $e) {
            $message = 'No se pudo enviar el mensaje. Error de envío: ' . htmlspecialchars($mail->ErrorInfo);
            logPasswordResetAttempt($pdo, $user['usu_id'], $ipAddress, false);
        }
    } else {
        $message = "Si su cuenta existe, se enviará un enlace para restablecer la contraseña a su correo electrónico.";
        logPasswordResetAttempt($pdo, null, $ipAddress, false);
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
                    <h1 class="text-center mb-4"> <i class="fas fa-key"></i> Recuperar contraseña</h1>
                    <?php if (isset($message)) : ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Dirección de correo electrónico</label>
                            <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($userEmail) ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Restablecer contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; ?>

</body>

</html>