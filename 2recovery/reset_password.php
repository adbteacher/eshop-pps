<?php
require 'database.php';
require 'jwt.php'; // JWT handling library

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Email'])) {
    $Email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);

    // Validate the email format
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Email Format.";
        exit;
    }

    // Prepare and execute the SQL statement to fetch user data
    $Stmt = $pdo->prepare("SELECT Id, failed_attempts, lock_until FROM Users WHERE Email = :Email LIMIT 1");
    $Stmt->bindParam(':Email', $Email, PDO::PARAM_STR);
    $Stmt->execute();
    $User = $Stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the account is temporarily locked
    if ($User) {
        $current_time = new DateTime();
        if ($User['lock_until'] !== NULL && new DateTime($User['lock_until']) > $current_time) {
            echo "Your account is temporarily locked. Please try again later.";
            exit;
        }

        // Reset failed attempts on successful email verification
        $Stmt = $pdo->prepare("UPDATE Users SET failed_attempts = 0 WHERE Id = :User_Id");
        $Stmt->bindParam(':User_Id', $User['Id'], PDO::PARAM_INT);
        $Stmt->execute();

        $Payload = [
            'sub' => $User['Id'],  // Subject
            'iat' => time(),       // Issued at
            'exp' => time() + 3600 // Expiration time
        ];
        // Generate a JWT for the password reset link
        $Token = JWTHandler::CreateToken($Payload);

        // Construct the password reset link using the JWT
        $ResetLink = "https://eshop-pps-whatever.com/update_password.php?token=" . urlencode($Token);
        $Subject = "Password Reset";
        $Message = "Hello,\n\nIf you wish to reset your password for " . $_SERVER['HTTP_HOST'] . ", please click on the following link:\n\n" . $ResetLink . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this change, please ignore this email.\n\nRegards,\nThe " . $_SERVER['HTTP_HOST'] . " team";
        $Headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        mail($Email, $Subject, $Message, $Headers);

        echo "If your account exists, a password reset link will be sent to your email.";
    } else {
        echo "If your account exists, a password reset link will be sent to your email.";
    }
}
