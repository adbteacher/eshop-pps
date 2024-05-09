<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dirección</title>
</head>

<body>
    <?php
    require_once '../Database.php';

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
    $edit_adress = null;
    if (!empty($edit_address_id)) {
        $connection = database::LoadDatabase();
        $sql = "SELECT * FROM pps_addresses_per_user WHERE adr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$edit_address_id]);
        $edit_adress = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Manejar el envío del formulario para actualizar la dirección
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitUpdateadress'])) {
        $line1 = isset($_POST['adr_line1']) ? cleanInput($_POST['adr_line1']) : '';
        $line2 = isset($_POST['adr_line2']) ? cleanInput($_POST['adr_line2']) : '';
        $city = isset($_POST['adr_city']) ? cleanInput($_POST['adr_city']) : '';
        $state = isset($_POST['adr_state']) ? cleanInput($_POST['adr_state']) : '';
        $postal_code = isset($_POST['adr_postal_code']) ? cleanInput($_POST['adr_postal_code']) : '';
        $country = isset($_POST['adr_country']) ? cleanInput($_POST['adr_country']) : '';

        // Actualizar la dirección en la base de datos
        $sql = "UPDATE pps_addresses_per_user SET adr_line1 = ?, adr_line2 = ?, adr_city = ?, adr_state = ?, adr_postal_code = ?, adr_country = ? WHERE adr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$line1, $line2, $city, $state, $postal_code, $country, $edit_address_id]);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_adres.php");
        exit;
    }
    ?>

    <h1>Editar Dirección</h1>

    <?php if ($edit_adress) : ?>
        <!-- Formulario para editar la dirección -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="edit_address_id" value="<?php echo $edit_adress['adr_id']; ?>">
            <label for="adr_line1">Línea 1:</label>
            <input type="text" name="adr_line1" value="<?php echo $edit_adress['adr_line1']; ?>" required>
            <br>
            <label for="adr_line2">Línea 2:</label>
            <input type="text" name="adr_line2" value="<?php echo $edit_adress['adr_line2']; ?>">
            <br>
            <label for="adr_city">Ciudad:</label>
            <input type="text" name="adr_city" value="<?php echo $edit_adress['adr_city']; ?>" required>
            <br>
            <label for="adr_state">Estado:</label>
            <input type="text" name="adr_state" value="<?php echo $edit_adress['adr_state']; ?>">
            <br>
            <label for="adr_postal_code">Código Postal:</label>
            <input type="text" name="adr_postal_code" value="<?php echo $edit_adress['adr_postal_code']; ?>" required>
            <br>
            <label for="adr_country">País:</label>
            <input type="text" name="adr_country" value="<?php echo $edit_adress['adr_country']; ?>" required>
            <br>
            <button type="submit" name="submitUpdateadress">Actualizar Dirección</button>
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