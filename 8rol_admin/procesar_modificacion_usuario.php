<?php
// Establecer conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "ceti");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos del formulario
$idUsuario = $_POST['idUsuario'];
$nombre = $_POST['nombre'];
$passwd = $_POST['passwd'];
$telf = $_POST['telf'];
$rol = $_POST['rol'];
$email = $_POST['email'];

// Validar datos (por ejemplo, verificar que los campos obligatorios no estén vacíos)

// Actualizar usuario en la base de datos
$query = "UPDATE pps_users SET usu_name='$nombre', usu_password='$passwd', usu_phone='$telf', usu_rol='$rol', usu_email='$email' WHERE usu_id='$idUsuario'";
if ($conexion->query($query) === TRUE) {
    echo "Usuario actualizado exitosamente.";
} else {
    echo "Error al actualizar usuario: " . $conexion->error;
}

// Cerrar la conexión
$conexion->close();
?>
