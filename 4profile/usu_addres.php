<?php
// Database connection data
$ServerName = "localhost";
$UserName = "root";
$Password = "";
$Database = "qajh438";

// Create connection
$conn = new mysqli($ServerName, $UserName, $Password, $Database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Functions

function cleanInput($input) //Function to clean inputs.
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

    // Query the information of the selected user using prepared statement
    $sql = "SELECT * FROM pps_users WHERE usu_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind the parameter
    $stmt->bind_param("i", $UserId); // "i" indicates that it is an integer (the datatype of $UserId)

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Show the form to edit personal information
        $UserRow = $result->fetch_assoc();
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

    // Update information in the database
    // Prepare the SQL statement for updating user information
    $sql = "UPDATE pps_users SET 
    usu_name = ?, 
    usu_surnames = ?,
    usu_email = ?, 
    usu_phone = ? 
    WHERE usu_id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("sssii", $Name, $Surnames, $Email, $Phone, $UserId);

    if ($stmt->execute()) {
        //$sql = "SELECT * FROM pps_users WHERE usu_id = $UserId";
        //$result = $conn->query($sql);
        // Query the information of the selected user using prepared statement
        $sql = "SELECT * FROM pps_users WHERE usu_id = ?";
        $stmt = $conn->prepare($sql);

        // Bind the parameter
        $stmt->bind_param("i", $UserId); // "i" indicates that it is an integer (the datatype of $UserId)

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Obtén los datos actualizados del perfil
            $UserRow = $result->fetch_assoc();
        } else {
            echo "Error: No se pudo recuperar la información actualizada del perfil";
        }
    } else {
        echo "Error updating information: " . $conn->error;
    }
    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de informacón Personal</title>
</head>

<body>
    <!-- Form to select the user -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="userId">User ID:</label>
        <input type="text" name="userId" value="<?php echo $UserId; ?>">
        <input type="submit" name="submitUser" value="Select">
    </form>

    <!-- Form to display and edit user addresses -->
    <?php if ($UserId !== '') : ?>
        <h3>Direcciones del usuario:</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="userId" value="<?php echo $UserId; ?>">
            <label for="selectAddress">Selecciona una dirección:</label>
            <select name="selectAddress" id="selectAddress">
                <?php
                // Obtener las direcciones del usuario
                $sql = "SELECT * FROM pps_user_addresses WHERE addr_user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $UserId);
                $stmt->execute();
                $result = $stmt->get_result();

                // Mostrar las direcciones en el select
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['addr_id'] . "'>" . $row['addr_line1'] . "</option>";
                }
                ?>
            </select>
            <br>
            <input type="submit" name="editAddress" value="Edit Address">
            <input type="submit" name="addAddress" value="Add New Address">
        </form>

        <br>
        <h3>Editar o Añadir Dirección:</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="userId" value="<?php echo $UserId; ?>">
            <label for="line1">Dirección:</label>
            <input type="text" name="line1">
            <br>
            <label for="line2">Línea 2:</label>
            <input type="text" name="line2">
            <br>
            <label for="city">Ciudad:</label>
            <input type="text" name="city">
            <br>
            <label for="state">Estado:</label>
            <input type="text" name="state">
            <br>
            <label for="postalCode">Código Postal:</label>
            <input type="text" name="postalCode">
            <br>
            <label for="country">País:</label>
            <input type="text" name="country">
            <br>
            <br>
            <input type="submit" name="submitAddress" value="Save Address">
        </form>
    <?php endif; ?>


</body>

</html>

<?php
// Close the database connection
$conn->close();
?>