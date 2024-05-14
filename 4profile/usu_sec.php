<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
    exit;
}

// Función de limpieza:
function cleanInput($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
    $input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
    return $input;
}

require_once '../Database.php'; // 

function ChangePassword($user_email, $old_password, $new_password): bool
{
    $connection = database::LoadDatabase(); // Obtener la conexión a la base de datos

    // Verificar la contraseña antigua
    $sql = "SELECT usu_password FROM pps_users WHERE usu_email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($old_password, $user['usu_password'])) {
        echo "Error: La contraseña antigua es incorrecta.";
        return false;
    }

    // Generar un hash de la nueva contraseña
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $sql = "UPDATE pps_users SET usu_password = ? WHERE usu_email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$hashed_new_password, $user_email]);

    if ($stmt->rowCount() > 0) {
        echo "Contraseña actualizada con éxito.<br>";
        return true;
    } else {
        echo "Error al actualizar la contraseña.";
        return false;
    }
}

// Manejar el envío del formulario para cambiar la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_email = $_SESSION['email'];
    $old_password = isset($_POST['old_password']) ? cleanInput($_POST['old_password']) : '';
    $new_password = isset($_POST['new_password']) ? cleanInput($_POST['new_password']) : '';

    ChangePassword($user_email, $old_password, $new_password);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
</head>

<body>
    <h1>Cambiar Contraseña</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="old_password">Contraseña Antigua:</label><br>
        <input type="password" name="old_password" required><br><br>
        <label for="new_password">Nueva Contraseña:</label><br>
        <input type="password" name="new_password" required><br><br>
        <label for="confirm_new_password">Confirmar Nueva Contraseña:</label><br>
        <input type="password" name="confirm_new_password" required><br><br>
        <button type="submit" name="submit">Cambiar Contraseña</button>
    </form>
    <h1>Segundo factor de autenticación</h1>
    <ul>
        <li><a href="../1login/activate_2fa.php">Activar segundo factor</a></li>
    </ul>
</body>

</html>