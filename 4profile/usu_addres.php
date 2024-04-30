<?php
require_once 'db.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Redireccionar al usuario a la página de inicio de sesión si no está autenticado
    header("Location: ../1login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Función para obtener las direcciones del usuario
function getUserAddresses($user_id)
{
    $connection = GetDatabaseConnection();
    $sql = "SELECT * FROM pps_user_addresses WHERE addr_user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener las direcciones del usuario
$addresses = getUserAddresses($user_id);

// Manejar el envío del formulario para añadir una nueva dirección
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitNewAddress'])) {
    $line1 = $_POST['addr_line1'];
    $line2 = $_POST['addr_line2'];
    $city = $_POST['addr_city'];
    $state = $_POST['addr_state'];
    $postal_code = $_POST['addr_postal_code'];
    $country = $_POST['addr_country'];

    // Insertar la nueva dirección en la base de datos
    $sql = "INSERT INTO pps_user_addresses (addr_user_id, addr_line1, addr_line2, addr_city, addr_state, addr_postal_code, addr_country) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$user_id, $line1, $line2, $city, $state, $postal_code, $country]);

    // Redireccionar a la página para evitar el reenvío del formulario
    header("Location: addresses.php");
    exit;
}

// Manejar el envío del formulario para modificar la dirección principal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitMainAddress'])) {
    $main_address_id = $_POST['main_address_id'];

    // Marcar todas las direcciones del usuario como no principales
    $sql = "UPDATE pps_user_addresses SET addr_is_main = 0 WHERE addr_user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$user_id]);

    // Marcar la dirección seleccionada como principal
    $sql = "UPDATE pps_user_addresses SET addr_is_main = 1 WHERE addr_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$main_address_id]);

    // Redireccionar a la página para evitar el reenvío del formulario
    header("Location: addresses.php");
    exit;
}

// Manejar el envío del formulario para eliminar una dirección
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitDeleteAddress'])) {
    $address_id = $_POST['delete_address_id'];

    // Eliminar la dirección de la base de datos
    $sql = "DELETE FROM pps_user_addresses WHERE addr_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$address_id]);

    // Redireccionar a la página para evitar el reenvío del formulario
    header("Location: addresses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direcciones de Usuario</title>
</head>

<body>
    <h1>Direcciones de Usuario</h1>

    <!-- Mostrar las direcciones existentes del usuario -->
    <h2>Direcciones Actuales:</h2>
    <ul>
        <?php foreach ($addresses as $address) : ?>
            <li>
                <?php echo $address['addr_line1']; ?>
                <?php echo $address['addr_line2'] ? ', ' . $address['addr_line2'] : ''; ?>
                <br>
                <?php echo $address['addr_city'] . ', ' . $address['addr_state'] . ' ' . $address['addr_postal_code']; ?>
                <br>
                <?php echo $address['addr_country']; ?>
                <?php if ($address['addr_is_main']) : ?>
                    <strong>(Principal)</strong>
                <?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="delete_address_id" value="<?php echo $address['addr_id']; ?>">
                    <button type="submit" name="submitDeleteAddress">Eliminar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Formulario para añadir una nueva dirección -->
    <h2>Añadir Nueva Dirección:</h2>
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

    <!-- Formulario para seleccionar la dirección principal -->
    <h2>Seleccionar Dirección Principal:</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <select name="main_address_id">
            <?php foreach ($addresses as $address) : ?>
                <option value="<?php echo $address['addr_id']; ?>"><?php echo $address['addr_line1']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="submitMainAddress">Seleccionar Principal</button>
    </form>
</body>

</html>

<?php
// Cerrar la conexión a la base de datos al finalizar
$connection = null;
?>