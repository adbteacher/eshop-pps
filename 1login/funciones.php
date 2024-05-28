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
function GetDatabaseConnection(): PDO {
    static $Connection;
    if ($Connection === null) {
        $Connection = Database::LoadDatabase();
    }
    return $Connection;
}

// Function to add various security headers to the HTTP response
function AddSecurityHeaders(): void {
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com; img-src 'self' data: https://maps.googleapis.com https://maps.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; frame-src https://www.google.com;");
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
}

// Function to sanitize user input by trimming, stripping slashes, and converting special characters to HTML entities
function SanitizeInput(string $Input): string {
    return htmlspecialchars(stripslashes(trim($Input)), ENT_QUOTES, 'UTF-8');
}

// Function to get user data by email from the database
function GetUserByEmail(string $Email): array|bool {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT * FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        return $Query->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Function to get the user ID by email from the database
function GetUserIdByEmail(string $Email): int {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT usu_id FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return $Result ? (int)$Result['usu_id'] : 0;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return 0;
    }
}

// Function to check the number of login attempts for a user within a specified time frame
function CheckLoginAttempts(string $Email): bool {
    try {
        $Connection = GetDatabaseConnection();
        $Ip = $_SERVER['REMOTE_ADDR'];
        $UserId = GetUserIdByEmail($Email);
        if ($UserId == 0) {
            return false;
        }
        $waitTime = LOGIN_WAIT_TIME;
        $Query = $Connection->prepare(
            "SELECT COUNT(*) AS attempts 
            FROM pps_logs_login 
            WHERE lol_ip = :ip AND lol_user = :user_id AND lol_was_correct_login = 0 AND lol_datetime > DATE_SUB(NOW(), INTERVAL :wait_time MINUTE)"
        );
        $Query->bindParam(':ip', $Ip, PDO::PARAM_STR);
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->bindParam(':wait_time', $waitTime, PDO::PARAM_INT);
        $Query->execute();
        $Attempts = $Query->fetchColumn();
        return $Attempts >= MAX_LOGIN_ATTEMPTS;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Function to log a login attempt in the database
function LogLoginAttempt(int $UserId, string $Ip, bool $WasSuccessful): void {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare(
            "INSERT INTO pps_logs_login (lol_user, lol_ip, lol_was_correct_login, lol_datetime) 
            VALUES (:user_id, :ip, :was_successful, NOW())"
        );
        $Query->bindParam(':user_id', $UserId, PDO::PARAM_INT);
        $Query->bindParam(':ip', $Ip, PDO::PARAM_STR);
        $Query->bindParam(':was_successful', $WasSuccessful, PDO::PARAM_BOOL);
        $Query->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

// Function to check if a user exists by email in the database
function UserExistsByEmail(string $Email): bool {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT COUNT(*) FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        return $Query->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Function to register a new user in the database
function RegisterUser(string $Email, string $Password): bool {
    try {
        if (UserExistsByEmail($Email)) {
            echo "Error: El usuario ya existe.";
            return false;
        }
        $Connection = GetDatabaseConnection();
        $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);
        $Query = $Connection->prepare("INSERT INTO pps_users (usu_email, usu_password, usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_name, usu_surnames, usu_prefix, usu_phone, usu_company, usu_cif, usu_web, usu_documents, usu_2fa) VALUES (:email, :password, 'U', 'U', 'N', '', NOW(), '', '', '', 0, '', '', '', '', '')");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->bindParam(':password', $HashedPassword, PDO::PARAM_STR);
        $Query->execute();
        if ($Query->rowCount() > 0) {
            echo "Usuario registrado con éxito.<br>";
            return true;
        } else {
            echo "Error al registrar el usuario.";
            return false;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Function to verify a user's credentials
function VerifyUser(string $Email, string $Password, string &$msg = ""): bool {
    try {
        if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Formato de correo electrónico inválido.";
            return false;
        }
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT usu_password, usu_id FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        if (!$Result) {
            $msg = "Usuario no encontrado.";
            return false;
        }
        if (!password_verify($Password, $Result['usu_password'])) {
            $msg = "Contraseña incorrecta.";
            return false;
        }
        LogLoginAttempt((int)$Result['usu_id'], $_SERVER['REMOTE_ADDR'], true);
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $msg = "Error del servidor. Inténtalo de nuevo más tarde.";
        return false;
    }
}

// Function to check if a user has 2FA enabled
function Has2FA(string $Email): bool {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("SELECT usu_2fa FROM pps_users WHERE usu_email = :email");
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return !empty($Result['usu_2fa']);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Function to update a user's 2FA secret in the database
function UpdateUser2FASecret(string $Email, string $Secret): bool {
    try {
        $Connection = GetDatabaseConnection();
        $Query = $Connection->prepare("UPDATE pps_users SET usu_2fa = :secret WHERE usu_email = :email");
        $Query->bindParam(':secret', $Secret, PDO::PARAM_STR);
        $Query->bindParam(':email', $Email, PDO::PARAM_STR);
        $Query->execute();
        return $Query->rowCount() > 0;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/* 
// Function to start a secure session (commented out)
function StartSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $secure = true;
        $httponly = true;

        // Set various session settings
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);

        $lifetime = SESSION_LIFETIME;
        $path = '/';
        $domain = $_SERVER['HTTP_HOST'];

        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        session_start();
        session_regenerate_id(true);
    }
}
*/

// Function to redirect unauthorized users to the login page
function RedirectUnauthorizedUser(): void {
    header('Location: login.php');
    exit;
}


// No cerrar etiqueta php para que Iván duerma tranquilo y para que la bestia no se vuelva a despertar
