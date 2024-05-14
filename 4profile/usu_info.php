<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['email'])) {
	header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
	exit;
}
require_once '../Database.php';

$user_email = $_SESSION['email'];

// Functions
function cleanInput($input): array|string
{
	$input = trim($input);
	$input = stripslashes($input);
	$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
	$input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
	return $input;
}

// Process the personal information editing form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitPersonalInfo'])) {
	// Retrieve form data
	$Name     = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
	$Surnames = isset($_POST['surnames']) ? cleanInput($_POST['surnames']) : '';
	$Email    = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
	$Phone    = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';

	// Database connection
	$connection = database::LoadDatabase();

	// Update information in the database
	// Prepare the SQL statement for updating user information
	$sql = "UPDATE pps_users SET 
    usu_name = ?,  
    usu_surnames = ?,
    usu_phone = ? 
    WHERE usu_email = ?";

	// Prepare the statement
	$stmt = $connection->prepare($sql);

	// Bind the parameters
	$stmt->bindValue(1, $Name);
	$stmt->bindValue(2, $Surnames);
	$stmt->bindValue(3, $Phone);
	$stmt->bindValue(4, $user_email);

	if ($stmt->execute()) {
		echo "Changes saved successfully.";
	} else {
		echo "Error updating information: " . $connection->errorInfo()[2];
	}
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gestión de información personal</title>
</head>

<body>

	<?php
	include "../nav.php";
	?>

	<h3>Información de usuario:</h3>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<label for="name">Nombre:
			<input type="text" name="name" value="<?php echo $user_email; ?>">
		</label>
		<br>

		<label for="Apellidos">Apellidos:
			<input type="text" name="surnames" value="<?php echo $user_email; ?>">
		</label>
		<br>

		<br>
		<label for="email">Email:
			<input type="email" name="email" value="<?php echo $user_email; ?>" readonly>
		</label>
		<br>

		<label for="phone">Teléfono:
			<input type="text" name="phone" value="">
		</label>
		<br>
		<br>

		<input type="submit" name="submitPersonalInfo" value="Save Changes">
	</form>

</body>

</html>

<?php
// Close the database connection
$connection = null;
?>