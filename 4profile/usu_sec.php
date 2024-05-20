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

require_once '../Database.php';

function validatePassword($password)
{
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    if (!preg_match('/[.+\-*]/', $password)) {
        return false;
    }
    return true;
}

function ChangePassword($user_email, $old_password, $new_password, $confirm_new_password): bool
{
    $connection = database::LoadDatabase(); // Obtener la conexión a la base de datos

    // Verificar la contraseña antigua
    $sql = "SELECT usu_password FROM pps_users WHERE usu_email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$user_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($old_password, $user['usu_password'])) {
        $_SESSION['error_message'] = 'Error: La contraseña antigua es incorrecta.';
        return false;
    }

    if ($confirm_new_password !== $new_password) {
        $_SESSION['error_message'] = 'Error: Las contraseñas no coinciden.';
        return false;
    }

    if (!validatePassword($new_password)) {
        $_SESSION['error_message'] = 'Error: La nueva contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un símbolo (.+-*).';
        return false;
    }

    // Generar un hash de la nueva contraseña
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Actualizar la contraseña en la base de datos
    $sql = "UPDATE pps_users SET usu_password = ? WHERE usu_email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$hashed_new_password, $user_email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Contraseña actualizada con éxito.';
        return true;
    } else {
        $_SESSION['error_message'] = 'Error al actualizar la contraseña.';
        return false;
    }
}

// Manejar el envío del formulario para cambiar la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Error: Token CSRF inválido.';
        header("Location: usu_sec.php");
        exit;
    }

    $user_email = $_SESSION['UserEmail'];
    $old_password = isset($_POST['old_password']) ? cleanInput($_POST['old_password']) : '';
    $new_password = isset($_POST['new_password']) ? cleanInput($_POST['new_password']) : '';
    $confirm_new_password = isset($_POST['confirm_new_password']) ? cleanInput($_POST['confirm_new_password']) : '';

    if (ChangePassword($user_email, $old_password, $new_password, $confirm_new_password)) {
        header("Location: usu_sec.php");
    } else {
        header("Location: usu_sec.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
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

        .title-container,
        .second-factor-container {
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            padding: 20px;
        }
    </style>
    <script>
        function validatePassword() {
            const password = document.getElementById('new_password').value;
            const errorDiv = document.getElementById('password_error');
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.+\-*]).{8,}$/;

            if (!regex.test(password)) {
                errorDiv.innerHTML = 'La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un símbolo (.+-*).';
                return false;
            }

            errorDiv.innerHTML = '';
            return true;
        }
    </script>
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
                    <?php
                    if (isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                        unset($_SESSION['error_message']);
                    }
                    if (isset($_SESSION['success_message'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                        unset($_SESSION['success_message']);
                    }
                    ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form-container" onsubmit="return validatePassword()">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="form-group">
                            <label for="old_password" class="form-label">Contraseña Antigua:</label>
                            <input type="password" id="old_password" name="old_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">Nueva Contraseña:</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required oninput="validatePassword()">
                            <div id="password_error" class="text-danger"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="second-factor-container">
                    <h3 class="text-center">Activar segundo factor</h3>
                    <div class="container mt-3 text-center">
                        <a href="../1login/activate_2fa.php" class="btn btn-primary">Activar segundo factor</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>