<?php
require_once 'database.php'; // Incluye el archivo de conexión PDO

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idUsuario = $_POST['idUsuario'];
    $nombre = $_POST['nombre'];
    $passwd = $_POST['passwd'];
    $telf = $_POST['telf'];
    $rol = $_POST['rol'];
    $email = $_POST['email'];

    // Validar datos (por ejemplo, verificar que los campos obligatorios no estén vacíos)

    // Actualizar usuario en la base de datos
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

