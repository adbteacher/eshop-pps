<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dirección</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .container {
            padding: 20px;
        }
    </style>
</head>

<body>
    <?php
    require_once '../Database.php';
    include "../nav.php";
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
        $connection = database::LoadDatabase();
        $sql = "SELECT * FROM pps_addresses_per_user WHERE adr_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$edit_address_id]);
        $edit_address = $stmt->fetch(PDO::FETCH_ASSOC);
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
        header("Location: usu_addres.php");
        exit;
    }
    ?>

    <div class="container">
        <!-- Formulario para editar la dirección -->
        <h1 class="text-center mb-4">Editar Dirección</h1>

        <?php if ($edit_address) : ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="edit_address_id" value="<?php echo $edit_address['adr_id']; ?>">
                <div class="mb-3">
                    <label for="adr_line1" class="form-label">Calle 1:</label>
                    <input type="text" name="adr_line1" value="<?php echo $edit_address['adr_line1']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="adr_line2" class="form-label">Calle 2:</label>
                    <input type="text" name="adr_line2" value="<?php echo $edit_address['adr_line2']; ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="adr_country" class="form-label">País:</label>
                    <input type="text" name="adr_country" value="<?php echo $edit_address['adr_country']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="adr_state" class="form-label">Provincia:</label>
                    <input type="text" name="adr_state" value="<?php echo $edit_address['adr_state']; ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="adr_city" class="form-label">Ciudad:</label>
                    <input type="text" name="adr_city" value="<?php echo $edit_address['adr_city']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="adr_postal_code" class="form-label">Código Postal:</label>
                    <input type="text" name="adr_postal_code" value="<?php echo $edit_address['adr_postal_code']; ?>" class="form-control" required>
                </div>
                <button type="submit" name="submitUpdateadress" class="btn btn-primary">Actualizar Dirección</button>
            </form>
        <?php else : ?>
            <p class="text-danger">No se pudo encontrar la dirección para editar.</p>
        <?php endif; ?>
    </div>

</body>

</html>

<?php
// Cerrar la conexión a la base de datos al finalizar
$connection = null;
?>