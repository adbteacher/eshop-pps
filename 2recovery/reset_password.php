<?php
require 'db.php';
require 'jwt.php'; // JWT handling library

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // Validate the email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid Email Format.";
        exit;
    }

    // Prepare and execute the SQL statement to fetch user data
    $stmt = $pdo->prepare("SELECT id, failed_attempts, lock_until FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the account is temporarily locked
    if ($user) {
        $current_time = new DateTime();
        if ($user['lock_until'] !== NULL && (new DateTime($user['lock_until'])) ->getTimestamp() > $current_time->getTimestamp()) {
            echo "Your account is temporarily locked. Please try again later.";
            exit;
        }

        // Reset failed attempts on successful email verification
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0 WHERE id = :userId");
        $stmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
        $stmt->execute();

        $payload = [
            'sub' => $user['id'],  // Subject
            'iat' => time(),       // Issued at
            'exp' => time() + 3600 // Expiration time
        ];
        // Generate a JWT for the password reset link
        $token = JWTHandler::createToken($payload);

        // Construct the password reset link using the JWT
        $resetLink = "https://eshop-pps-whatever.com/update_password.php?token=" . urlencode($token);
        $subject = "Password Reset";
        $message = "Hello,\n\nIf you wish to reset your password for " . $_SERVER['HTTP_HOST'] . ", please click on the following link:\n\n" . $resetLink . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this change, please ignore this email.\n\nRegards,\nThe " . $_SERVER['HTTP_HOST'] . " team";
        $headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        mail($email, $subject, $message, $headers);

        echo "If your account exists, a password reset link will be sent to your email.";
    } else {
        echo "If your account exists, a password reset link will be sent to your email.";
    }
}
