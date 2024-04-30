<?php
// Establecer conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "ceti");

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$rol = $_POST['rol'];
$passwd = $_POST['passwd'];
$telf = $_POST['telf'];
$email = $_POST['email'];

// Insertar nuevo usuario en la base de datos
$query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email) VALUES ('$nombre', '$rol', '$passwd', '$telf', '$email')";
if ($conexion->query($query_insert) === TRUE) {
    echo "Usuario creado exitosamente.";
} else {
    echo "Error al crear usuario: " . $conexion->error;
}

// Cerrar la conexión
$conexion->close();
?>