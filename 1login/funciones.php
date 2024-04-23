<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

function AddSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:;");
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    header('X-Content-Type-Options: nosniff');
}

function SanitizeInput($Input) {
    $Input = trim($Input);
    $Input = stripslashes($Input);
    $Input = htmlspecialchars($Input);
    return $Input;
}

function CheckLoginAttempts() {
    $Connection = GetDatabaseConnection();
    $Ip = $_SERVER['REMOTE_ADDR'];
    $WaitTime = 5;  // Tiempo de espera reducido a 5 minutos.

    // Prepara la consulta SQL para contar solo los intentos fallidos de inicio de sesión desde la misma IP en los últimos 5 minutos.
    $Query = $Connection->prepare("SELECT COUNT(*) as attempts FROM pps_records_login WHERE rlo_ip = ? AND rlo_was_correct_login = 0 AND rlo_datetime > (UNIX_TIMESTAMP() - ? * 60)");
    $Query->bindParam(1, $Ip);
    $Query->bindParam(2, $WaitTime);
    $Query->execute();
    $Attempts = $Query->fetchColumn();

    // Si se han registrado 5 o más intentos fallidos, se restringe el acceso.
    if ($Attempts >= 5) {
        die("Demasiados intentos de inicio de sesión fallidos. Intente más tarde.");
    }
}


function GetUserIdByUsername($Username) {
    $Connection = GetDatabaseConnection();
    $Query = $Connection->prepare("SELECT usu_id FROM pps_users WHERE usu_name = ?");
    $Query->bindParam(1, $Username);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return $Result ? (int) $Result['usu_id'] : 0;  // Devuelve 0 si no se encuentra el usuario
    } catch (PDOException $e) {
        error_log("Error al obtener el ID del usuario: " . $e->getMessage());
        return 0;  // Devuelve 0 en caso de error
    }
}


function UserExists($Username) {
    $Connection = GetDatabaseConnection();
    $Query = $Connection->prepare("SELECT COUNT(*) FROM pps_users WHERE usu_name = ?");
    $Query->bindParam(1, $Username);
    try {
        $Query->execute();
        return $Query->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error al verificar la existencia del usuario: " . $e->getMessage());
        return false;
    }
}

function RegisterUser($Username, $Password) {
    // Verificar si el usuario ya existe en la base de datos.
    if (UserExists($Username)) {
        echo "Error: El usuario ya existe.";
        return false;  // Retorna false para indicar que el registro no fue exitoso.
    }

    // Conexión a la base de datos.
    $Connection = GetDatabaseConnection();
    $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);

    // Preparar la consulta SQL para insertar el nuevo usuario SIN código de 2FA.
    $Query = $Connection->prepare("INSERT INTO pps_users (usu_name, usu_password) VALUES (?, ?)");
    $Query->bindParam(1, $Username);
    $Query->bindParam(2, $HashedPassword);
    $Query->execute();

    // Comprobar si el registro fue exitoso.
    if ($Query->rowCount() > 0) {
        echo "Usuario registrado con éxito.<br>";
        return true;  // Retorna true para indicar que el registro fue exitoso.
    } else {
        echo "Error al registrar el usuario.";
        return false;  // Retorna false si hubo un error durante el registro.
    }
}


function VerifyUser($Username, $Password) {
    $Connection = GetDatabaseConnection();
    $Query = $Connection->prepare("SELECT usu_password FROM pps_users WHERE usu_name = ?");
    $Query->bindParam(1, $Username);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        if (!$Result) {
            return "Usuario no encontrado.";
        }

        if (!password_verify($Password, $Result['usu_password'])) {
            return "Contraseña incorrecta.";
        }

        return "Inicio de sesión exitoso.";
    } catch (PDOException $e) {
        error_log("Error al verificar el usuario: " . $e->getMessage());
        return "Error en la base de datos al verificar el usuario.";
    }
}


function LogAttempt($Username, $Success) {
    $Connection = GetDatabaseConnection();
    $Ip = $_SERVER['REMOTE_ADDR'];
    $Status = $Success ? 1 : 0;

    $UserId = GetUserIdByUsername($Username);
    if ($UserId == 0 && !$Success) {
        // Opción 1: No registrar el intento si el usuario no existe y la operación no fue exitosa.
        return;
        // Opción 2: Registrar con un usuario especial o manejar de alguna manera que no cause un error.
        // $UserId = ID_ESPECIAL;  // Definir ID_ESPECIAL como un valor constante que se ajuste al diseño de la DB.
    }

    $Query = $Connection->prepare("INSERT INTO pps_records_login (rlo_user, rlo_ip, rlo_was_correct_login) VALUES (?, ?, ?)");
    $Query->bindParam(1, $UserId);
    $Query->bindParam(2, $Ip);
    $Query->bindParam(3, $Status);
    $Query->execute();

    if (!$Success) {
        CheckLoginAttempts($Username);
    }
}

// Función para verificar si el usuario tiene 2FA activado
function Has2FA($Username) {
    $Connection = GetDatabaseConnection();
    $Query = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_name = ?");
    $Query->bindParam(1, $Username);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return !empty($Result['usu_verification_code']);
    } catch (PDOException $e) {
        error_log("Error al verificar 2FA: " . $e->getMessage());
        return false;
    }
}

?>
