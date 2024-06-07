<?php
/*
     Este archivo procesa el formulario de creacion de usuario.
     */
require_once '../autoload.php';

session_start();

functions::checkAdminAccess();

// Verificar si el usuario está autenticado
functions::ActiveSession();

// Comprobar permisos al programa
functions::HasPermissions(basename(__FILE__));

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

function validarSQL($cadena): bool
{
    // Lista de palabras reservadas de SQL
    $palabrasReservadas = array("SELECT", "INSERT", "UPDATE", "DELETE", "FROM", "WHERE", "DROP", "UNION", "TABLE", "DATABASE", "ORDER BY", "GROUP BY", "HAVING", "JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN", "ON", "AND", "OR", "LIMIT");

    // Convertir la cadena a mayúsculas para comparación sin distinción de mayúsculas y minúsculas
    $cadena = strtoupper($cadena);

    // Verificar si alguna palabra reservada de SQL está presente en la cadena
    foreach ($palabrasReservadas as $palabra)
    {
        if (preg_match("/\b$palabra\b/", $cadena))
        {
            return true;
        }
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $nombre = $_POST['nombre'];
    $rol    = $_POST['rol'];
    $passwd = $_POST['passwd'];
    $telf   = $_POST['telf'];
    $email  = $_POST['email'];
    if (!empty($passwd)) {
        if (strlen($passwd) < 8) {
            echo 'La contraseña debe tener al menos 8 caracteres.';
            exit;
        }
        if (!preg_match("/^[a-zA-Z0-9@\-_\+]+$/", $passwd) || validarSQL($passwd)) {
            echo 'La contraseña no cumple los requisitos minimos';
            exit;
        }
    } else {
        echo 'La contraseña no puede estar vacía.';
        exit;
    }
    
    try
    {
        // Verificar si hay campos susceptibles a inyección SQL o si el usuario ya existe
        $query_verificar_nombre = "SELECT * FROM pps_users WHERE usu_name=?";
        $stmt_verificar_nombre  = $conexion->prepare($query_verificar_nombre);
        $stmt_verificar_nombre->execute([$nombre]);
        $result_verificar_nombre = $stmt_verificar_nombre->fetchAll(PDO::FETCH_ASSOC);

        $query_verificar_email = "SELECT * FROM pps_users WHERE usu_email=?";
        $stmt_verificar_email  = $conexion->prepare($query_verificar_email);
        $stmt_verificar_email->execute([$email]);
        $result_verificar_email = $stmt_verificar_email->fetchAll(PDO::FETCH_ASSOC);

        if (validarSQL($nombre) || validarSQL($rol) || validarSQL($passwd) || validarSQL($telf) || validarSQL($email) || count($result_verificar_nombre) > 0)
        {
            echo "ERROR, DATOS NO VÁLIDOS O NOMBRE DE USUARIO YA EXISTENTE";
        }
        else if (count($result_verificar_email) > 0)
        {
            echo "ERROR, CORREO ELECTRÓNICO YA EXISTENTE";
        }
        else
        {
            // Hashear la contraseña
            $hashed_passwd = password_hash($passwd, PASSWORD_DEFAULT);

            // Insertar nuevo usuario en la base de datos con usu_status='A'
            $query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email, usu_status) VALUES (?, ?, ?, ?, ?, 'A')";
            $stmt_insert  = $conexion->prepare($query_insert);
            if ($stmt_insert->execute([$nombre, $rol, $hashed_passwd, $telf, $email]))
            {
                echo "Usuario creado exitosamente.";
            }
            else
            {
                throw new Exception("Error al crear usuario");
            }
        }
    }
    catch (Exception $e)
    {
        echo "Error: Algo ha salido mal. Detalles: " . $e->getMessage();
    }
}
else
{
    echo "Por favor, rellena todos los campos";
}