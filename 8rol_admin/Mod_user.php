<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="estilo.css"> <!-- Referencia al archivo de estilo CSS -->
</head>
<body>
    <h1>Modificar Usuario</h1>

    <?php
    // Establecer conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "ceti");

    // Verificar la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener el ID del usuario a modificar
    $idUsuario = $_POST['idUsuario'];

    // Obtener los datos del usuario
    $query = "SELECT * FROM pps_users WHERE usu_id = $idUsuario";
    $result = $conexion->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Usuario no encontrado.";
        exit;
    }
    ?>

    <h2>Modificar Usuario</h2>
    <form id="formModificarUsuario" method="post">
        <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>"> <!-- Campo oculto con el ID del usuario -->
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo $row['usu_name']; ?>" required>
        <br><br>
        <label for="passwd">Contraseña:</label>
        <input type="password" id="passwd" name="passwd" value="<?php echo $row['usu_password']; ?>" required>
        <br><br>
        <label for="telf">Teléfono:</label>
        <input type="text" id="telf" name="telf" value="<?php echo $row['usu_phone']; ?>" required>
        <br><br>
        <label for="rol">Rol:</label>
        <select id="rol" name="rol">
            <option value="Administrador" <?php if ($row['usu_rol'] == 'Administrador') echo 'selected'; ?>>Administrador</option>
            <option value="Usuario" <?php if ($row['usu_rol'] == 'Usuario') echo 'selected'; ?>>Usuario</option>
            <option value="Soporte" <?php if ($row['usu_rol'] == 'Soporte') echo 'selected'; ?>>Soporte</option>
        </select>
        <br><br>
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" value="<?php echo $row['usu_email']; ?>" required>
        <br><br>
        <button type="button" id="btnModificarUsuario">Modificar Usuario</button>
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        // AJAX para enviar el formulario de modificación de usuario
        $("#btnModificarUsuario").click(function(){
            $.ajax({
                url: "procesar_modificacion_usuario.php", // Ruta del archivo PHP que procesa el formulario
                type: "POST",
                data: $("#formModificarUsuario").serialize(), // Serializar el formulario
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
