<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

/*function validarSQL($cadena) {
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
}*/
if (isset($_POST['eliminarUsuario'])) {
    if (isset($_POST['idUsuario']) && !empty($_POST['idUsuario'])) {
        // Establecer conexión a la base de datos
        require_once '../Database.php';
        $conexion = database::LoadDatabase();

        // Obtener el ID del usuario a eliminar
        $idUsuario = $_POST['idUsuario'];

        try {
            // Eliminar usuario de la base de datos
            $query = "DELETE FROM pps_users WHERE usu_id = ?";
            $stmt = $conexion->prepare($query);
            $exito = $stmt->execute([$idUsuario]);

            if ($exito) {
                // Redirigir al usuario de nuevo a la página actual
                header("Location: {$_SERVER['REQUEST_URI']}");
                exit(); // Detener la ejecución del script para evitar más procesamiento
            } else {
                echo "Error al eliminar el usuario.";
            }
        } catch (Exception $e) {
            echo "Error al eliminar usuario: " . $e->getMessage();
        }

        // Cerrar la conexión
        $conexion = null;
    } else {
        echo "No se proporcionó un ID de usuario válido.";
    }
}


// Mostrar la lista de usuarios
function MostrarUsuarios($conexion) {
    $query = "SELECT * FROM pps_users";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    if ($result) {
        echo "<h2>Lista de Usuarios</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Contraseña</th><th>Teléfono</th><th>Correo</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>{$row['usu_id']}</td>";
            echo "<td>{$row['usu_name']}</td>";
            echo "<td>{$row['usu_rol']}</td>";
            echo "<td>{$row['usu_password']}</td>";
            echo "<td>{$row['usu_phone']}</td>";
            echo "<td>{$row['usu_email']}</td>";
            echo "<td>";
            echo "<form action='Mod_user.php' method='post'>";
            echo "<input type='hidden' name='idUsuario' value='{$row['usu_id']}'>"; // Campo oculto para enviar el ID del usuario
            echo "<button type='submit'>Modificar</button>"; // Botón para enviar el formulario
            echo "</form>";
            echo "</td>";
            echo "<td>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='idUsuario' value='{$row['usu_id']}'>"; // Campo oculto para enviar el ID del usuario
            echo "<button type='submit' name='eliminarUsuario'>Eliminar</button>"; // Botón para enviar el formulario de eliminación
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron usuarios.";
    }
}

?>
<button onclick="window.location.href='Rol_Admin.php'">Ir a Rol-Admin</button>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilo.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> <!-- Referencia a jQuery -->
    <title>Administración de Usuarios</title>
</head>
<body>
    <h1>Administración de Usuarios</h1>

    <?php
    // Mostrar la lista de usuarios
    MostrarUsuarios($conexion);
    ?>

    <!-- Formulario para crear un nuevo usuario -->
    <h2>Crear Nuevo Usuario</h2>
    <form id="formCrearUsuario" method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" pattern="[a-zA-Z\s]+" required>
        <br><br>
        <label for="passwd">Contraseña:</label>
        <input type="password" id="passwd" name="passwd" pattern="^[a-zA-Z0-9!@#$%^&*()_+}{:;?]+$" required>
        <br><br>
        <label for="telf">Telefono:</label>
        <input type="text" id="telf" name="telf" pattern="\d{9}" title="El número de teléfono debe tener 9 dígitos" required>
        <br><br>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol">
            <option value="Administrador">Administrador</option>
            <option value="Usuario">Usuario</option>
            <option value="Soporte">Soporte</option>
        </select>
        <br><br>
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" required>
        <br><br>
        <button type="submit" id="btnCrearUsuario" name="crearUsuario">Crear Usuario</button>
    </form>
    <script>
   $(document).ready(function(){
        // AJAX para enviar el formulario de modificación de usuario
        $("#btnCrearUsuario").click(function(){
            $.ajax({
                url: "crear_usuario.php", // Ruta del archivo PHP que procesa el formulario
                type: "POST",
                data: $("#formCrearUsuario").serialize(), // Serializar el formulario
                success: function(response){
                    alert(response); // Mostrar mensaje de respuesta
                    // Redireccionar a la página de administración de usuarios
                    window.location.href = "Gestion_Users.php";
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
// Procesamiento del formulario para crear un nuevo usuario
// if (isset($_POST['crearUsuario'])) {
//     $nombre = $_POST['nombre'];
//     $rol = $_POST['rol'];
//     $passwd = $_POST['passwd'];
//     $telf = $_POST['telf'];
//     $email = $_POST['email'];

    // Verificar si hay campos susceptibles a inyección SQL o si el usuario ya existe
    // $query_verificar = "SELECT * FROM pps_users WHERE usu_name=?";
    // $stmt_verificar = $conexion->prepare($query_verificar);
    // $stmt_verificar->execute([$nombre]);
    // $result_verificar = $stmt_verificar->fetchAll(PDO::FETCH_ASSOC);

    // if (validarSQL($nombre) || validarSQL($rol) || validarSQL($passwd) || validarSQL($telf) || validarSQL($email) || count($result_verificar) > 0) {
    //     echo "ERROR, DATOS NO VÁLIDOS O USUARIO YA EXISTENTE";
    // } else {
        // Continuar con el procesamiento del formulario y ejecución de la consulta preparada
        // Insertar nuevo usuario en la base de datos
//         $query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email) VALUES (?, ?, ?, ?, ?)";
//         $stmt_insert = $conexion->prepare($query_insert);
//         if ($stmt_insert->execute([$nombre, $rol, $passwd, $telf, $email])) {
//             echo "Usuario creado exitosamente.";
//         } else {
//             echo "Error al crear usuario: " . $stmt_insert->errorInfo()[2];
//         }
//     }
// }
?>

<?php
// Cerrar la conexión
$conexion = null;
?>

