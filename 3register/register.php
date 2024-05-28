<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

session_start();

require('functions.php');

$Conn = database::LoadDatabase();
$Errors = array();

$UserType = isset($_POST['UserType']) ? $_POST['UserType'] : '';

// Verificar si el formulario ha sido enviado
if (isset($_POST['register']))
{
	$Prefix           = GetPrefix(htmlspecialchars($_POST['Prefix']));				// Limpieza de formato
	$PhoneNumber      = GetPhoneNumber(htmlspecialchars($_POST['PhoneNumber']));	// Limpieza de formato
	$Email            = htmlspecialchars($_POST['Email']);
	$ConfirmEmail     = htmlspecialchars($_POST['ConfirmEmail']);
	$Password         = htmlspecialchars($_POST['Password']);
	$ConfirmPassword  = htmlspecialchars($_POST['ConfirmPassword']);
	$VerificationCode = GetVerificationCode();

	if ($UserType == 'cus')
	{
		$CustomerName     = htmlspecialchars($_POST['CustomerName']);
		$CustomerSurNames = htmlspecialchars($_POST['CustomerSurNames']);
	}

	if ($UserType == 'com')
	{
		$CompanyName      = htmlspecialchars($_POST['CompanyName']);
		$Cif              = htmlspecialchars($_POST['Cif']);
		$CompanyWeb       = htmlspecialchars($_POST['CompanyWeb']);
		$CompanyDocuments = GetCompanyDocuments($_FILES['CompanyDocuments'], $Cif); // functions.php

		// Validación del CIF
		// Un CIF válido contiene 8 caracteres,
		// 7 números y 1 letra en mayuscula
		if (!CifValidation($Cif)){ // functions.php
			$Errors +=["ErrorCif" => "Validación del Cif incorrecta. La letra debe ser correcta y como máximo 7 números y 1 letra."];
		}
	}

	// Validación del prefijo del número de teléfono
	if (!is_numeric($Prefix) OR strlen($Prefix) > 5)
	{
		$Errors +=["ErrorPrefix" => "Prefijo inválido."];
	}
	
	// Validación del teléfono
	if (!is_numeric($PhoneNumber) OR strlen($PhoneNumber) > 11)
	{
		$Errors +=["ErrorPhoneNumber" => "El número de teléfono es inválido."];
	}

	// Validación del correo electrónico
	// Validar que los correos electrónicos coincidan
	if ($Email !== $ConfirmEmail)
	{
		$Errors +=["ErrorConfirmEmail" => "Los correos electrónicos no coinciden."];
	}

	// Validar formato de correo electrónico
	if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
	{
		$Errors +=["ErrorEmail" => 'El correo electrónico no es válido.'];
	}

	// Validación de las contraseñas
	// Validar que las contraseñas coincidan
	if ($Password !== $ConfirmPassword)
	{
		$Errors +=["ErrorConfirmPassword" => "Las contraseñas no coinciden."];
	}

	// Validar que las contraseñas cumplen los requisitos mínimos
	// Al menos 8 caracteres
	// Al menos 1 caracter minuscula
	// Al menos 1 caracter mayuscula
	// Al menos un número
	$PatternPassword = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
	if (!preg_match($PatternPassword, $Password))
	{
		$Errors +=["ErrorPassword" => "La contraseña no cumple los requisitos mínimos."];
	}

	// Hash de la contraseña
	$Password = password_hash($Password, PASSWORD_DEFAULT);

	// Generar código de verificación enviado al correo
	$VerificationCode = GetVerificationCode();

	// Variable con fecha y hora
	$DateTime = date('Y-m-d H:i:s');

	if (!empty($Errors)) {
	// Guardar los errores en la sesión
		$_SESSION['Errors'] = $Errors;
		// Redirigir a la página de registro
		header('Location: register.form.php');
		exit;
	}


	// Insertar en base de datos al usuario/cliente
	if ($UserType == 'cus')
	{
		// Comprobar si el usuario ya existe en la DB
		$Query       = ("SELECT usu_email FROM pps_users WHERE usu_type = 'U' AND usu_email = '$Email';"); // 'U' Cliente
		$ResultQuery = $Conn->query($Query);
		if ($ResultQuery->rowCount() > 0)
		{
			$Errors +=["ErrorUserExist" => "El usuario con email: '" . $Email . ",' ya existe."];
		}

		// Preparación de datos a la Base de datos
		//
		$Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_name, usu_surnames, usu_prefix, usu_phone, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents, usu_2fa ) VALUES ( 'V', 'V', 'N', '$VerificationCode', '$DateTime','$CustomerName', '$CustomerSurNames', '$Prefix', '$PhoneNumber', '$Email', '$Password', '', '', '', '', '' )");
	}

	// Insertar en base de datos al usuario/empresa
	elseif ($UserType == 'com')
	{
		// Comprobar si el usuario ya existe en la DB
		$Query       = ("SELECT usu_email FROM pps_users WHERE usu_type = 'V' AND usu_email = '$Email';"); // 'V' Empresa
		$ResultQuery = $Conn->query($Query);
		if ($ResultQuery->rowCount() > 0)
		{
			$Errors +=["ErrorUserExist" => "La empresa con email: '" . $Email . ",' ya existe."];
		}

		// Preparación de datos a la Base de datos
		//
		$Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_name, usu_surnames, usu_prefix, usu_phone, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents, usu_2fa ) VALUES ( 'V', 'V', 'N', '$VerificationCode', '$DateTime','', '', '$Prefix', '$PhoneNumber', '$Email', '$Password', '$CompanyName', '$Cif', '$CompanyWeb', '$CompanyDocuments', '' )");

	}

	try
	{
		// Query a la base de datos
		$stmt = $Conn->prepare($Query);
		if ($stmt->execute())
		{
			echo _("Te has registrado correctamente");
			header('Refresh: 1; URL=/1login/login.php');
		}
		else
		{
			// Si no hay filas afectadas, asumimos un error
			throw new Exception("Error en la ejecución de la consulta: " . $Query);
		}
	}
	catch (Exception $e)
	{
		// Manejo de errores
		echo _("Error: " . $Query . "<br>" . $e->getMessage());
	}
	finally
	{
		// Cierra la conexión
		$Conn = null;
	}
}