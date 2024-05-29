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
    public static function HasPermissions(string $UserRol, string $ProgramName): bool
    {
        //$_SESSION['Rol']

        $DataBase = database::LoadDatabase();

        $Query = "SELECT ppr_allowed FROM pps_permission_per_rol WHERE ppr_rol = '$UserRol' AND ppr_program = '$ProgramName'";

        $Result = $DataBase->query($Query)->fetch(PDO::FETCH_ASSOC);

        if (!$Result || $Result['ppr_allowed'] == "N")
        {
            //TODO AÑADIR BORRADO DE SESION, LIMPIEZA DE DATOS, CIERRE DE SESION
            // Y REDIRECCIÓN A LA LANDING PAGE
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function ActiveSession(): void
    {
        if (!$_SESSION["UserID"])
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
}