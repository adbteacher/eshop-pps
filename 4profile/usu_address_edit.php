<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dirección</title>
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

    // Obtener el ID de la dirección a editar
    $edit_address_id = isset($_POST['edit_address_id']) ? cleanInput($_POST['edit_address_id']) : '';

    // Obtener los detalles de la dirección a editar
    $edit_address = null;
    if (!empty($edit_address_id)) {
        $connection = GetDatabaseConnection();
        $sql = "SELECT * FROM pps_user_addresses WHERE addr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$edit_address_id]);
        $edit_address = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Manejar el envío del formulario para actualizar la dirección
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitUpdateAddress'])) {
        $line1 = isset($_POST['addr_line1']) ? cleanInput($_POST['addr_line1']) : '';
        $line2 = isset($_POST['addr_line2']) ? cleanInput($_POST['addr_line2']) : '';
        $city = isset($_POST['addr_city']) ? cleanInput($_POST['addr_city']) : '';
        $state = isset($_POST['addr_state']) ? cleanInput($_POST['addr_state']) : '';
        $postal_code = isset($_POST['addr_postal_code']) ? cleanInput($_POST['addr_postal_code']) : '';
        $country = isset($_POST['addr_country']) ? cleanInput($_POST['addr_country']) : '';

        // Actualizar la dirección en la base de datos
        $sql = "UPDATE pps_user_addresses SET addr_line1 = ?, addr_line2 = ?, addr_city = ?, addr_state = ?, addr_postal_code = ?, addr_country = ? WHERE addr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$line1, $line2, $city, $state, $postal_code, $country, $edit_address_id]);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_addres.php");
        exit;
    }
    ?>

    <h1>Editar Dirección</h1>

    <?php if ($edit_address) : ?>
        <!-- Formulario para editar la dirección -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="edit_address_id" value="<?php echo $edit_address['addr_id']; ?>">
            <label for="addr_line1">Línea 1:</label>
            <input type="text" name="addr_line1" value="<?php echo $edit_address['addr_line1']; ?>" required>
            <br>
            <label for="addr_line2">Línea 2:</label>
            <input type="text" name="addr_line2" value="<?php echo $edit_address['addr_line2']; ?>">
            <br>
            <label for="addr_city">Ciudad:</label>
            <input type="text" name="addr_city" value="<?php echo $edit_address['addr_city']; ?>" required>
            <br>
            <label for="addr_state">Estado:</label>
            <input type="text" name="addr_state" value="<?php echo $edit_address['addr_state']; ?>">
            <br>
            <label for="addr_postal_code">Código Postal:</label>
            <input type="text" name="addr_postal_code" value="<?php echo $edit_address['addr_postal_code']; ?>" required>
            <br>
            <label for="addr_country">País:</label>
            <input type="text" name="addr_country" value="<?php echo $edit_address['addr_country']; ?>" required>
            <br>
            <button type="submit" name="submitUpdateAddress">Actualizar Dirección</button>
        </form>
    <?php else : ?>
        <p>No se pudo encontrar la dirección para editar.</p>
    <?php endif; ?>

</body>

</html>

<?php
// Cerrar la conexión a la base de datos al finalizar
$connection = null;
?>