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

$pdo = database::LoadDatabase();

function generateCaptchaQuestion()
{
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_question'] = "$num1 + $num2";
    $_SESSION['captcha_answer'] = $num1 + $num2;
}

// Initialize captcha question if not set
if (!isset($_SESSION['captcha_question'])) {
    generateCaptchaQuestion();
}

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

function checkAttemptsAndLock(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare("SELECT usu_id FROM pps_users WHERE usu_email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['usu_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) AS attempt_count, MAX(lor_datetime) AS last_attempt, MAX(lor_lock_until) AS lock_until FROM pps_logs_recovery WHERE lor_user = :userId AND lor_datetime >= NOW() - INTERVAL 1 HOUR");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentTime = new DateTime();
        $lockUntil = $result['lock_until'] ? new DateTime($result['lock_until']) : null;

        if ($result['attempt_count'] >= 3 && $lockUntil && $currentTime < $lockUntil) {
            return false; // Account is still locked
        }

        if ($result['attempt_count'] >= 3) {
            $lockTime = $currentTime->add(new DateInterval('PT30M'))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE pps_users SET usu_reset_token = NULL WHERE usu_id = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("INSERT INTO pps_logs_recovery (lor_user, lor_ip, lor_datetime, lor_attempt, lor_lock_until) VALUES (:userId, '127.0.0.1', NOW(), 0, :lockTime)");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':lockTime', $lockTime, PDO::PARAM_STR);
            $stmt->execute();

            return false; // Account is now locked for 30 minutes
        }
    }

    return true;
}

function incrementFailedAttempts(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("INSERT INTO pps_logs_recovery (lor_user, lor_ip, lor_datetime, lor_attempt) VALUES (:userId, '127.0.0.1', NOW(), 0)");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    $csrfToken = $_POST['csrf_token'];
    if (!validateCsrfToken($csrfToken)) {
        $message = "Token CSRF no válido.";
        generateCaptchaQuestion();
    } else {
        // Verificación de CAPTCHA
        if ($_POST['captcha_answer'] != $_SESSION['captcha_answer']) {
            $message = "Respuesta CAPTCHA incorrecta, estudia matemática básica.";
            generateCaptchaQuestion();
        } else {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $ipAddress = $_SERVER['REMOTE_ADDR']; // Capture the IP address of the client making the request

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Formato de correo electrónico no válido.";
                generateCaptchaQuestion();
            } else {
                // Prepare and execute SQL statement to fetch user data
                $stmt = $pdo->prepare("SELECT usu_id FROM pps_users WHERE usu_email = :email LIMIT 1");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    if (!checkAttemptsAndLock($pdo, $email)) {
                        $message = "Su cuenta está bloqueada temporalmente. Por favor, inténtelo de nuevo más tarde.";
                    } else {
                        // Increment failed attempts
                        incrementFailedAttempts($pdo, $user['usu_id']);

                        // Generate a JWT for the password reset link
                        $payload = [
                            'sub' => $user['usu_id'],  // Subject
                            'iat' => time(),           // Issued At
                            'exp' => time() + 3600     // Expiry Time
                        ];
                        $token = JWTHandler::createToken($payload);

                        // Save the token to invalidate previous tokens
                        $stmt = $pdo->prepare("UPDATE pps_users SET usu_reset_token = :token WHERE usu_id = :userId");
                        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                        $stmt->bindParam(':userId', $user['usu_id'], PDO::PARAM_INT);
                        $stmt->execute();

                        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/update_password.php?token=" . urlencode($token);

                        // Setup PHPMailer
                        $mail = getMailer();
                        try {
                            $mail->addAddress($email);     // Add a recipient

                            // Content
                            $mail->isHTML(true);           // Set email format to HTML
                            $mail->Subject = 'Restablecimiento de password';
                            $mail->Body    = "Hola,<br><br>Si desea restablecer su password, haga clic en el siguiente enlace:<br><br><a href='" . htmlspecialchars($resetLink) . "'>" . htmlspecialchars($resetLink) . "</a><br><br>Este enlace expira en 1 hora.<br><br>Si no has solicitado este cambio, ignore este email.<br><br>Saludos,<br>el equipo de " . htmlspecialchars($_SERVER['HTTP_HOST']);

                            $mail->send();
                            $message = 'Si su cuenta existe, se enviará un enlace para restablecer la contraseña a su correo electrónico.';
                            logPasswordResetAttempt($pdo, $user['usu_id'], $ipAddress, true);
                        } catch (Exception $e) {
                            $message = 'No se pudo enviar el mensaje. Error de envío: ' . htmlspecialchars($mail->ErrorInfo);
                            logPasswordResetAttempt($pdo, $user['usu_id'], $ipAddress, false);
                        }
                    }
                } else {
                    $message = "Si su cuenta existe, se enviará un enlace para restablecer la contraseña a su correo electrónico.";
                    logPasswordResetAttempt($pdo, null, $ipAddress, false);
                }

                // Clear CAPTCHA session data and generate a new question
                generateCaptchaQuestion();
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
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
                    <h1 class="text-center mb-4"> <i class="fas fa-key"></i> Restablecer contraseña</h1>
                    <?php if (!empty($message)) : ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Dirección de correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="captcha_question" class="form-label">Pregunta de seguridad:
                                <?= htmlspecialchars($_SESSION['captcha_question']) ?></label>
                            <input type="text" class="form-control" id="captcha_answer" name="captcha_answer" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; ?>

</body>

</html>