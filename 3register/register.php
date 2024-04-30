<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 * 
 */

if(!defined('SI_NO_EXISTE_PETA')){ die('No me seas cabrón y sal de aquí'); }

include('database.php');

$UserType = isset($_POST['UserType']) ? $_POST['UserType'] : '';

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $Prefix = $_POST['Prefix'];
    $PhoneNumber = $_POST['PhoneNumber'];
    $Address = $_POST['Address'];
    $Email = $_POST['Email'];
    $ConfirmEmail = $_POST['ConfirmEmail'];
    $Password = $_POST['Password'];
    $ConfirmPassword = $_POST['ConfirmPassword'];
    $VerificationCode ='';

    if ($UserType == 'cus') {
        $CustomerName = $_POST['CustomerName'];
        $CustomerSurNames = $_POST['CustomerSurNames'];
    }

    
    if ($UserType == 'com') {
        $CompanyName = $_POST['CompanyName'];
        $Cif = $_POST['Cif'];
        $CompanyWeb = $_POST['CompanyWeb'];
        $CompanyDocuments = $_POST['CompanyDocuments'];

        // Comprobar CIF
        // Un CIF válido contiene 8 caracteres,
        // 7 números y 1 letra en mayuscula
        $PatternCIF = '/^[a-zA-Z]?\d{11}[a-zA-Z]?$/';

        if (!preg_match($PatternCIF, $Cif)){
            die("CIF inválido!");
        }
        $PatternCIF = Null;
    }

    // Validar que las contraseñas coincidan
    if ($Password !== $ConfirmPassword) {
        die("Las contraseñas no coinciden.");
    }

    // Validar que las contraseñas cumplen los requisitos mínimos
    // Al menos 8 caracteres
    // Al menos 1 caracter minuscula
    // Al menos 1 caracter mayuscula
    // Al menos un número
    $PatternPassword = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
    if (!preg_match($PatternPassword, $Password)) {
        die("Invalid Password.");
    }
    
    // Validar que los correos electrónicos coincidan
    if ($Email !== $ConfirmEmail) {
        die("Los correos electrónicos no coinciden.");
    }

    $PatternPassword = $ConfirmPassword = $ConfirmEmail = Null;

    // Hash de la contraseña
    $Password = password_hash($Password, PASSWORD_DEFAULT);

    // Comprobar si el usuario ya existe en la DB
    //$Sql = $Conn->prepare("SELECT users FROM etc")

    // Generar codigo de verificacion enviado al correo
    $VerificationCode = mt_rand(1000, 9999);

    // Variable con fecha y hora
    $DateTime = date('Y-m-d H:i:s');
    
    //TMP variables por errores con DB
    $Address = 'C/ La amargura';
    $CompanyDocuments='DocumentoFalso123';

    // Insertar en base de datos al usuario/cliente
    if ($UserType == 'cus') {
        // Introducción de datos a la Base de datos
        //
        $Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_prefix, usu_phone, usu_name, usu_surnames, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents ) VALUES ( 'V', 'V', 'N', \"$VerificationCode\", \"$DateTime\", \"$Prefix\", \"$PhoneNumber\", \"$CustomerName\", \"$CustomerSurName\", \"$Email\", \"$Password\", '', '', '', '' )");
    }

    // Insertar en base de datos al usuario/empresa
    elseif ($UserType == 'com') {
        // Introducción de datos a la Base de datos
        //
        $Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_prefix, usu_phone, usu_name, usu_surnames, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents ) VALUES ( 'V', 'V', 'N', \"$VerificationCode\", \"$DateTime\", \"$Prefix\", \"$PhoneNumber\", '', '', \"$Email\", \"$Password\", \"$CompanyName\", \"$Cif\", \"$CompanyWeb\", \"$CompanyDocuments\" )");
    }

    // Query a la base de datos
    if (mysqli_query($Conn, $Query)) {
        echo _("Te has registrado correctamente");
        header('Refresh: 1; URL=login.php');
    } else {
        echo _("Error: " . $Query . "<br>" . mysqli_error($Conn));
    }

    $Conn->close();
}
?>