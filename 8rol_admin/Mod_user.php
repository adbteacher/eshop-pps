<?php
    require_once '../autoload.php'; // Incluye el archivo de conexión PDO
    session_start();

    if (!isset($_SESSION['UserRol'])) {
        echo "<p class='text-danger'>Acceso denegado. No se encontró el rol de usuario en la sesión.</p>";
        exit;
    }
    
    // Verificar si el usuario es administrador
    if ($_SESSION["UserRol"] !== 'A') {
        echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
        exit;
    }
    // Obtener el ID del usuario a modificar
    $idUsuario = $_POST['idUsuario'];

    // Obtener una conexión a la base de datos
    $conexion = database::LoadDatabase();

    try {
        // Preparar la consulta para obtener los datos del usuario
        $query = "SELECT * FROM pps_users WHERE usu_id = ?";
        $stmt  = $conexion->prepare($query);
        $stmt->execute([$idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo "Usuario no encontrado.";
            exit;
        }
    } catch (PDOException $e) {
        // Manejar cualquier excepción y mostrar un mensaje genérico
        echo "Algo ha salido mal.";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <!-- Añadir CSS de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5">
    <h1>Modificar Usuario</h1>
    <form id="formModificarUsuario" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>"> <!-- Campo oculto con el ID del usuario -->
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['usu_name']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="nueva_passwd">Nueva Contraseña:</label>
            <input type="password" id="nueva_passwd" name="nueva_passwd" class="form-control">
        </div>
        <div class="form-group">
            <label for="confirmar_passwd">Confirmar Nueva Contraseña:</label>
            <input type="password" id="confirmar_passwd" name="confirmar_passwd" class="form-control">
        </div>
        <div class="form-group">
            <label for="telf">Teléfono:</label>
            <input type="text" id="telf" name="telf" value="<?php echo htmlspecialchars($row['usu_phone']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" class="form-control">
                <option value="A" <?php if ($row['usu_rol'] == 'A') echo 'selected'; ?>>Administrador</option>
                <option value="U" <?php if ($row['usu_rol'] == 'U') echo 'selected'; ?>>Usuario</option>
                <option value="S" <?php if ($row['usu_rol'] == 'S') echo 'selected'; ?>>Soporte</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Correo:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['usu_email']); ?>" class="form-control" required>
        </div>
        <button type="button" id="btnModificarUsuario" class="btn btn-primary">Modificar Usuario</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        // AJAX para enviar el formulario de modificación de usuario
        $("#btnModificarUsuario").click(function () {
            var nuevaPasswd = $("#nueva_passwd").val();
            var confirmarPasswd = $("#confirmar_passwd").val();

            if (nuevaPasswd !== confirmarPasswd) {
                alert("Las contraseñas no coinciden.");
                return;
            }

            $.ajax({
                url: "procesar_modificacion_usuario.php", // Ruta del archivo PHP que procesa el formulario
                type: "POST",
                data: $("#formModificarUsuario").serialize(), // Serializar el formulario
                success: function (response) {
                    alert(response); // Mostrar mensaje de respuesta
                    // Redireccionar a la página de administración de usuarios
                    window.location.href = "Gestion_Users.php";
                }
            });
        });
    });
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
