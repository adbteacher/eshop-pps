<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Nueva Dirección</title>
</head>

<body>
    <?php
    require_once 'db.php';

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
    function addNewAddress($user_id, $line1, $line2, $city, $state, $postal_code, $country)
    {
        $connection = GetDatabaseConnection();
        $sql = "INSERT INTO pps_user_addresses (addr_user_id, addr_line1, addr_line2, addr_city, addr_state, addr_postal_code, addr_country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$user_id, $line1, $line2, $city, $state, $postal_code, $country]);
    }

    // Manejar el envío del formulario para añadir una nueva dirección
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitNewAddress'])) {
        $user_id = '1'; // Obtener el ID del usuario de alguna manera
        $line1 = isset($_POST['addr_line1']) ? cleanInput($_POST['addr_line1']) : '';
        $line2 = isset($_POST['addr_line2']) ? cleanInput($_POST['addr_line2']) : '';
        $city = isset($_POST['addr_city']) ? cleanInput($_POST['addr_city']) : '';
        $state = isset($_POST['addr_state']) ? cleanInput($_POST['addr_state']) : '';
        $postal_code = isset($_POST['addr_postal_code']) ? cleanInput($_POST['addr_postal_code']) : '';
        $country = isset($_POST['addr_country']) ? cleanInput($_POST['addr_country']) : '';

        // Añadir la nueva dirección a la base de datos
        addNewAddress($user_id, $line1, $line2, $city, $state, $postal_code, $country);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_addres.php");
        exit;
    }
    ?>

    <!-- Formulario para añadir una nueva dirección -->
    <h1>Añadir Nueva Dirección</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="addr_line1">Línea 1:</label>
        <input type="text" name="addr_line1" required>
        <br>
        <label for="addr_line2">Línea 2:</label>
        <input type="text" name="addr_line2">
        <br>
        <label for="addr_city">Ciudad:</label>
        <input type="text" name="addr_city" required>
        <br>
        <label for="addr_state">Estado:</label>
        <input type="text" name="addr_state">
        <br>
        <label for="addr_postal_code">Código Postal:</label>
        <input type="text" name="addr_postal_code" required>
        <br>
        <label for="addr_country">País:</label>
        <input type="text" name="addr_country" required>
        <br>
        <button type="submit" name="submitNewAddress">Añadir Dirección</button>
    </form>
</body>

</html>