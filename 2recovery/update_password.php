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
        $message = "Invalid CSRF token.";
    } else {
        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        // Verify the JWT token
        $userId = JWTHandler::verifyToken($token);
        if ($userId && $userId['exp'] > time()) {
            // Check password strength
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
                $message = "The password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one digit.";
            } elseif ($password !== $confirmPassword) {
                $message = "The passwords do not match.";
            } else {
                // Hash and salt the new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update the user's password in the database
                $stmt = $pdo->prepare("UPDATE pps_users SET usu_password = :password WHERE usu_id = :userId");
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':userId', $userId['sub'], PDO::PARAM_INT);
                $stmt->execute();

                $message = "Your password has been successfully updated.";
            }
        } else {
            $message = "The reset link has expired or is invalid. Please request a new one.";
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
    <title>Update Password</title>
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
                    <h1 class="text-center mb-4"> <i class="fas fa-unlock-alt"></i> Update Password</h1>
                    <?php if (isset($message)) : ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; ?>

</body>

</html>