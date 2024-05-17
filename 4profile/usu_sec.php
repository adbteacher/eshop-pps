<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
    exit;
}

// Generar un token CSRF y almacenarlo en la sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token']; // CSRF TOKEN

// Función de limpieza:
function cleanInput($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
    $input = str_replace(['=', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
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
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Error: Invalid CSRF token.";
        exit;
    }

    $user_email = $_SESSION['UserEmail'];
    $old_password = isset($_POST['old_password']) ? cleanInput($_POST['old_password']) : '';
    $new_password = isset($_POST['new_password']) ? cleanInput($_POST['new_password']) : '';

    ChangePassword($user_email, $old_password, $new_password);
}
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 400px;
            /* Ancho máximo del formulario */
            margin: 0 auto;
            /* Centra el formulario horizontalmente */
            padding: 20px;
            /* Añade espaciado interior al formulario */
            border: 1px solid #ccc;
            /* Añade un borde al formulario */
            border-radius: 5px;
            /* Añade bordes redondeados al formulario */
            background-color: #f9f9f9;
            /* Añade un color de fondo al formulario */
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: bold;
        }

        .container {
            padding: 10px;
        }

        .title-container {
            margin-top: 20px;
            /* Ajusta el margen superior del contenedor del título */
            border: 1px solid #ccc;
            /* Añade un borde al contenedor del título */
            border-radius: 5px;
            /* Añade bordes redondeados al contenedor del título */
            background-color: #f9f9f9;
            /* Añade un color de fondo al contenedor del título */
            padding: 20px;
            /* Añade espaciado interior al contenedor del título */
        }

        .second-factor-container {
            margin-top: 20px;
            /* Ajusta el margen superior del contenedor del segundo factor */
            border: 1px solid #ccc;
            /* Añade un borde al contenedor del segundo factor */
            border-radius: 5px;
            /* Añade bordes redondeados al contenedor del segundo factor */
            background-color: #f9f9f9;
            /* Añade un color de fondo al contenedor del segundo factor */
            padding: 20px;
            /* Añade espaciado interior al contenedor del segundo factor */
        }
    </style>
</head>

<body>

    <?php
    include "../nav.php";
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="title-container">
                    <h3 class="text-center">Cambiar Contraseña</h3>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-container">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="form-group">
                            <label for="old_password" class="form-label">Contraseña Antigua:</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">Nueva Contraseña:</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" name="confirm_new_password" class="form-control" required>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="second-factor-container">
                    <h3 class="text-center">Activar segundo factor</h3>
                    <div class="container mt-3 text-center">
                        <a href="activate_2fa.php" class="btn btn-primary">Activar segundo factor</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>