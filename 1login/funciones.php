<?php
require_once 'db.php';  // Incluye el script de conexión a la base de datos
require_once '../vendor/autoload.php';  // Autocargador para dependencias de Composer
use RobThree\Auth\TwoFactorAuth;  // Uso de la biblioteca de autenticación de dos factores

/**
 * Añade cabeceras de seguridad a las respuestas HTTP para mejorar la seguridad del cliente.
 */
function AddSecurityHeaders(): void
{
    header('X-Frame-Options: DENY');  // Previene el clickjacking
    header('X-XSS-Protection: 1; mode=block');  // Activar la protección XSS en navegadores compatibles
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:;");  // CSP para restringir recursos a los mismos origen
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');  // Activar HSTS para HTTPS estricto
    header('X-Content-Type-Options: nosniff');  // Evitar que el navegador mime-type sniffing
}

/**
 * Limpia una cadena de entrada para evitar inyecciones y otros ataques.
 *
 * @param string $Input Cadena de entrada a sanear.
 * @return string Cadena saneada.
 */
function SanitizeInput($Input): string
{
    $Input = trim($Input);  // Elimina espacios en blanco al inicio y al final
    $Input = stripslashes($Input);  // Elimina las barras invertidas
    $Input = htmlspecialchars($Input);  // Convierte caracteres especiales en entidades HTML
    return $Input;
}

/**
 * Obtiene el ID de usuario a partir de su email.
 *
 * @param string $Email Email del usuario a buscar.
 * @return int ID del usuario o 0 si no se encuentra.
 */
function GetUserIdByEmail($Email) {
    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Query = $Connection->prepare("SELECT usu_id FROM pps_users WHERE usu_email = ?");  // Prepara la consulta SQL
    $Query->bindParam(1, $Email);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return $Result ? (int) $Result['usu_id'] : 0;  // Devuelve el ID del usuario o 0 si no existe
    } catch (PDOException $e) {
        error_log("Error al obtener el ID del usuario: " . $e->getMessage());
        return 0;
    }
}

/**
 * Verifica los intentos de inicio de sesión fallidos para un email y bloquea si excede los límites.
 *
 * @param string $Email Email del usuario a verificar.
 */
// Esta función hay que cambiarla porque patatas
function CheckLoginAttempts($Email): void
{
    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Ip = $_SERVER['REMOTE_ADDR'];  // Dirección IP del cliente
    $UserId = GetUserIdByEmail($Email);  // Obtiene el ID del usuario por su email

    if ($UserId == 0) {
        return; // Si no existe el usuario, no se continúa con la verificación de intentos.
    }

    $WaitTime = 3;  // Tiempo de espera de 3 minutos.
    $MaxAttempts = 5; // Máximo de intentos fallidos permitidos.

    // Prepara la consulta SQL para contar solo los intentos fallidos de inicio de sesión desde la misma IP y para el mismo usuario en los últimos 3 minutos.
    $Query = $Connection->prepare("SELECT COUNT(*) AS attempts FROM pps_logs_login WHERE lol_ip = ? AND lol_user = ? AND lol_was_correct_login = 0 AND lol_datetime > DATE_SUB(NOW(), INTERVAL ? MINUTE)");

    $Query->bindParam(1, $Ip);
    $Query->bindParam(2, $UserId);
    $Query->bindParam(3, $WaitTime);
    $Query->execute();
    $Attempts = $Query->fetchColumn();

    // Si se han registrado 5 o más intentos fallidos, se restringe el acceso.
    if ($Attempts >= $MaxAttempts) {
        die("Demasiados intentos de inicio de sesión fallidos. Intente más tarde.");
    }
}

/**
 * Verifica si existe un usuario por su email.
 *
 * @param string $Email Email del usuario a verificar.
 * @return bool Verdadero si el usuario existe, falso si no.
 */
function UserExistsByEmail($Email): bool
{
    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Query = $Connection->prepare("SELECT COUNT(*) FROM pps_users WHERE usu_email = ?");  // Prepara la consulta SQL
    $Query->bindParam(1, $Email);
    try {
        $Query->execute();
        return $Query->fetchColumn() > 0;  // Devuelve verdadero si encuentra al menos un registro
    } catch (PDOException $e) {
        error_log("Error al verificar la existencia del usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Registra un nuevo usuario con su email y contraseña.
 *
 * @param string $Email Email del nuevo usuario.
 * @param string $Password Contraseña del nuevo usuario.
 * @return bool Verdadero si el registro es exitoso, falso si falla.
 */
// Funciones de mi registro que habrá que eliminar cuando esté implementado el registro de JV
function RegisterUser($Email, $Password): bool
{
    if (UserExistsByEmail($Email)) {
        echo "Error: El usuario ya existe.";
        return false;
    }

    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $HashedPassword = password_hash($Password, PASSWORD_DEFAULT);  // Genera un hash de la contraseña
    $Query = $Connection->prepare("INSERT INTO pps_users (usu_email, usu_password) VALUES (?, ?)");  // Prepara la consulta SQL
    $Query->bindParam(1, $Email);
    $Query->bindParam(2, $HashedPassword);
    $Query->execute();

    if ($Query->rowCount() > 0) {
        echo "Usuario registrado con éxito.<br>";
        return true;
    } else {
        echo "Error al registrar el usuario.";
        return false;
    }
}

/**
 * Verifica las credenciales de un usuario.
 *
 * @param string $Email Email del usuario a verificar.
 * @param string $Password Contraseña proporcionada por el usuario.
 * @return string Mensaje con el resultado de la verificación.
 */
function VerifyUser($Email, $Password): string
{
    // Validar el formato del correo electrónico
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        return "Formato de email inválido.";
    }

    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Query = $Connection->prepare("SELECT usu_password FROM pps_users WHERE usu_email = ?");  // Prepara la consulta SQL
    $Query->bindParam(1, $Email);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);

        // Verificar si el resultado de la consulta es nulo o si la contraseña no coincide
        if (!$Result || !password_verify($Password, $Result['usu_password'])) {
            return "Usuario o contraseña incorrecta.";
        }

        return "Inicio de sesión exitoso.";
    } catch (PDOException $e) {
        error_log("Error al verificar el usuario: " . $e->getMessage());
        return "Error en la base de datos al verificar el usuario.";
    }
}


/**
 * Registra un intento de inicio de sesión en la base de datos.
 *
 * @param string $Email Email del usuario que intenta iniciar sesión.
 * @param bool $Success Indica si el intento de inicio de sesión fue exitoso.
 */
function LogAttempt($Email, $Success): void
{
    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Ip = $_SERVER['REMOTE_ADDR'];  // Dirección IP del cliente
    $Status = $Success ? 1 : 0;  // Estado del intento de inicio de sesión (1 para exitoso, 0 para no exitoso)

    $UserId = GetUserIdByEmail($Email);  // Obtiene el ID del usuario por su email
    if ($UserId == 0 && !$Success) {
        return;  // Si no existe el usuario y el intento fue fallido, no se registra
    }

    // Prepara la consulta SQL para insertar el registro de intento de inicio de sesión
    $Query = $Connection->prepare("INSERT INTO pps_logs_login (lol_user, lol_ip, lol_was_correct_login, lol_datetime) VALUES (?, ?, ?, NOW())");  // Utiliza NOW() para insertar la fecha y hora actual
    $Query->bindParam(1, $UserId);
    $Query->bindParam(2, $Ip);
    $Query->bindParam(3, $Status);
    $Query->execute();

    if (!$Success) {
        CheckLoginAttempts($Email);  // Verifica si se ha excedido el número de intentos fallidos
    }
}

/**
 * Verifica si el usuario tiene activada la autenticación de dos factores (2FA).
 *
 * @param string $Username Nombre de usuario a verificar.
 * @return bool Verdadero si el usuario tiene 2FA activado, falso si no.
 */
function Has2FA($Username): bool
{
    $Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
    $Query = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_name = ?");  // Prepara la consulta SQL
    $Query->bindParam(1, $Username);
    try {
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);
        return !empty($Result['usu_verification_code']);  // Devuelve verdadero si el campo de verificación no está vacío
    } catch (PDOException $e) {
        error_log("Error al verificar 2FA: " . $e->getMessage());
        return false;
    }
}
