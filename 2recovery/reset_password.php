<?php
require 'database.php'; // 

// ReCAPTCHA Verification (Implement ReCAPTCHA Integration)

Session_Start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Email'])) {
    $Email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);

    // Validate Email Format
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Email Format.";
        exit;
    }

    // Validate if the Email Exists in the Database
    $Stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = :Email LIMIT 1");
    $Stmt->bindParam(':Email', $Email, PDO::PARAM_STR);
    $Stmt->execute();
    $User = $Stmt->fetch(PDO::FETCH_ASSOC);

    if ($User) {
        // Generate a Secure Token (Hashed and Salted)
        $Token = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $ExpiresAt = new DateTime('+1 hour'); // Expires in 1 Hour

        // Insert Token into the Database
        $Stmt = $pdo->prepare("INSERT INTO Password_Resets (User_Id, Token, Expires_At) VALUES (:User_Id, :Token, :Expires_At)");
        $Stmt->execute([
            ':User_Id' => $User['Id'],
            ':Token' => $Token,
            ':Expires_At' => $ExpiresAt->format('Y-m-d H:i:s')
        ]);

        // Send Email with Reset Link
        $ResetLink = "https://your-site.com/ResetPassword.php?token=" . urlencode($Token);
        $Subject = "Password Reset";
        $Message = "Hello,\n\nIf you wish to reset your password for " . $_SERVER['HTTP_HOST'] . ", please click on the following link:\n\n" . $ResetLink . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this change, please ignore this email.\n\nRegards,\nThe " . $_SERVER['HTTP_HOST'] . " team";
        $Headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        mail($Email, $Subject, $Message, $Headers);

        echo "If your account exists, a password reset link will be sent to your email.";
    } else {
        echo "If your account exists, a password reset link will be sent to your email."; // Same Message to Maintain Ambiguity
    }
}
