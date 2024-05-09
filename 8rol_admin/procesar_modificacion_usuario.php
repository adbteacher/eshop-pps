<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST['idUsuario'];
    $nombre = $_POST['nombre'];
    $passwd = $_POST['passwd'];
    $telf = $_POST['telf'];
    $rol = $_POST['rol'];
    $email = $_POST['email'];

    // Validar que el nombre no contenga caracteres susceptibles a inyección SQL
    if (!preg_match("/^[a-zA-Z\s]+$/", $nombre)) {
        echo "El nombre contiene caracteres inválidos.";
        exit; // Detener la ejecución si el nombre es inválido
    }

    // Validar que la contraseña tenga al menos 8 caracteres y no contenga caracteres susceptibles a inyección SQL
    if (strlen($passwd) < 8 || !preg_match("/^[a-zA-Z0-9!@#$%^&*()_+}{:;?]+$/", $passwd)) {
        echo "La contraseña debe tener al menos 8 caracteres y no contener caracteres inválidos.";
        exit; // Detener la ejecución si la contraseña es inválida
    }

    // Validar que el número de teléfono tenga exactamente 9 caracteres y sean todos numéricos
    if (strlen($telf) !== 9 || !ctype_digit($telf)) {
        echo "El número de teléfono debe tener exactamente 9 dígitos y ser numérico.";
        exit; // Detener la ejecución si el número de teléfono es inválido
    }

    // Actualizar usuario en la base de datos utilizando consultas preparadas
    $query = "UPDATE pps_users SET usu_name=?, usu_password=?, usu_phone=?, usu_rol=?, usu_email=? WHERE usu_id=?";
    $stmt = $conexion->prepare($query);
    $stmt->execute([$nombre, $passwd, $telf, $rol, $email, $idUsuario]);

    if ($stmt->rowCount() > 0) {
        echo "Usuario actualizado exitosamente.";
    } else {
        echo "Error al actualizar usuario.";
    }
}

// Cerrar la conexión
$conexion = null;
?>

