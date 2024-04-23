<?php
session_start();
require_once 'funciones.php';
require_once 'vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;

AddSecurityHeaders();

if (!isset($_SESSION['username'])) {
    echo "No está autorizado para ver esta página.";
    exit;
}

$Username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Code2FA = SanitizeInput($_POST['code']);
    $Tfa = new TwoFactorAuth();
    $Connection = GetDatabaseConnection();
    $Query = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_name = ?");
    $Query->bindParam(1, $Username);
    $Query->execute();
    $Result = $Query->fetch(PDO::FETCH_ASSOC);
    $Secret = $Result['usu_verification_code'];

    if ($Tfa->verifyCode($Secret, $Code2FA)) {
        header('Location: activate_2fa.php');
        exit;
    } else {
        echo "Código 2FA incorrecto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body>
    <div class="form-box">
        <h1>Verificar 2FA</h1>
        <form method="post">
            Código 2FA: <input type="text" name="code" required><br>
            <input type="submit" value="Verificar">
        </form>
    </div>
</body>
</html>
