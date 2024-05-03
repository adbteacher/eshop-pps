<?php
require_once 'database.php'; // Incluye el archivo de conexión PDO

try {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $rol = $_POST['rol'];
    $passwd = $_POST['passwd'];
    $telf = $_POST['telf'];
    $email = $_POST['email'];

    // Obtener una conexión a la base de datos
    $conexion = database::LoadDatabase();

    // Preparar la consulta para insertar un nuevo usuario
    $query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conexion->prepare($query_insert);
    $stmt_insert->execute([$nombre, $rol, $passwd, $telf, $email]);

    // Verificar si se insertó correctamente
    if ($stmt_insert->rowCount() > 0) {
        echo "Usuario creado exitosamente.";
    } else {
        echo "Error al crear usuario.";
    }
} catch (PDOException $e) {
    // Manejar cualquier excepción y mostrar un mensaje genérico
    echo "Algo ha salido mal.";
}
?>

