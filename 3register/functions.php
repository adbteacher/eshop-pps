<?php

//if (!defined('SI_NO_EXISTE_PETA'))
//{
//	die('No me seas cabrón y sal de aquí');
//}

require('../Database.php');


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
	$RootUploadDir = '/pps/uploads-eshop/';

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
			0550,
			false
		);
	}

	return true;
}

// Creación de estructura de carpeta del usuario
function GetCompanyUserDir($Cif): string
{
	$RootUploadDir  = GetRootUploadDir();
	$CompanyUserDir = $RootUploadDir . $Cif . '/';

	return $CompanyUserDir;
}

function SetCompanyUserDir($Cif): int
{
	$RootUploadDir  = GetRootUploadDir();
	$CompanyUserDir = GetCompanyUserDir($Cif);

	// Si no existe la carpeta del usuario, se crea
	if (!is_dir($CompanyUserDir))
	{
		mkdir(
			$CompanyUserDir,
			0550,
			false
		);
	}

	return true;
}

// Recogida y limpieza de los 2 documentos PDF como máximo que se pueden subir al servidor.
function GetCompanyDocuments($CompanyDocuments, $Cif): string
{
	SetRootUploadDir();
	SetCompanyUserDir($Cif);

	$CompanyUserDir          = GetCompanyUserDir($Cif); // Ruta de los archivos del usuario específico
	$MaxDocumentsToUpload    = 2;                       // Se suben como maximo 2 archivos
	$AllowedCompanyDocuments = 'application/pdf';       // Documentos válidos: PDF
	$UnitSeparator           = '^_';                    // Separador para la inserción en la DB.
	$CompanyDocumentsPathDB  = '';                      // Campo para la inserción en la DB.

	if (count($CompanyDocuments["name"]) > $MaxDocumentsToUpload)
	{
		die('Error: hay mas de ' . $MaxDocumentsToUpload . ' archivos.');
	}

	for ($i = 0; $i < $MaxDocumentsToUpload; $i++)
	{
		$CompanyDocumentsName = $i+1 . '.pdf';
		$CompanyDocumentsTmp  = $CompanyDocuments['tmp_name'][$i];
		$CompanyDocumentsType = mime_content_type($CompanyDocumentsTmp);
		$CompanyDocumentsPath = $CompanyUserDir . $CompanyDocumentsName;

		if ($CompanyDocumentsType != $AllowedCompanyDocuments)
		{
			die('El archivo no es un PDF. Su formato no es válido.');
		}

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

// por hacer
function UploadCompanyDocuments($CompanyDocuments,$Cif): bool
{
	SetCompanyUserDir($Cif);

	if (!move_uploaded_file($CompanyDocumentsTmp, $CompanyUserDir))
	{
		echo 'no se ha movido<br>';
		return false;
	}

	return true;
}

function GetVerificationCode(): string
{
	// Definir los caracteres permitidos
	$Char             = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$Char             = str_shuffle($Char);
	$LongChar         = 16;
	$VerificationCode = '';

	// Generar el código aleatorio
	for ($i = 0; $i < $LongChar; $i++) {
		$VerificationCode .= $Char[rand(0, $LongChar - 1)];
	}

	return $VerificationCode;
}

function SendMail()
{

}