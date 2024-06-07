<?php

// Include external files for database connection and autoload functions
require_once '../autoload.php';

// Use the TwoFactorAuth library from RobThree
use RobThree\Auth\TwoFactorAuth;

// Define constants for login wait time, max login attempts, and session lifetime
define('LOGIN_WAIT_TIME', 5);
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_LIFETIME', 900);

// Function to get the database connection, using a static variable to ensure only one connection is made
function GetDatabaseConnection(): PDO
{
    static $Connection;
    if ($Connection === null)
    {
        $Connection = Database::LoadDatabase();
    }
    return $Connection;
}

// Function to add various security headers to the HTTP response
function AddSecurityHeaders(): void
{
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'self';");
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
}

// Function to sanitize user input by trimming, stripping slashes, and converting special characters to HTML entities
function SanitizeInput(string $Input): string
{
    return htmlspecialchars(stripslashes(trim($Input)), ENT_QUOTES, 'UTF-8');
}

// Function to check the number of login attempts for a user within a specified time frame
function CheckLoginAttempts(int $UserId): bool
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Ip = $_SERVER['REMOTE_ADDR'];
        if ($UserId == 0)
        {
            return false;
        }
        $maxAttempts = MAX_LOGIN_ATTEMPTS;
        $waitTime = LOGIN_WAIT_TIME;
        $Query = $Connection->prepare(
            "SELECT lol_was_correct_login 
             FROM pps_logs_login 
             WHERE lol_ip = :ip AND lol_user = :user_id AND lol_datetime > DATE_SUB(NOW(), INTERVAL :wait_time MINUTE)
             ORDER BY lol_datetime DESC 
             LIMIT :max_attempts"
        );
        $Query->bindParam(':ip', $Ip, PDO::PARAM_STR);
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->bindParam(':wait_time', $waitTime, PDO::PARAM_INT);
        $Query->bindParam(':max_attempts', $maxAttempts, PDO::PARAM_INT);
        $Query->execute();
        $Attempts = $Query->fetchAll(PDO::FETCH_COLUMN);

        // Check if all attempts in the result set are failures
        return count($Attempts) === $maxAttempts && !in_array(1, $Attempts);
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        return false;
    }
}

// Function to log a login attempt in the database
function LogLoginAttempt(int $UserId, string $Ip, bool $WasSuccessful): void
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query      = $Connection->prepare(
            "INSERT INTO pps_logs_login (lol_user, lol_ip, lol_was_correct_login, lol_datetime) 
            VALUES (:user_id, :ip, :was_successful, NOW())"
        );
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->bindParam(':ip', $Ip, PDO::PARAM_STR);
        $Query->bindParam(':was_successful', $WasSuccessful, PDO::PARAM_BOOL);
        $Query->execute();
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
    }
}

// Function to verify a user's credentials
function VerifyUser(string $Email, string $Password, int &$UserId = 0, string &$msg = ""): bool
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT usu_password, usu_id FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        if (!$Result)
        {
            // Usuario no encontrado
            $msg = "Credenciales incorrectas.";
            return false;
        }
        
        $UserId = (int)$Result['usu_id'];
        if (!password_verify($Password, $Result['usu_password']))
        {
            // Contraseña incorrecta
            $msg = "Credenciales incorrectas.";
            return false;
        }
        
        LogLoginAttempt($UserId, $_SERVER['REMOTE_ADDR'], true);
        return true;
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        $msg = "Error del servidor. Inténtalo de nuevo más tarde.";
        return false;
    }
}

// Function to check if a user has 2FA enabled
function Has2FA(int $UserId): bool
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query      = $Connection->prepare("SELECT usu_2fa FROM pps_users WHERE usu_id = :user_id");
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return !empty($Result['usu_2fa']);
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        return false;
    }
}

// Function to update a user's 2FA secret in the database
function UpdateUser2FASecret(int $UserId, string $Secret): bool
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query      = $Connection->prepare("UPDATE pps_users SET usu_2fa = :secret WHERE usu_id = :user_id");
        $Query->bindParam(':secret', $Secret, PDO::PARAM_STR);
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->execute();
        return $Query->rowCount() > 0;
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        return false;
    }
}

// Function to get user data by ID from the database
function GetUserById(int $UserId): array|bool
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT * FROM pps_users WHERE usu_id = :id");
        $Query->bindParam(':id', $UserId, PDO::PARAM_INT);
        $Query->execute();
        return $Query->fetch(PDO::FETCH_ASSOC) ?: false;
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        return false;
    }
}

// Function to redirect unauthorized users to the login page
function RedirectUnauthorizedUser(): void
{
    header('Location: login.php');
    exit;
}

// Function to get the user ID by email from the database
function GetUserIdByEmail(string $Email): int
{
    try
    {
        $Connection = GetDatabaseConnection();
        $Query      = $Connection->prepare("SELECT usu_id FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return $Result ? (int)$Result['usu_id'] : 0;
    }
    catch (PDOException $e)
    {
        error_log($e->getMessage());
        return 0;
    }
}


