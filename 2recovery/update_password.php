<?php
require 'database.php'; // 

session_start();

if (isset($_POST['Token']) && isset($_POST['Password'])) {
    $Token = $_POST['Token']; // Directly using POST data after validation
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];

    // Validate token and check for expiry
    $Stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :Token AND expires_at > NOW()");
    $Stmt->bindParam(':Token', $Token, PDO::PARAM_STR);
    $Stmt->execute();
    $Reset = $Stmt->fetch(PDO::FETCH_ASSOC);

    if ($Reset) {
        // Validate new password
        if (strlen($Password) < 8) {
            echo "The password must be at least 8 characters long.";
            exit;
        }

        if ($Password !== $ConfirmPassword) {
            echo "The passwords do not match.";
            exit;
        }

        // Hash and salt the new password
        $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);

        // Update user's password in the database
        $Stmt = $pdo->prepare("UPDATE users SET password = :Password WHERE id = :UserId");
        $Stmt->bindParam(':Password', $HashedPassword, PDO::PARAM_STR);
        $Stmt->bindParam(':UserId', $Reset['user_id'], PDO::PARAM_INT);
        $Stmt->execute();

        // Delete token from the database
        $Stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :Token");
        $Stmt->bindParam(':Token', $Token, PDO::PARAM_STR);
        $Stmt->execute();

        echo "Your password has been successfully updated.";
    } else {
        // Invalid or expired token, show error message
        echo "The reset link has expired. Please request a new one.";
    }
} else {
    // Redirect to reset request page (ResetPassword.php)
    header('Location: ResetPassword.php');
    exit;
}
