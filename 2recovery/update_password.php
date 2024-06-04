<?php

/**
 * This script manages updating a user's password.
 * It verifies the provided JWT token and updates the password if the token is valid.
 */

require_once '../autoload.php'; // Autoload necessary classes and Database connection
require 'jwt.php'; // JWT handling library
require 'csrf.php'; // CSRF handling
session_start();

// Redirect to login page if user is not authenticated
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php");
    exit;
}

$pdo = database::LoadDatabase();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['token']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
    $csrfToken = $_POST['csrf_token'];
    if (!validateCsrfToken($csrfToken)) {
        $message = "Token CSRF no válido.";
    } else {
        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        // Verify the JWT token
        $userId = JWTHandler::verifyToken($token);
        if ($userId && $userId['exp'] > time()) {
            // Check password strength
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
                $message = "La contraseña debe tener al menos 8 caracteres e incluir al menos una letra mayúscula, una letra minúscula y un dígito.";
            } elseif ($password !== $confirmPassword) {
                $message = "Las contraseñas no coinciden.";
            } else {
                // Hash and salt the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update the user's password in the database
                $stmt = $pdo->prepare("UPDATE pps_users SET usu_password = :password WHERE usu_id = :userId");
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':userId', $userId['sub'], PDO::PARAM_INT);
                $stmt->execute();

                $message = "Su contraseña ha sido actualizada satisfactoriamente.";
            }
        } else {
            $message = "El enlace de reinicio ha caducado o no es válido. Por favor solicite uno nuevo.";
        }
    }
} else {
    $token = $_GET['token'] ?? '';
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar contraseña</title>
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
                    <h1 class="text-center mb-4"> <i class="fas fa-unlock-alt"></i> Actualizar contraseña</h1>
                    <?php if (isset($message)) : ?>
                        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Confirmar cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; ?>

</body>

</html>