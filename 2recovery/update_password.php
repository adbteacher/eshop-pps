<?php

/**
 * This script manages updating a user's password.
 * It verifies the provided JWT token and updates the password if the token is valid.
 */

require 'Database.php';
require 'jwt.php'; // JWT handling library
session_start();

if (isset($_POST['token']) && isset($_POST['password']) && isset($_POST['confirmPassword'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Verify the JWT token
    $userId = JWTHandler::verifyToken($token);
    if ($userId && $userId['exp'] > time()) {
        // Check password length
        if (strlen($password) < 8) {
            echo "The password must be at least 8 characters long.";
            exit;
        }

        // Check if passwords match
        if ($password !== $confirmPassword) {
            echo "The passwords do not match.";
            exit;
        }

        // Hash and salt the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :userId");
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId['sub'], PDO::PARAM_INT);
        $stmt->execute();

        echo "Your password has been successfully updated.";
    } else {
        echo "The reset link has expired or is invalid. Please request a new one.";
    }
} else {
    header('Location: resetPassword.php');
    exit;
}
