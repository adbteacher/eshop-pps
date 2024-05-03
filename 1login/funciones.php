<?php
	require_once 'db.php';
	require_once 'vendor/autoload.php';

	use RobThree\Auth\TwoFactorAuth;

	function AddSecurityHeaders(): void
	{
		header('X-Frame-Options: DENY');
		header('X-XSS-Protection: 1; mode=block');
		header("Content-Security-Policy: default-src 'self'; img-src 'self' data:;");
		header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
		header('X-Content-Type-Options: nosniff');
	}

	function SanitizeInput($Input): string
	{
		$Input = trim($Input);
		$Input = stripslashes($Input);
		$Input = htmlspecialchars($Input);
		return $Input;
	}

	function CheckLoginAttempts($Email): void
	{
		$Connection = GetDatabaseConnection();
		$Ip         = $_SERVER['REMOTE_ADDR'];
		$UserId     = GetUserIdByEmail($Email);

		if ($UserId == 0)
		{
			return; // Si no existe el usuario, no se continúa con la verificación de intentos.
		}

		$WaitTime    = 3;  // Tiempo de espera de 3 minutos.
		$MaxAttempts = 5; // Máximo de intentos fallidos permitidos.

		// Prepara la consulta SQL para contar solo los intentos fallidos de inicio de sesión desde la misma IP y para el mismo usuario en los últimos 3 minutos.
		$Query = $Connection->prepare("SELECT COUNT(*) AS attempts FROM pps_logs_login WHERE lol_ip = ? AND lol_user = ? AND lol_was_correct_login = 0 AND lol_datetime > DATE_SUB(NOW(), INTERVAL ? MINUTE)");

		$Query->bindParam(1, $Ip);
		$Query->bindParam(2, $UserId);
		$Query->bindParam(3, $WaitTime);
		$Query->execute();
		$Attempts = $Query->fetchColumn();

		// Si se han registrado 5 o más intentos fallidos, se restringe el acceso.
		if ($Attempts >= $MaxAttempts)
		{
			die("Demasiados intentos de inicio de sesión fallidos. Intente más tarde.");
		}
	}

	function GetUserIdByEmail($Email)
	{
		$Connection = GetDatabaseConnection();
		$Query      = $Connection->prepare("SELECT usu_id FROM pps_users WHERE usu_email = ?");
		$Query->bindParam(1, $Email);
		try
		{
			$Query->execute();
			$Result = $Query->fetch(PDO::FETCH_ASSOC);
			return $Result ? (int)$Result['usu_id'] : 0;
		}
		catch (PDOException $e)
		{
			error_log("Error al obtener el ID del usuario: " . $e->getMessage());
			return 0;
		}
	}

	function UserExistsByEmail($Email)
	{
		$Connection = GetDatabaseConnection();
		$Query      = $Connection->prepare("SELECT COUNT(*) FROM pps_users WHERE usu_email = ?");
		$Query->bindParam(1, $Email);
		try
		{
			$Query->execute();
			return $Query->fetchColumn() > 0;
		}
		catch (PDOException $e)
		{
			error_log("Error al verificar la existencia del usuario: " . $e->getMessage());
			return false;
		}
	}

	function RegisterUser($Email, $Password): bool
	{
		if (UserExistsByEmail($Email))
		{
			echo "Error: El usuario ya existe.";
			return false;
		}

		$Connection     = GetDatabaseConnection();
		$HashedPassword = password_hash($Password, PASSWORD_DEFAULT);
		$Query          = $Connection->prepare("INSERT INTO pps_users (usu_email, usu_password) VALUES (?, ?)");
		$Query->bindParam(1, $Email);
		$Query->bindParam(2, $HashedPassword);
		$Query->execute();

		if ($Query->rowCount() > 0)
		{
			echo "Usuario registrado con éxito.<br>";
			return true;
		}
		else
		{
			echo "Error al registrar el usuario.";
			return false;
		}
	}

	function VerifyUser($Email, $Password): string
	{
		$Connection = GetDatabaseConnection();
		$Query      = $Connection->prepare("SELECT usu_password FROM pps_users WHERE usu_email = ?");
		$Query->bindParam(1, $Email);
		try
		{
			$Query->execute();
			$Result = $Query->fetch(PDO::FETCH_ASSOC);
			if (!$Result)
			{
				return "Usuario no encontrado.";
			}

			if (!password_verify($Password, $Result['usu_password']))
			{
				return "Contraseña incorrecta.";
			}

			return "Inicio de sesión exitoso.";
		}
		catch (PDOException $e)
		{
			error_log("Error al verificar el usuario: " . $e->getMessage());
			return "Error en la base de datos al verificar el usuario.";
		}
	}

	function LogAttempt($Email, $Success): void
	{
		$Connection = GetDatabaseConnection();
		$Ip         = $_SERVER['REMOTE_ADDR'];
		$Status     = $Success ? 1 : 0;

		$UserId = GetUserIdByEmail($Email);
		if ($UserId == 0 && !$Success)
		{
			return;
		}

		// Utiliza NOW() para insertar la fecha y hora actual en formato datetime
		$Query = $Connection->prepare("INSERT INTO pps_logs_login (lol_user, lol_ip, lol_was_correct_login, lol_datetime) VALUES (?, ?, ?, NOW())");
		$Query->bindParam(1, $UserId);
		$Query->bindParam(2, $Ip);
		$Query->bindParam(3, $Status);
		$Query->execute();

		if (!$Success)
		{
			CheckLoginAttempts($Email);
		}
	}


	// Función para verificar si el usuario tiene 2FA activado
	function Has2FA($Username): bool
	{
		$Connection = GetDatabaseConnection();
		$Query      = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_name = ?");
		$Query->bindParam(1, $Username);
		try
		{
			$Query->execute();
			$Result = $Query->fetch(PDO::FETCH_ASSOC);
			return !empty($Result['usu_verification_code']);
		}
		catch (PDOException $e)
		{
			error_log("Error al verificar 2FA: " . $e->getMessage());
			return false;
		}
	}
