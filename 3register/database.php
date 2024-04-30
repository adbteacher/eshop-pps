<?php
// DB
// Conexión a la base de datos
$DbServername = "localhost";
$DbUsername = "usuario";
$DbPassword = "1234";
$DbName = "eshop";

// Conectar con la base de datos
$Conn = mysqli_connect($DbServername, $DbUsername, $DbPassword, $DbName);

// Verificar la conexión con la base de datos
if (mysqli_connect_errno()) {
    exit('Error conecting to the database' . mysqli_connect_error());
}

?>