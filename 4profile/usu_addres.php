<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direcciones de Usuario</title>
    <link rel="stylesheet" href="usu_address_styles.css">
</head>

<body>
    <?php
    require_once '../Database.php';


    // Provisional hasta tener la sesión del login.
    // TODO HACER CSRF TOKEN
    $user_email = $_SESSION['email'];

    // Función de limpieza:
    function cleanInput($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
        $input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
        return $input;
    }

    // Función para obtener las direcciones del usuario
    function getUserAddresses($user_email)
    {
        $connection = database::LoadDatabase();
        $sql = "SELECT * FROM pps_addresses_per_user WHERE usu_email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$user_email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener las direcciones del usuario
    $addresses = getUserAddresses($user_email);

    // Manejar el envío del formulario para modificar la dirección principal
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitMainAddress'])) {
        $connection = database::LoadDatabase();

        $main_address_id = isset($_POST['main_address_id']) ? cleanInput($_POST['main_address_id']) : '';

        // Marcar todas las direcciones del usuario como no principales
        $sql = "UPDATE pps_addresses_per_user SET adr_is_main = 0 WHERE usu_email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$user_email]);

        // Marcar la dirección seleccionada como principal
        $sql = "UPDATE pps_addresses_per_user SET adr_is_main = 1 WHERE adr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$main_address_id]);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_addres.php");
        exit;
    }

    // Manejar el envío del formulario para eliminar una dirección
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitDeleteAddress'])) {
        $connection = database::LoadDatabase();

        $address_id = isset($_POST['delete_address_id']) ? cleanInput($_POST['delete_address_id']) : '';

        // Eliminar la dirección de la base de datos
        $sql = "DELETE FROM pps_addresses_per_user WHERE adr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$address_id]);

        // Redireccionar a la página para evitar el reenvío del formulario
        header("Location: usu_addres.php");
        exit;
    }
    ?>

    <h1>Direcciones de Usuario</h1>

    <!-- Mostrar las direcciones existentes del usuario -->
    <div class="address-container">
        <?php foreach ($addresses as $address) : ?>
            <div class="address">
                <?php echo $address['adr_line1']; ?>
                <?php echo $address['adr_line2'] ? ', ' . $address['adr_line2'] : ''; ?>
                <br>
                <?php echo $address['adr_city'] . ', ' . $address['adr_state'] . ' ' . $address['adr_postal_code']; ?>
                <br>
                <?php echo $address['adr_country']; ?>
                <?php if ($address['adr_is_main']) : ?>
                    <strong class="custom-strong">(Principal)</strong>
                <?php endif; ?>
                <!-- Contenedor para los botones -->
                <div class="button-container">
                    <?php if (!$address['adr_is_main']) : ?>
                        <!-- Botón para hacer esta dirección como principal -->
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="main_address_id" value="<?php echo $address['adr_id']; ?>">
                            <button type="submit" name="submitMainAddress">Principal</button>
                        </form>
                    <?php endif; ?>
                    <!-- Botón para editar la dirección -->
                    <form method="post" action="usu_address_edit.php">
                        <input type="hidden" name="edit_address_id" value="<?php echo $address['adr_id']; ?>">
                        <button type="submit" name="submitEditAddress">Editar</button>
                    </form>
                    <!-- Botón para eliminar la dirección -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="delete_address_id" value="<?php echo $address['adr_id']; ?>">
                        <button type="submit" name="submitDeleteAddress">Eliminar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Botón para crear una nueva dirección -->
    <form method="post" action="usu_new_address.php">
        <button type="submit" class="custom-button">Crear Dirección</button>
    </form>

</body>

</html>