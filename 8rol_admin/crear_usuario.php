<?php
require_once 'database.php'; // Incluye el archivo de conexión PDO

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

if (isset($_POST['crearUsuario'])) {
    $nombre = $_POST['nombre'];
    $rol = $_POST['rol'];
    $passwd = $_POST['passwd'];
    $telf = $_POST['telf'];
    $email = $_POST['email'];

    // Verificar si hay campos susceptibles a inyección SQL o si el usuario ya existe
    $query_verificar = "SELECT * FROM pps_users WHERE usu_name=?";
    $stmt_verificar = $conexion->prepare($query_verificar);
    $stmt_verificar->execute([$nombre]);
    $result_verificar = $stmt_verificar->fetchAll(PDO::FETCH_ASSOC);

    if (validarSQL($nombre) || validarSQL($rol) || validarSQL($passwd) || validarSQL($telf) || validarSQL($email) || count($result_verificar) > 0) {
        echo "ERROR, DATOS NO VÁLIDOS O USUARIO YA EXISTENTE";
    } else {
        // Continuar con el procesamiento del formulario y ejecución de la consulta preparada
        // Insertar nuevo usuario en la base de datos
        $query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($query_insert);
        if ($stmt_insert->execute([$nombre, $rol, $passwd, $telf, $email])) {
            echo "Usuario creado exitosamente.";
        } else {
            echo "Error al crear usuario: " . $stmt_insert->errorInfo()[2];
        }
    }
}

// Cerrar la conexión
$conexion = null;

function validarSQL($cadena) {
    // Lista de palabras reservadas de SQL
    $palabrasReservadas = array("SELECT", "INSERT", "UPDATE", "DELETE", "FROM", "WHERE", "DROP", "UNION", "TABLE", "DATABASE", "ORDER BY", "GROUP BY", "HAVING", "JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN", "ON", "AND", "OR", "LIMIT");

    // Convertir la cadena a mayúsculas para comparación sin distinción de mayúsculas y minúsculas
    $cadena = strtoupper($cadena);

    // Verificar si alguna palabra reservada de SQL está presente en la cadena
    foreach ($palabrasReservadas as $palabra) {
        if (preg_match("/\b$palabra\b/", $cadena)) {
            return true;
        }
    }
    return false;
}
?>