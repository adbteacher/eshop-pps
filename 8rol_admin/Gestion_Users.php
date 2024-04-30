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
    // Establecer conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "ceti");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Función para mostrar la lista de usuarios
    /*function mostrarUsuarios($conexion) {
        $query = "SELECT * FROM pps_users";
        $result = $conexion->query($query);

        if ($result->num_rows > 0) {
            echo "<h2>Lista de Usuarios</h2>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['usu_name']} - Rol: {$row['usu_rol']}</li>";
            }
            echo "</ul>";
        } else {
            echo "No se encontraron usuarios.";
        }
    }*/
    function mostrarUsuarios($conexion) {
        $query = "SELECT * FROM pps_users";
        $result = $conexion->query($query);

        if ($result->num_rows > 0) {
            echo "<h2>Lista de Usuarios</h2>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Contraseña</th><th>Teléfono</th><th>Correo</th></tr>";
            while ($row = $result->fetch_assoc()) {
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
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No se encontraron usuarios.";
        }
    }

    // Mostrar la lista de usuarios
    mostrarUsuarios($conexion);

    // Formulario para crear un nuevo usuario
    ?>
    <h2>Crear Nuevo Usuario</h2>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>
        <br>
        <br>
        <label for="passwd">Contraseña:</label>
        <input type="password" id="passwd" name="passwd" required>
        <br>
        <br>
        <label for="telf">Telefono:</label>
        <input type="text" id="telf" name="telf" pattern="\d{9}" title="El número de teléfono debe tener 9 dígitos" required>
        <br>
        <br>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol">
            <option value="Administrador">Administrador</option>
            <option value="Usuario">Usuario</option>
            <option value="Soporte">Soporte</option>
        </select>
        <br>
        <br>
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <br>
        <button type="submit" name="crearUsuario">Crear Usuario</button>
        <br>
    </form>
    
    <?php
    // Procesamiento del formulario para crear un nuevo usuario
    if (isset($_POST['crearUsuario'])) {
        $nombre = $_POST['nombre'];
        $rol = $_POST['rol'];
        $passwd = $_POST['passwd'];
        $telf = $_POST['telf'];
        $email = $_POST['email'];

        // Verificar si el usuario ya existe
        $query_verificar = "SELECT * FROM pps_users WHERE usu_name='$nombre'";
        $result_verificar = $conexion->query($query_verificar);

        if ($result_verificar->num_rows > 0) {
            echo "Error: El usuario ya existe.";
        } else {
            // Insertar nuevo usuario en la base de datos
            $query_insert = "INSERT INTO pps_users (usu_name, usu_rol, usu_password, usu_phone, usu_email) VALUES ('$nombre', '$rol', '$passwd', '$telf', '$email')";
            if ($conexion->query($query_insert) === TRUE) {
                echo "Usuario creado exitosamente.";
            } else {
                echo "Error al crear usuario: " . $conexion->error;
            }
        }

        // Mostrar la lista actualizada de usuarios
        //mostrarUsuarios($conexion);
    }

    // Cerrar la conexión
    $conexion->close();
    ?>

</body>
</html>