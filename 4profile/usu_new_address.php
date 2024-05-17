<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Nueva Dirección</title>
</head>

<body>
    <?php
    session_start(); // Iniciar la sesión si aún no se ha iniciado

    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['UserEmail']) || !isset($_SESSION['UserID'])) {
        header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
        exit;
    }

    require_once '../Database.php'; // Conexión a la PDO.

    $UserID = $_SESSION['UserID']; // ID de usuario

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
        $input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
        return $input;
    }

    // Función para añadir una nueva dirección
    function addNewadress($user_id, $line1, $line2, $city, $state, $postal_code, $country)
    {
        $connection = database::LoadDatabase();
        $sql = "INSERT INTO pps_addresses_per_user (adr_user, adr_line1, adr_line2, adr_city, adr_state, adr_postal_code, adr_country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$user_id, $line1, $line2, $city, $state, $postal_code, $country]);
    }

    // Manejar el envío del formulario para añadir una nueva dirección
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitNewadress'])) {
        // Verificar el token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "Error: Invalid CSRF token.";
            exit;
        }

        $line1 = isset($_POST['adr_line1']) ? cleanInput($_POST['adr_line1']) : '';
        $line2 = isset($_POST['adr_line2']) ? cleanInput($_POST['adr_line2']) : '';
        $city = isset($_POST['adr_city']) ? cleanInput($_POST['adr_city']) : '';
        $state = isset($_POST['adr_state']) ? cleanInput($_POST['adr_state']) : '';
        $postal_code = isset($_POST['adr_postal_code']) ? cleanInput($_POST['adr_postal_code']) : '';
        $country = isset($_POST['adr_country']) ? cleanInput($_POST['adr_country']) : '';

        // Añadir la nueva dirección a la base de datos
        addNewadress($UserID, $line1, $line2, $city, $state, $postal_code, $country);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_addres.php");
        exit;
    }
    ?>

    <!-- Formulario para añadir una nueva dirección -->
    <h1>Añadir Nueva Dirección</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <label for="adr_line1">Línea 1:</label>
        <input type="text" name="adr_line1" required>
        <br>
        <label for="adr_line2">Línea 2:</label>
        <input type="text" name="adr_line2">
        <br>
        <label for="adr_city">Ciudad:</label>
        <input type="text" name="adr_city" required>
        <br>
        <label for="adr_state">Estado:</label>
        <input type="text" name="adr_state">
        <br>
        <label for="adr_postal_code">Código Postal:</label>
        <input type="text" name="adr_postal_code" required>
        <br>
        <label for="adr_country">País:</label>
        <input type="text" name="adr_country" required>
        <br>
        <button type="submit" name="submitNewadress">Añadir Dirección</button>
    </form>
</body>

</html>