<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST['idUsuario'];
    $nombre = $_POST['nombre'];
    $telf = $_POST['telf'];
    $rol = $_POST['rol'];
    $email = $_POST['email'];
    $passwd = $_POST['passwd']; // Obtener la contraseña del formulario

    // Validar que el nombre no contenga caracteres susceptibles a inyección SQL
    if (!preg_match("/^[a-zA-Z\s]+$/", $nombre)) {
        echo "El nombre contiene caracteres inválidos.";
        exit; // Detener la ejecución si el nombre es inválido
    }

    // Validar que el número de teléfono tenga exactamente 9 caracteres y sean todos numéricos
    if (strlen($telf) !== 9 || !ctype_digit($telf)) {
        echo "El número de teléfono debe tener exactamente 9 dígitos y ser numérico.";
        exit; // Detener la ejecución si el número de teléfono es inválido
    }

    // Preparar la consulta de actualización
    $query = "UPDATE pps_users SET usu_name=?, usu_phone=?, usu_rol=?, usu_email=?"; // Query base sin contraseña
    $params = [$nombre, $telf, $rol, $email];

    // Verificar si se proporcionó una nueva contraseña
    if (!empty($passwd)) {
        // Validar que la contraseña tenga al menos 8 caracteres y no contenga caracteres susceptibles a inyección SQL
        if (strlen($passwd) < 8 || !preg_match("/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]+$/", $passwd)) {
            echo "La contraseña debe tener al menos 8 caracteres y no contener caracteres inválidos.";
            exit; // Detener la ejecución si la contraseña es inválida
        }
        // Hashear la nueva contraseña
        $hashed_passwd = password_hash($passwd, PASSWORD_DEFAULT);
        // Agregar la contraseña hasheada a la consulta y parámetros
        $query .= ", usu_password=?";
        $params[] = $hashed_passwd;
    }

    // Agregar la condición WHERE para filtrar por ID de usuario
    $query .= " WHERE usu_id=?";
    $params[] = $idUsuario;

    // Ejecutar la consulta de actualización
    $stmt = $conexion->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo "Usuario actualizado exitosamente.";
    } else {
        echo "No se realizaron cambios en los datos del usuario.";
    }
}

// Cerrar la conexión
$conexion = null;
?>







