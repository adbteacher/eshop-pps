<?php
require 'database.php';
require 'jwt.php'; // JWT handling library

session_start();

if (isset($_POST['Token']) && isset($_POST['Password']) && isset($_POST['ConfirmPassword'])) {
    $Token = $_POST['Token'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    // Verify the JWT token
    $UserId = JWTHandler::VerifyToken($Token);
    if ($UserId && $UserId['exp'] > time()) {
        // Check password length
        if (strlen($Password) < 8) {
            echo "The password must be at least 8 characters long.";
            exit;
        }
        // Check if passwords match
        if ($Password !== $ConfirmPassword) {
            echo "The passwords do not match.";
            exit;
        }

        // Hash and salt the new password
        $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $Stmt = $pdo->prepare("UPDATE users SET password = :Password WHERE id = :UserId");
        $Stmt->bindParam(':Password', $HashedPassword, PDO::PARAM_STR);
        $Stmt->bindParam(':UserId', $UserId['sub'], PDO::PARAM_INT);
        $Stmt->execute();

        echo "Your password has been successfully updated.";
    } else {
        echo "The reset link has expired or is invalid. Please request a new one.";
    }
} else {
    header('Location: ResetPassword.php');
    exit;
}
