<?php
	/**
	 * Josevi
	 * CETI
	 * PPS - Puesta en Producción Segura
	 *
	 */

	//if (!defined('SI_NO_EXISTE_PETA'))
	//{
	//	die('No me seas cabrón y sal de aquí');
	//}
	require_once("../vendor/autoload.php");
	require_once("../autoload.php");

	$Conn = database::LoadDatabase();

	$UserType = isset($_POST['UserType']) ? $_POST['UserType'] : '';

	// Declarar variables
	$PhoneNumber = $Address = $Email = $ConfirmEmail = $Password = $ConfirmPassword = $CustomerName = $CustomerSurNames = $CompanyName = $Cif = $CompanyWeb = $CompanyDocuments = '';

	// Verificar si el formulario ha sido enviado
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"]))
	{
		$Prefix           = htmlspecialchars($_POST['Prefix']);
		$PhoneNumber      = htmlspecialchars($_POST['PhoneNumber']);
		$Address          = htmlspecialchars($_POST['Address']);
		$Email            = htmlspecialchars($_POST['Email']);
		$ConfirmEmail     = htmlspecialchars($_POST['ConfirmEmail']);
		$Password         = htmlspecialchars($_POST['Password']);
		$ConfirmPassword  = htmlspecialchars($_POST['ConfirmPassword']);
		$VerificationCode = '';

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
			$CompanyDocuments = htmlspecialchars($_POST['CompanyDocuments']);

			// Comprobar CIF
			// Un CIF válido contiene 8 caracteres,
			// 7 números y 1 letra en mayuscula
			$PatternCIF = '/^[a-zA-Z]?\d{11}[a-zA-Z]?$/';

			if (!preg_match($PatternCIF, $Cif))
			{
				die('CIF inválido!');
			}
			$PatternCIF = null;

		}

		// El prefijo debe ser un INT para la base de datos
		if (!is_numeric($Prefix))
		{
			die('El prefijo debe ser un número');
		}

		// El número de telefono puede ser un número con o sin espacios
		//$PatternPhoneNumber = '/^\s*\d\s*(?:\d\s*){8}$/'; // la base de datos espera un INT
		//if (!preg_match($PatternPhoneNumber, $PhoneNumber)) {
		//    die('El número de teléfono es inválido.');
		//}
		// El número de teléfono debe ser un número entero sin espacios
		$PhoneNumber = str_replace(' ', '', strval($PhoneNumber));
		if (!is_numeric($PhoneNumber) && strlen($PhoneNumber) <= 11)
		{
			die('El número de teléfono es inválido.');
		}

		// Validar que los correos electrónicos coincidan
		if ($Email !== $ConfirmEmail)
		{
			die("Los correos electrónicos no coinciden.");
		}

		// Validar formato de correo electrónico
		if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
		{
			die('El correo electrónico no es válido.');
		}

		$ConfirmEmail = null;

		// Validar que las contraseñas coincidan
		if ($Password !== $ConfirmPassword)
		{
			die("Las contraseñas no coinciden.");
		}
		$ConfirmPassword = null;

		// Validar que las contraseñas cumplen los requisitos mínimos
		// Al menos 8 caracteres
		// Al menos 1 caracter minuscula
		// Al menos 1 caracter mayuscula
		// Al menos un número
		$PatternPassword = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
		if (!preg_match($PatternPassword, $Password))
		{
			die("La contraseña no cumple los requisitos mínimos.");
		}
		$PatternPassword = null;

		// Hash de la contraseña
		$Password = password_hash($Password, PASSWORD_DEFAULT);

		// Generar codigo de verificacion enviado al correo
		$VerificationCode = '1234';

		// Variable con fecha y hora
		$DateTime = date('Y-m-d H:i:s');

		//Variables temporales
		$Address          = 'C/ La amargura';
		$CompanyDocuments = 'DocumentoFalso123';

		// Insertar en base de datos al usuario/cliente
		if ($UserType == 'cus')
		{
			// Comprobar si el usuario ya existe en la DB
			$Query       = ("SELECT usu_email FROM pps_users WHERE usu_type = 'U' AND usu_email = \"$Email\";"); // 'U' Cliente
			$ResultQuery = $Conn->query($Query);
			if ($ResultQuery->rowCount() > 0)
			{
				die("El usuario con email: '" . $Email . ",' ya existe.");
			}
			$ResultQuery = null;

			// Preparación de datos a la Base de datos
			//
			// v1
			$Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_prefix, usu_phone, usu_name, usu_surnames, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents ) 
					VALUES ( 'U', 'U', 'N', \"$VerificationCode\", \"$DateTime\", \"$Prefix\", \"$PhoneNumber\", \"$CustomerName\", \"$CustomerSurNames\", \"$Email\", \"$Password\", '', '', '', '' )");
		}
		// Insertar en base de datos al usuario/empresa
		elseif ($UserType == 'com')
		{
			// Comprobar si el usuario ya existe en la DB
			$Query       = ("SELECT usu_email FROM pps_users WHERE usu_type = 'V' AND usu_email = \"$Email\";"); // 'V' Empresa
			$ResultQuery = $Conn->query($Query);
			if ($ResultQuery->rowCount() > 0)
			{
				die("La empresa con email: '" . $Email . ",' ya existe.");
			}
			$ResultQuery = null;

			// Preparación de datos a la Base de datos
			//
			$Query = ("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_prefix, usu_phone, usu_name, usu_surnames, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents ) VALUES ( 'V', 'V', 'N', \"$VerificationCode\", \"$DateTime\", \"$Prefix\", \"$PhoneNumber\", '', '', \"$Email\", \"$Password\", \"$CompanyName\", \"$Cif\", \"$CompanyWeb\", \"$CompanyDocuments\" )");
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
