<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

if(session_status() != PHP_SESSION_ACTIVE) session_start();

require('functions.php');
require('../2recovery/jwt.php'); // JWT handling library

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$Conn = database::LoadDatabase();
$Errors = array();

$UserType = isset($_POST['UserType']) ? $_POST['UserType'] : '';

// Verificar si el formulario ha sido enviado
if (isset($_POST['register']))
{
	$Prefix           = GetPrefix(htmlspecialchars($_POST['Prefix']));				// functions.php
	$PhoneNumber      = GetPhoneNumber(htmlspecialchars($_POST['PhoneNumber']));	// functions.php
	$Email            = htmlspecialchars($_POST['Email']);
	$ConfirmEmail     = htmlspecialchars($_POST['ConfirmEmail']);
	$Password         = htmlspecialchars($_POST['Password']);
	$ConfirmPassword  = htmlspecialchars($_POST['ConfirmPassword']);
	$VerificationCode = GetVerificationCode();										// functions.php

	// Añadido para mantener los valores del formulario
	$keys = ["UserType", "CustomerName", "CustomerSurNames", "CompanyName", "Cif", "Prefix", "PhoneNumber", "CompanyWeb", "Email", "ConfirmEmail"];
	$formValues = array();

	foreach ($keys as $key)
	{
	    $formValues[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
	}

	$_SESSION['formValues'] = $formValues;

	if ($UserType == 'U')
	{
		$CustomerName     = htmlspecialchars($_POST['CustomerName']);
		$CustomerSurNames = htmlspecialchars($_POST['CustomerSurNames']);
		$CompanyName      = '';
		$Cif              = '';
		$CompanyWeb       = '';
		$CompanyDocuments = '';

		if (!NameValidation($CustomerName) or empty($CustomerName))
		{
			$Errors[] = 'CustomerName';
		}

		if (!NameValidation($CustomerSurNames) or empty($CustomerSurNames))
		{
			$Errors[] = 'CustomerSurNames';
		}
	}

	if ($UserType == 'V')
	{
		$CustomerName     = '';
		$CustomerSurNames = '';
		$CompanyName      = htmlspecialchars($_POST['CompanyName']);
		$Cif              = htmlspecialchars($_POST['Cif']);
		$CompanyWeb       = htmlspecialchars($_POST['CompanyWeb']);
		$CompanyDocuments = $_FILES['CompanyDocuments'];            // functions.php

		if (!NameValidation($CompanyName) or empty($CompanyName))
		{
			$Errors[] = 'CompanyName';
		}

		// Validación del CIF
		// Un CIF válido contiene 8 caracteres,
		// 7 números y 1 letra en mayuscula
		if (!CifValidation($Cif) or empty($Cif))
		{
			$Errors[] = 'Cif';
		}

		if (!NameValidation($CompanyWeb) or empty($CompanyWeb))
		{
			$Errors[] = 'CompanyWeb';
		}
	}


	// Validación del prefijo del número de teléfono
	if (!is_numeric($Prefix) OR strlen($Prefix) > 5 or empty($Prefix))
	{
		$Errors[] = 'Prefix';
	}
	else
	{
		$Prefix = '+' . $Prefix;
	}

	// Validación del teléfono
	if (!is_numeric($PhoneNumber) OR strlen($PhoneNumber) > 11 or empty($PhoneNumber))
	{
		$Errors[] = 'PhoneNumber';
	}

	// Validación del correo electrónico
	// Validar formato de correo electrónico
	if (!filter_var($Email, FILTER_VALIDATE_EMAIL) or empty($Email))
	{
		$Errors[] = 'Email';
	}

    $PatternEmail = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/';

	if (!preg_match($PatternEmail, $Email))
	{
		if (!in_array('Email', $Errors))
		{
			$Errors[] = 'Email';
		}
	}

	// Validar que los correos electrónicos coincidan
	if ($Email !== $ConfirmEmail or empty($ConfirmEmail))
	{
		$Errors[] = 'ConfirmEmail';
	}

	// Validación de las contraseñas
	// Validar que las contraseñas coincidan
	if ($Password !== $ConfirmPassword or empty($ConfirmPassword))
	{
		$Errors[] = 'ConfirmPassword';
	}

	// Validar que las contraseñas cumplen los requisitos mínimos
	// Al menos 8 caracteres
	// Al menos 1 caracter minuscula
	// Al menos 1 caracter mayuscula
	// Al menos un número
	$PatternPassword = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
	if (!preg_match($PatternPassword, $Password) or empty($PatternPassword))
	{
		$Errors[] = 'Password';
	}

	// Hash de la contraseña
	$Password = password_hash($Password, PASSWORD_DEFAULT);

	// Muestra de errores de los formularios
	if (!empty($Errors))
	{
		// Guardar los errores en la sesión
		$_SESSION['Errors'] = $Errors;
		// Redirigir a la página de registro
		header('Location: register.form.php');
		exit;
	}

	// Comprobación de usuario existente
	$Query       = ("SELECT usu_email FROM pps_users WHERE usu_email = '$Email';");
	$ResultQuery = $Conn->query($Query);

	// Muestra de error de que el usuario ya existe
	if ($ResultQuery->rowCount() > 0)
	{
		$Errors[] = 'UserExist';
		// Guardar los errores en la sesión
		$_SESSION['Errors'] = $Errors;
		// Redirigir a la página de registro
		header('Location: register.form.php');
		exit;
	}

	if (!empty($Cif))
	{
		$Query       = ("SELECT usu_cif FROM pps_users WHERE usu_cif = '$Cif';");
		$ResultQuery = $Conn->query($Query);
	}

	if ($ResultQuery->rowCount() > 0)
	{
		$Errors[] = 'UserExist';
		// Guardar los errores en la sesión
		$_SESSION['Errors'] = $Errors;
		// Redirigir a la página de registro
		header('Location: register.form.php');
		exit;
	}

	if (!empty($CompanyDocuments))
	{
		if (!UploadCompanyDocuments($CompanyDocuments, $Cif) or empty($CompanyDocuments))
		{
			$Errors[] = 'CompanyDocuments';
			// Guardar los errores en la sesión
			$_SESSION['Errors'] = $Errors;
			// Redirigir a la página de registro
			header('Location: register.form.php');
			exit;
		}
	}
	$CompanyDocuments = GetCompanyDocuments($CompanyDocuments, $Cif);

	// Variable con fecha y hora
	$DateTime = date('Y-m-d H:i:s');

	// Subida de datos a la base de datos
	//
	try
	{
		// Preparación de datos a la base de datos
		//
		$Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_name, usu_surnames, usu_prefix, usu_phone, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents, usu_2fa ) VALUES ( 'V', '$UserType', 'N', '$VerificationCode', '$DateTime','$CustomerName', '$CustomerSurNames', '$Prefix', '$PhoneNumber', '$Email', '$Password', '$CompanyName', '$Cif', '$CompanyWeb', '$CompanyDocuments', '')");
		$stmt = $Conn->prepare($Query);

		if ($stmt->execute())
		{
			if (SendMail($Email, $VerificationCode))
			{
				echo _("Te has registrado correctamente.<br>");
				echo _("Se ha mandado un correo para la verificación de la cuenta.<br>");
				header("Refresh:3;URL=/1login/login.php");
			}
		}
	}
	catch (Exception $e)
	{
		$Errors[] = 'SendMail';

		$Query = ("DELETE FROM pps_users WHERE usu_email = '$Email'");
		$stmt = $Conn->prepare($Query);
		$stmt->execute();
	}
	finally
	{
		// Cierra la conexión
		$Conn = null;
	}
}
