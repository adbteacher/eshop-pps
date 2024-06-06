<?php
require_once(__DIR__ . "/Database.php");

/**
 * Functions.php - Main utilities
 *
 * @Author Iván
 *
 */
class functions
{
    public static function HasPermissions(string $ProgramName): void
    {
		$Allowed = false;
		$Result = "";

		if (empty($_SESSION['UserRol']))
		{
			$Allowed = false;
		}
		else
		{
			$DataBase = database::LoadDatabase();

			$Query = "SELECT ppr_allowed FROM pps_permission_per_rol WHERE ppr_rol = :UserRol AND ppr_program = :ProgramName";

			// Preparar el statement
			$stmt = $DataBase->prepare($Query);

			// Enlazar los parámetros
			$stmt->bindParam(':UserRol', $_SESSION['UserRol'], PDO::PARAM_STR);
			$stmt->bindParam(':ProgramName', $ProgramName, PDO::PARAM_STR);

			// Ejecutar la consulta
			$stmt->execute();

			// Obtener el resultado
			$Result = $stmt->fetch(PDO::FETCH_ASSOC);
		}

        if (empty($Result) || !$Result || $Result['ppr_allowed'] == "N")
        {
			$Allowed = false;
        }
        else
        {
			$Allowed = true;
		}

		if (!$Allowed)
		{
			echo "No tienes permisos para ver a este programa";
			session_destroy();
			die();
		}
    }

    public static function ActiveSession(): void
    {
        if (!isset($_SESSION["UserID"]))
        {
            header("Location:/1login/login.php");
        }
    }

    public static function checkAdminAccess(): void
    {
        if (!isset($_SESSION['UserRol']))
        {
            echo "<p class='text-danger'>Acceso denegado. No se encontró el rol de usuario en la sesión.</p>";
            exit;
        }

        // Verificar si el usuario es administrador
        if ($_SESSION["UserRol"] !== 'A')
        {
            echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
            exit;
        }
    }

    public static function checkVendorAccess(): void
    {
        if ($_SESSION["UserRol"] !== 'V')
        {
            echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
            exit;
        }
    }

    public static function checkSupportAccess(): void
    {
        if ($_SESSION["UserRol"] !== 'S')
        {
            echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
            exit;
        }
    }

	public static function GetUser(string $ID): array|bool
	{
		try
		{
			$Connection = database::LoadDatabase();
			$Query      = $Connection->prepare("SELECT * FROM pps_users WHERE usu_id = :id");
			$Query->bindParam(':id', $ID, PDO::PARAM_STR);
			$Query->execute();
			return $Query->fetch(PDO::FETCH_ASSOC) ?: false;
		}
		catch (PDOException $e)
		{
			error_log($e->getMessage());
			return false;
		}
	}

}