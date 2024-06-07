<?php

require('../Database.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function CifValidation($Cif): bool
{
	// Convertimos el CIF a mayúsculas para asegurar consistencia
	$Cif = strtoupper($Cif);

	if (preg_match('~(^[XYZ\d]\d{7})([TRWAGMYFPDXBNJZSQVHLCKE]$)~', $Cif, $parts))
	{
		$control = 'TRWAGMYFPDXBNJZSQVHLCKE';
		$Nie     = array('X', 'Y', 'Z');
		// Convertimos el NIE (Número de Identificación de Extranjero) a un formato estándar
		// reemplazando ciertas letras iniciales con sus valores correspondientes ('X', 'Y', 'Z' con '0', '1', '2')
		$parts[1] = str_replace(array_values($Nie), array_keys($Nie), $parts[1]);
		// Calculamos el dígito de control y lo comparamos con el obtenido
		$cheksum = substr($control, $parts[1] % 23, 1);
		return ($parts[2] == $cheksum);

	}
	elseif (preg_match('~(^[ABCDEFGHIJKLMUV])(\d{7})(\d$)~', $Cif, $parts))
	{
		$checksum = 0;
		// Iteramos sobre cada dígito del CIF para calcular el dígito de control
		// asignando la posición de cada dígito a $pos y su valor a $val
		foreach (str_split($parts[2]) as $pos => $val)
		{
			$checksum += array_sum(str_split($val * (2 - ($pos % 2))));
		}
		$checksum = ((10 - ($checksum % 10)) % 10);
		return ($parts[3] == $checksum);

	}
	elseif (preg_match('~(^[KLMNPQRSW])(\d{7})([JABCDEFGHI]$)~', $Cif, $parts))
	{
		$control  = 'JABCDEFGHI';
		$checksum = 0;
		// Iteramos sobre cada dígito del CIF para calcular el dígito de control
		// asignando la posición de cada dígito a $pos y su valor a $val
		foreach (str_split($parts[2]) as $pos => $val)
		{
			$checksum += array_sum(str_split($val * (2 - ($pos % 2))));
		}
		$checksum = substr($control, ((10 - ($checksum % 10)) % 10), 1);
		return ($parts[3] == $checksum);
	}

	// Si no se cumplen ninguna de las condiciones anteriores, retornamos falso
	return false;
}

function GetPhoneNumber($PhoneNumber): string
{
	$PhoneNumber = str_replace(' ', '', strval($PhoneNumber));
	$PhoneNumber = str_replace('-', '', strval($PhoneNumber));

	return $PhoneNumber;
}

function GetPrefix($Prefix): string
{
	$Prefix = str_replace(' ', '', strval($Prefix));
	$Prefix = str_replace('+', '', strval($Prefix));

	return $Prefix;
}

// Devuelve la carpeta raíz para subida de ficheros
function GetRootUploadDir(): string
{
	$RootUploadDir = 'C:\\uploads-eshop\\';

	return $RootUploadDir;
}

// Creación de estructura de carpeta raíz
function SetRootUploadDir(): bool
{
	$RootUploadDir = GetRootUploadDir();

	// Si no existe la carpeta raíz de archivos de subida, se crea
	if (!is_dir($RootUploadDir))
	{
		mkdir(
			$RootUploadDir,
			0770,
			false
		);
	}

	return true;
}

// Creación de estructura de carpeta del usuario
function GetCompanyUserDir($Cif): string
{
	$RootUploadDir  = GetRootUploadDir();
	$CompanyUserDir = $RootUploadDir . $Cif . '\\';

	return $CompanyUserDir;
}

function SetCompanyUserDir($Cif): bool
{
	$RootUploadDir  = GetRootUploadDir();
	$CompanyUserDir = GetCompanyUserDir($Cif);

	// Si no existe la carpeta del usuario, se crea
	if (!is_dir($CompanyUserDir))
	{
		mkdir(
			$CompanyUserDir,
			0770,
			false
		);
	}

	return true;
}

// Subida de los documentos de la empresa
function UploadCompanyDocuments($CompanyDocuments, $Cif): bool
{
	SetRootUploadDir();
	SetCompanyUserDir($Cif);

	$MaxDocumentsToUpload    = 2;                       // Se suben como maximo 2 archivos
	$CompanyUserDir          = GetCompanyUserDir($Cif); // Ruta de los archivos del usuario específico
	$AllowedCompanyDocuments = 'application/pdf';       // Documentos válidos: PDF
	$UnitSeparator           = '^_';                    // Separador para la inserción en la DB.

	for ($i = 0; $i < $MaxDocumentsToUpload; $i++)
	{
		$CompanyDocumentsTmp  = $CompanyDocuments['tmp_name'][$i];
		$CompanyDocumentsType = mime_content_type($CompanyDocumentsTmp);

		if ($CompanyDocumentsType != $AllowedCompanyDocuments)
		{
			return false;
		}
	}

	if (count($CompanyDocuments["tmp_name"]) > $MaxDocumentsToUpload)
	{
		return false;
	}

	for ($i = 0; $i < 2; $i++)
	{
		$CompanyDocumentsToUpload = $CompanyDocuments['tmp_name'][$i];
		$CompanyDocumentsPathDir  = $CompanyUserDir . $i+1 . '.pdf';

		if (!move_uploaded_file($CompanyDocumentsToUpload, $CompanyDocumentsPathDir))
		{
			return false;
		}
	}

	return true;
}

// Subida de los documentos de la empresa
function GetCompanyDocuments($CompanyDocuments, $Cif): string
{
	$MaxDocumentsToUpload    = 2;                       // Se suben como maximo 2 archivos
	$CompanyUserDir          = GetCompanyUserDir($Cif); // Ruta de los archivos del usuario específico
	$UnitSeparator           = '^_';                    // Separador para la inserción en la DB.
	$CompanyDocumentsPathDB  = '';                      // Campo para la inserción en la DB.

	// Path para la base de datos
	for ($i = 0; $i < $MaxDocumentsToUpload; $i++)
	{
		$CompanyDocumentsName = $i+1 . '.pdf';
		$CompanyDocumentsPath = $CompanyUserDir . $CompanyDocumentsName;

		if ($i == ($MaxDocumentsToUpload - 1))
		{
			$CompanyDocumentsPathDB .= $CompanyDocumentsPath;
		}
		else
		{
			$CompanyDocumentsPathDB .= $CompanyDocumentsPath . $UnitSeparator;
		}
	}

	return $CompanyDocumentsPathDB;
}

function GetVerificationCode(): string
{
	// Definir los caracteres permitidos
	$Char             = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$Char             = str_shuffle($Char);
	$LongChar         = 16;
	$VerificationCode = '';

	// Generar el código aleatorio
	for ($i = 0; $i < $LongChar; $i++)
	{
		$VerificationCode .= $Char[rand(0, $LongChar - 1)];
	}

	return $VerificationCode;
}

function VerificationCodeValidation($Validating)
{
	$Char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	// Si la variable solo contiene caracteres permitidos, se devuelve verdadero
	if (strspn($Validating, $Char) == strlen($Validating))
	{
	return true;
	}

	return false;
}

function NameValidation($Validating): bool
{
	$Char = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZáéíóúÁÉÍÓÚàèìòùÀÈÌÒÙ .-,'`";

	// Si la variable solo contiene caracteres permitidos, se devuelve verdadero
	if (strspn($Validating, $Char) == strlen($Validating))
	{
		return true;
	}

	return false;
}

function SendMail($Email, $VerificationCode): bool
{
	require '../mail_config.php';
	require '../vendor/phpmailer/phpmailer/src/Exception.php';
	require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
	require '../vendor/phpmailer/phpmailer/src/SMTP.php';

	$Mail = getMailer();

	try
	{
		$Mail->addAddress($Email);		// Add a recipient

		// Content
		$Mail->isHTML(true);			// Set email format to HTML
		$Mail->Subject = 'Verification Code';
		$Mail->Body    = _("Hola,<br><br>su clave para su cuenta es: ") . $VerificationCode . ".<br><br>Fruteria del barrio.";

		if ($Mail->send())
		{
			return true;
		}
	}
	catch (Exception $e)
	{
		return false;
	}
	return true;
}