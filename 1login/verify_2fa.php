<?php
// Include necessary files for additional functions and autoload
require_once 'funciones.php';
require_once '../autoload.php';

// Use the TwoFactorAuth library from RobThree
use RobThree\Auth\TwoFactorAuth;

// Start the session
	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}
// Add security headers to the HTTP response
AddSecurityHeaders();

$error             = '';
$additionalMessage = '';

// If the user's ID is not set in the session, redirect them to the login page
if (!isset($_SESSION['TempUserID'])) // Use TempUserID to check
{
    RedirectUnauthorizedUser();
}

// Get the user's ID from the session
$UserId = $_SESSION['TempUserID'];

// Initialize 2FA attempts counter in the session if not already set
if (!isset($_SESSION['2fa_attempts']))
{
    $_SESSION['2fa_attempts'] = 0;
}

// Function to count failed 2FA attempts
function CountFailedAttempts($UserId)
{
    $Connection = GetDatabaseConnection();
    $Query      = $Connection->prepare("
        SELECT COUNT(*) AS failed_attempts 
        FROM pps_logs_2fa 
        WHERE lfa_user = ? AND lfa_was_successful = 0 AND lfa_datetime > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $Query->execute([$UserId]);
    $Result = $Query->fetch(PDO::FETCH_ASSOC);
    return $Result ? (int)$Result['failed_attempts'] : 0;
}

// Check if the user is blocked
function IsUserBlocked($UserId, $blockDuration = 10, $maxAttempts = 5)
{
    $failedAttempts = CountFailedAttempts($UserId);
    return $failedAttempts >= $maxAttempts;
}

// Function to log 2FA attempts
function Log2FAAttempt($UserId, $Ip, $wasSuccessful)
{
    $Connection = GetDatabaseConnection();
    $Query      = $Connection->prepare("
        INSERT INTO pps_logs_2fa (lfa_user, lfa_ip, lfa_was_successful, lfa_datetime) 
        VALUES (?, ?, ?, NOW())
    ");
    $Query->execute([$UserId, $Ip, $wasSuccessful ? 1 : 0]);
}

$blocked = IsUserBlocked($UserId);

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$blocked)
{
    usleep(500000); // "Está pensando"
    // Validate the CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
    {
        $error = "Error, vuelva a intentarlo más tarde.";
        error_log("Error en la validación CSRF para el usuario con ID: $UserId");
        // Regenerate the CSRF token after a failed attempt
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    else
    {
        // Sanitize and validate the 2FA code input
        $Code2FA = SanitizeInput($_POST['code']);
        if (!preg_match('/^\d{6}$/', $Code2FA))
        {
            $error = "Formato de código 2FA incorrecto.";
        }
        else
        {
            // Initialize the TwoFactorAuth object
            $Tfa = new TwoFactorAuth();
            // Get the database connection
            $Connection = GetDatabaseConnection();

            // Prepare and execute the query to retrieve the user's 2FA secret
            $Query = $Connection->prepare("SELECT usu_2fa FROM pps_users WHERE usu_id = ?");
            $Query->execute([$UserId]);
            $Result = $Query->fetch(PDO::FETCH_ASSOC);

            // Check if the secret was retrieved successfully
            if (!$Result || empty($Result['usu_2fa']))
            {
                $error = "No se pudo recuperar el secreto 2FA.";
                error_log("No se pudo recuperar el secreto 2FA para el usuario con ID: $UserId");
            }
            else
            {
                $Secret = $Result['usu_2fa'];
                // Verify the 2FA code
                if ($Tfa->verifyCode($Secret, $Code2FA))
                {
                    // Log successful attempt
                    Log2FAAttempt($UserId, $_SERVER['REMOTE_ADDR'], true);
                    // Regenerate the CSRF token and reset 2FA attempts counter
                    $_SESSION['csrf_token']   = bin2hex(random_bytes(32));
                    $_SESSION['2fa_attempts'] = 0;

                    // Move session variables from Temp to permanent
                    $_SESSION['UserID'] = $_SESSION['TempUserID'];
                    unset($_SESSION['TempUserID']);

                    // Retrieve the user details to set additional session variables
                    $User = GetUserById($UserId);
                    $_SESSION['UserName'] = $User['usu_name'];
                    $_SESSION['UserRol'] = $User['usu_rol'];

                    // Redirect to the products page upon successful verification
                    header('Location: ../10products/products.php');
                    exit;
                }
                else
                {
                    // Log failed attempt
                    Log2FAAttempt($UserId, $_SERVER['REMOTE_ADDR'], false);
                    // Delay to mitigate brute force attacks and increment the 2FA attempts counter
                    sleep(1);
                    $_SESSION['2fa_attempts']++;
                    $error = "Código 2FA incorrecto.";
                    // Display additional message if the number of attempts is 2 or more
                    if ($_SESSION['2fa_attempts'] >= 2)
                    {
                        $additionalMessage = "Si ha perdido el código, contacte con soporte.";
                    }
                    // Regenerate the CSRF token after a failed attempt
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            }
        }
    }
}

if ($blocked)
{
    $error             = "Demasiados intentos fallidos. Inténtelo de nuevo en 10 minutos.";
    $additionalMessage = "Si ha perdido el código, contacte con soporte.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .spinner-border {
            display: none;
            width: 1.5rem;
            height: 1.5rem;
            border-width: 0.2em;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php if (!$blocked): ?>
            // Set focus to the 2FA code input field when the page loads
            document.getElementById('code').focus();
            <?php endif; ?>
            // Show spinner and disable the verify button on form submit
            document.querySelector("form").addEventListener("submit", function () {
                var verifyButton = document.querySelector(".btn-primary");
                var spinner = document.querySelector(".spinner-border");
                verifyButton.disabled = true;
                spinner.style.display = "inline-block";
            });
        });
    </script>
</head>
<body>
<div class="form-box">
    <h1>Verificar 2FA</h1>
    <form method="post">
        <!-- Include the CSRF token as a hidden input -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="message-container">
            <!-- Display the error message if it's not empty -->
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
                <!-- Display additional message if it exists -->
                <?php if (!empty($additionalMessage)): ?>
                    <div class="info"><?php echo $additionalMessage; ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if (!$blocked): ?>
            <div class="form-group">
                <!-- Input field for the 2FA code with validation pattern -->
                <input type="text" name="code" id="code" class="form-control" placeholder="Ingrese su código 2FA" required pattern="\d{6}" title="El código debe ser de 6 dígitos" maxlength="6">
            </div>
            <div class="button-group">
                <!-- Verify button with a spinner -->
                <button type="submit" class="btn btn-primary">
                    Verificar
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </button>
            </div>
        <?php endif; ?>
        <!-- Link to return to the login page -->
        <br>
        <a href="login.php" class="btn btn-secondary">Volver</a>
    </form>
</div>
</body>
</html>
