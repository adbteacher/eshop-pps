<?php
session_start();

require_once '../autoload.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['UserEmail'])) {
    header("Location: ../1login/login.php");
    exit;
}

// Obtener el ID del usuario
$user_id = $_SESSION['UserID'];

$connection = database::LoadDatabase();

// Función de limpieza:
function cleanInput($input): array|string
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "\\", "¿"], '', $input);
    $input = str_replace(['=', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
    return $input;
}

// Cifrar contraseña
function hashPassword($password)
{
    // Aplicar el cifrado SHA256
    $hashed_password = hash('sha256', $password);
    return $hashed_password;
}

// Generar un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Funciones para generar el Key de cifrado (de manera aleatoria).
function getEncryptionKey()
{
    // Verificar si la clave ya está definida en la sesión
    if (!isset($_SESSION['encryption_key'])) {
        // Generar una clave aleatoria de 32 bytes (256 bits) y almacenarla en la sesión
        $_SESSION['encryption_key'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['encryption_key'];
}

//  Función para cifrar el ID (Ya que se envía por POST y es inseguro).
function encryptId($id): bool|string
{
    $key = getEncryptionKey();
    $iv  = substr(hash('sha256', $key), 0, 16);
    return openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv);
}

//  Función para descifrar el ID (Ya que se envía por POST y es inseguro).
function decryptId($encryptedId): bool|string
{
    $key = getEncryptionKey();
    $iv  = substr(hash('sha256', $key), 0, 16);
    return openssl_decrypt($encryptedId, 'AES-256-CBC', $key, 0, $iv);
}

// Función para verificar si el ID del método de pago pertenece al usuario
function validatePaymentMethodOwnership($pmu_id, $user_id): bool
{
    $connection = database::LoadDatabase();
    $sql        = "SELECT COUNT(*) AS count FROM pps_payment_methods_per_user WHERE pmu_id = ? AND pmu_user = ?";
    $stmt       = $connection->prepare($sql);
    $stmt->execute([$pmu_id, $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

// Función para comprobar si es el primer método de pago del usuario
function isFirstPaymentMethod($user_id): bool
{
    $connection = database::LoadDatabase();
    $sql        = "SELECT COUNT(*) AS count FROM pps_payment_methods_per_user WHERE pmu_user = ?";
    $stmt       = $connection->prepare($sql);
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] == 0;
}

// Manejar el envío del formulario para agregar un método de pago
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitAddPaymentMethod'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Token CSRF inválido.';
        header("Location: payment_methods.php");
        exit;
    }

    // Verificar si es el primer método de pago
    $isFirstMethod = isFirstPaymentMethod($user_id);

    if ($isFirstMethod) {
        $pmu_is_main = 1;
    } else {
        $pmu_is_main = 0;
    }

    $payment_method  = cleanInput($_POST['pmu_payment_method']);
    $card_number     = isset($_POST['pmu_card_number']) ? cleanInput($_POST['pmu_card_number']) : '';
    $cve_number      = isset($_POST['pmu_cve_number']) ? cleanInput($_POST['pmu_cve_number']) : '';
    $cardholder      = isset($_POST['pmu_cardholder']) ? cleanInput($_POST['pmu_cardholder']) : '';
    $expiration_date = isset($_POST['pmu_expiration_date']) ? cleanInput($_POST['pmu_expiration_date']) : '';
    $online_account  = isset($_POST['pmu_online_account']) ? cleanInput($_POST['pmu_online_account']) : '';
    $online_password = isset($_POST['pmu_online_password']) ? cleanInput($_POST['pmu_online_password']) : '';
    $online_password = password_hash($online_password, PASSWORD_DEFAULT);

    // Validar los campos según el método de pago
    if ($payment_method == "1") {
        // Validar campos de tarjeta de crédito
        if (empty($card_number) || empty($cve_number) || empty($cardholder) || empty($expiration_date)) {
            $_SESSION['error_message'] = 'Por favor, complete todos los campos de la tarjeta de crédito.';
            header("Location: payment_methods.php");
            exit;
        }

        // Validar formato de tarjeta de crédito
        if (!preg_match('/^[0-9]{16}$/', $card_number)) {
            $_SESSION['error_message'] = 'Número de tarjeta inválido.';
            header("Location: payment_methods.php");
            exit;
        }

        // Validar formato de CVV
        if (!preg_match('/^[0-9]{3}$/', $cve_number)) {
            $_SESSION['error_message'] = 'Número CVV inválido.';
            header("Location: payment_methods.php");
            exit;
        }

        // Validar formato de fecha de expiración
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiration_date)) {
            $_SESSION['error_message'] = 'Fecha de expiración inválida.';
            header("Location: payment_methods.php");
            exit;
        }

        // Valores vacíos (conflicto con la BBDD de producción)
        $online_account  = 'A';
        $online_password = 'A';
        $account_number  = 0;
        $swift           = 'A';



        // Insertar método de pago de tarjeta de crédito
        $sql  = "INSERT INTO pps_payment_methods_per_user (pmu_payment_method, pmu_user, pmu_account_number, pmu_swift, pmu_card_number, pmu_cve_number, pmu_cardholder, pmu_expiration_date, pmu_online_account, pmu_online_password, pmu_is_main) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([$payment_method, $user_id, $account_number, $swift, $card_number, $cve_number, $cardholder, $expiration_date, $online_account, $online_password, $pmu_is_main])) {
            $_SESSION['success_message'] = 'Método de pago agregado exitosamente.';
        } else {
            $_SESSION['error_message'] = 'Hubo un error al agregar el método de pago.';
        }
    } elseif ($payment_method == "2") {
        // Validar campos de PayPal
        if (empty($online_account) || empty($online_password)) {
            $_SESSION['error_message'] = 'Por favor, complete todos los campos de PayPal.';
            header("Location: payment_methods.php");
            exit;
        }

        // Validar formato de correo electrónico
        if (!filter_var($online_account, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Correo electrónico de PayPal inválido.';
            header("Location: payment_methods.php");
            exit;
        }

        // Valores vacíos (conflicto con la BBDD de producción)
        $account_number  =  'A';
        $swift           =  'A';
        $card_number     =  0;
        $cve_number      =  0;
        $cardholder      =  'A';
        $expiration_date =  'A';

        // Insertar método de pago PayPal
        $sql  = "INSERT INTO pps_payment_methods_per_user (pmu_payment_method, pmu_user, pmu_account_number, pmu_swift, pmu_card_number, pmu_cve_number, pmu_cardholder, pmu_expiration_date, pmu_online_account, pmu_online_password, pmu_is_main) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        if ($stmt->execute([$payment_method, $user_id, $account_number, $swift, $card_number, $cve_number, $cardholder, $expiration_date, $online_account, $online_password, $pmu_is_main])) {
            $_SESSION['success_message'] = "Método de pago agregado exitosamente.";
        } else {
            $_SESSION['error_message'] = 'Hubo un error al agregar el método de pago.';
        }
    } else {
        $_SESSION['error_message'] = 'Método de pago inválido.';
    }

    header("Location: payment_methods.php");
    exit;
}

// Manejar el envío del formulario para eliminar un método de pago
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitDeletePaymentMethod'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Token CSRF inválido.';
        header("Location: payment_methods.php");
        exit;
    }

    $encrypted_pmu_id = cleanInput($_POST['pmu_id']);
    $pmu_id           = decryptId($encrypted_pmu_id);

    // Verificar si el ID del método de pago pertenece al usuario
    if (!validatePaymentMethodOwnership($pmu_id, $user_id)) {
        $_SESSION['error_message'] = 'El método de pago que intentas eliminar no pertenece a tu cuenta.';
        header("Location: payment_methods.php");
        exit;
    }


    $sql  = "DELETE FROM pps_payment_methods_per_user WHERE pmu_id = ? AND pmu_user = ?";
    $stmt = $connection->prepare($sql);
    if ($stmt->execute([$pmu_id, $user_id])) {
        $_SESSION['success_message'] = 'Método de pago eliminado exitosamente.';
    } else {
        $_SESSION['error_message'] = 'Hubo un error al eliminar el método de pago.';
    }
    header("Location: payment_methods.php");
    exit;
}

// Manejar el envío del formulario para editar un método de pago
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitEditPaymentMethod'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Token CSRF inválido.';
        header("Location: payment_methods.php");
        exit;
    }

    $encrypted_pmu_id = cleanInput($_POST['pmu_id']);
    $pmu_id           = decryptId($encrypted_pmu_id);

    // Verificar si el ID del método de pago pertenece al usuario
    if (!validatePaymentMethodOwnership($pmu_id, $user_id)) {
        $_SESSION['error_message'] = 'El método de pago que intentas editar no pertenece a tu cuenta.';
        header("Location: payment_methods.php");
        exit;
    }

    $_SESSION['edit_pmu_id'] = $pmu_id; // Almacenar el ID en la sesión
    header("Location: edit_payment_method.php");
    exit;
}

// Manejar el envío del formulario para hacer principal un método de pago
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitSetPrimaryPaymentMethod'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = 'Token CSRF inválido.';
        header("Location: payment_methods.php");
        exit;
    }

    $encrypted_pmu_id = cleanInput($_POST['pmu_id']);
    $pmu_id           = decryptId($encrypted_pmu_id);

    // Verificar si el ID del método de pago pertenece al usuario
    if (!validatePaymentMethodOwnership($pmu_id, $user_id)) {
        $_SESSION['error_message'] = 'El método de pago que intentas establecer como principal no pertenece a tu cuenta.';
        header("Location: payment_methods.php");
        exit;
    }

    // Marcar el método de pago como principal
    $sqlUpdate  = "UPDATE pps_payment_methods_per_user SET pmu_is_main = 0 WHERE pmu_user = ?";
    $stmtUpdate = $connection->prepare($sqlUpdate);
    $stmtUpdate->execute([$user_id]);

    $sqlSetPrimary  = "UPDATE pps_payment_methods_per_user SET pmu_is_main = 1 WHERE pmu_id = ?";
    $stmtSetPrimary = $connection->prepare($sqlSetPrimary);
    if ($stmtSetPrimary->execute([$pmu_id])) {
        $_SESSION['success_message'] = 'Método de pago establecido como principal exitosamente.';
    } else {
        $_SESSION['error_message'] = 'Hubo un error al establecer el método de pago como principal.';
    }

    header("Location: payment_methods.php");
    exit;
}

// Obtener todos los métodos de pago del usuario
$sql  = "SELECT * FROM pps_payment_methods_per_user WHERE pmu_user = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$user_id]);
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métodos de Pago</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .container {
            padding: 20px;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("¿Está seguro de que desea eliminar este método de pago?");
        }

        function toggleFields() {
            var paymentMethod = document.getElementById("pmu_payment_method").value;
            var creditCardFields = document.getElementById("credit-card-fields");
            var paypalFields = document.getElementById("paypal-fields");

            // Set all fields to not required initially
            var allFields = document.querySelectorAll(".payment-field");
            allFields.forEach(function(field) {
                field.required = false;
                field.value = ''; // Clear field values
            });

            if (paymentMethod == "1") { // Tarjeta de Crédito
                creditCardFields.style.display = "block";
                paypalFields.style.display = "none";

                // Set credit card fields to required
                document.getElementById("pmu_card_number").required = true;
                document.getElementById("pmu_cve_number").required = true;
                document.getElementById("pmu_cardholder").required = true;
                document.getElementById("pmu_expiration_date").required = true;
            } else if (paymentMethod == "2") { // PayPal
                creditCardFields.style.display = "none";
                paypalFields.style.display = "block";

                // Set PayPal fields to required
                document.getElementById("pmu_online_account").required = true;
                document.getElementById("pmu_online_password").required = true;
            }
        }

        window.onload = function() {
            toggleFields(); // Inicializar la visibilidad de los campos al cargar la página
        };
    </script>
</head>

<body>
    <?php include "../nav.php"; ?>

    <div class="container">
        <h1 class="text-center mb-4">Métodos de Pago</h1>

        <!-- Mensajes de éxito y error -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para agregar un nuevo método de pago -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label for="pmu_payment_method" class="form-label">Método de Pago:</label>
                <select id="pmu_payment_method" name="pmu_payment_method" class="form-select" onchange="toggleFields()" required>
                    <option value="">Seleccione...</option>
                    <option value="1">Tarjeta de Crédito</option>
                    <option value="2">PayPal</option>
                </select>
            </div>

            <div id="credit-card-fields" style="display:none;">
                <div class="mb-3">
                    <label for="pmu_card_number" class="form-label">Número de Tarjeta:</label>
                    <input type="text" id="pmu_card_number" name="pmu_card_number" class="form-control payment-field" maxlength="16" placeholder="1234 5678 9012 3456">
                </div>
                <div class="mb-3">
                    <label for="pmu_cve_number" class="form-label">CVV:</label>
                    <input type="text" id="pmu_cve_number" name="pmu_cve_number" class="form-control payment-field" maxlength="3" placeholder="123">
                </div>
                <div class="mb-3">
                    <label for="pmu_cardholder" class="form-label">Nombre del Titular:</label>
                    <input type="text" id="pmu_cardholder" name="pmu_cardholder" class="form-control payment-field" placeholder="Nombre del Titular">
                </div>
                <div class="mb-3">
                    <label for="pmu_expiration_date" class="form-label">Fecha de Expiración (MM/AA):</label>
                    <input type="text" id="pmu_expiration_date" name="pmu_expiration_date" class="form-control payment-field" maxlength="5" placeholder="MM/AA">
                </div>
            </div>

            <div id="paypal-fields" style="display:none;">
                <div class="mb-3">
                    <label for="pmu_online_account" class="form-label">Cuenta de PayPal (email):</label>
                    <input type="email" id="pmu_online_account" name="pmu_online_account" class="form-control payment-field" placeholder="example@example.com">
                </div>
                <div class="mb-3">
                    <label for="pmu_online_password" class="form-label">Contraseña de PayPal:</label>
                    <input type="password" id="pmu_online_password" name="pmu_online_password" class="form-control payment-field" placeholder="Contraseña de PayPal">
                </div>
            </div>

            <button type="submit" name="submitAddPaymentMethod" class="btn btn-primary">Agregar Método de Pago</button>
        </form>


        <h2 class="text-center mt-4">Métodos de Pago Guardados</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Detalles</th>
                    <th>Principal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payment_methods as $method) : ?>
                    <tr>
                        <td><?php echo ($method['pmu_payment_method'] == 1) ? 'Tarjeta de Crédito' : 'PayPal'; ?></td>
                        <td>
                            <?php if ($method['pmu_payment_method'] == 1) : ?>
                                Número de Tarjeta: <?php echo substr($method['pmu_card_number'], 0, 3) . '****' . substr($method['pmu_card_number'], -2); ?><br>
                                Nombre del Titular: <?php echo $method['pmu_cardholder']; ?><br>
                                Fecha de Expiración: <?php echo $method['pmu_expiration_date']; ?>
                            <?php else : ?>
                                Cuenta de PayPal: <?php echo $method['pmu_online_account']; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo ($method['pmu_is_main'] == 1) ? 'Sí' : 'No'; ?>
                        </td>
                        <td>
                            <!-- Verificar si el método de pago no es principal -->
                            <?php if ($method['pmu_is_main'] != 1) : ?>
                                <!-- Verificar si pmu_is_main está establecido en 0 o 1 -->
                                <?php if (isset($method['pmu_is_main']) && ($method['pmu_is_main'] == 0 || $method['pmu_is_main'] == 1)) : ?>
                                    <!-- Formulario para hacer principal -->
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="pmu_id" value="<?php echo encryptId($method['pmu_id']); ?>">
                                        <button type="submit" name="submitSetPrimaryPaymentMethod" class="btn btn-info">Hacer Principal</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="pmu_id" value="<?php echo encryptId($method['pmu_id']); ?>">
                                <button type="submit" name="submitEditPaymentMethod" class="btn btn-warning">Editar</button>
                            </form>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="d-inline" onsubmit="return confirmDelete();">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="pmu_id" value="<?php echo encryptId($method['pmu_id']); ?>">
                                <button type="submit" name="submitDeletePaymentMethod" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include "../footer.php"; ?>
</body>