<?php
require_once 'db.php';

// Functions
function cleanInput($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
    $input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
    return $input;
}

// Variable to store the selected user ID
$UserId = '';

// Process the user selection form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitUser']) || isset($_POST['submitPersonalInfo'])) {
    // Validate and sanitize the user ID
    $UserId = isset($_POST['userId']) ? cleanInput($_POST['userId']) : ''; //

    // Database connection
    $connection = GetDatabaseConnection();

    // Query the information of the selected user using prepared statement
    $sql = "SELECT * FROM pps_users WHERE usu_id = ?";
    $stmt = $connection->prepare($sql);

    // Execute the statement with the user ID parameter
    $stmt->execute([$UserId]);

    // Fetch the result as an associative array
    $UserRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($UserRow) {
        // Show the form to edit personal information
    } else {
        echo "User not found";
    }
}

// Process the personal information editing form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitPersonalInfo'])) {
    // Retrieve form data
    $UserId = isset($_POST['userId']) ? cleanInput($_POST['userId']) : '';
    $Name = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
    $Surnames = isset($_POST['surnames']) ? cleanInput($_POST['surnames']) : '';
    $Email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
    $Phone = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';

    // Database connection
    $connection = GetDatabaseConnection();

    // Update information in the database
    // Prepare the SQL statement for updating user information
    $sql = "UPDATE pps_users SET 
    usu_name = ?,  
    usu_surnames = ?,
    usu_email = ?, 
    usu_phone = ? 
    WHERE usu_id = ?";

    // Prepare the statement
    $stmt = $connection->prepare($sql);

    // Bind the parameters
    $stmt->bindValue(1, $Name);
    $stmt->bindValue(2, $Surnames);
    $stmt->bindValue(3, $Email);
    $stmt->bindValue(4, $Phone);
    $stmt->bindValue(5, $UserId);

    if ($stmt->execute()) {
        // Query the updated information of the selected user using prepared statement
        $sql = "SELECT * FROM pps_users WHERE usu_id = ?";
        $stmt = $connection->prepare($sql);

        // Execute the statement with the user ID parameter
        $stmt->execute([$UserId]);

        // Fetch the result as an associative array
        $UserRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($UserRow) {
            // Get the updated profile data
        } else {
            echo "Error: Could not retrieve updated profile information";
        }
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
    <title>Gestión de información Personal</title>
</head>

<body>
    <!-- Form to select the user -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="userId">User ID:</label>
        <input type="text" name="userId" value="<?php echo $UserId; ?>">
        <input type="submit" name="submitUser" value="Select">
    </form>

    <!-- Form to edit personal information -->
    <?php if (!empty($UserRow)) : ?>
        <h3>Información de usuario:</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="userId" value="<?php echo $UserRow['usu_id']; ?>">
            <label for="name">Nombre:</label>
            <input type="text" name="name" value="<?php echo $UserRow['usu_name']; ?>">
            <br>
            <label for="Apellidos">Apellidos:</label>
            <input type="text" name="surnames" value="<?php echo $UserRow['usu_surnames']; ?>">
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo $UserRow['usu_email']; ?>">
            <br>
            <label for="phone">Teléfono:</label>
            <input type="text" name="phone" value="<?php echo $UserRow['usu_phone']; ?>">
            <br>
            <br>
            <input type="submit" name="submitPersonalInfo" value="Save Changes">
        </form>
    <?php endif; ?>

</body>

</html>

<?php
// Close the database connection
$connection = null;
?>