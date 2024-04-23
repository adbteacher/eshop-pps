<?php
echo '<!DOCTYPE html>';
echo '<html lang="es">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Registro de Usuario</title>';
echo '</head>';
echo '<body>';

echo '<h3>¿Es usted un cliente o una empresa?</h3>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
echo '<label for="UserType">Selecciona el tipo de usuario:</label><br>';
echo '<input type="submit" name="UserType" value="Cliente"> ';
echo '<input type="submit" name="UserType" value="Empresa"><br>';
echo '</form>';

///////////////////////////////////////////////////////////////////////////////
// DB
// Conexión a la base de datos
$DbServername = "localhost";
$DbUsername = "usuario";
$DbPassword = "1234";
$DbName = "eshop";

// Conectar con la base de datos
$Conn = mysqli_connect($DbServername, $DbUsername, $DbPassword, $DbName);

// Verificar la conexión con la base de datos
if (mysqli_connect_error()) {
    exit('Error conecting to the database' . mysqli_connect_error());
}

///////////////////////////////////////////////////////////////////////////////

// Inicializar las variables
$CustomerName = $CustomerSurNames = '';
$CompanyName = $Cif = $CompanyWeb = '';
$Prefix = $PhoneNumber = $Address =  $Email = $ConfirmEmail = $Password = $ConfirmPassword = $VerificationCode ='';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $UserType = isset($_POST['UserType']) ? $_POST['UserType'] : '';

    // Limpiar variable y comprobar tipo de usuario
    if ($UserType === 'Cliente') {
        $UserType = 'customer';
    } elseif ($UserType === 'Empresa') {
        $UserType = 'company';
    } else {
        $UserType = 'customer';
    }

    //////////////////////////////////////////////////////////

    // Obtener los datos específicos del cliente
    if ($UserType == 'customer') {
        echo '<br><br>';
        echo '<h2>Registro de Usuario</h2> ';
        echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        echo '<p> * Nombre: <input type="text" name="CustomerName" maxlength="100" required>';
        echo ' * Apellidos: <input type="text" name="CustomerSurNames" maxlength="200" required><br>';
        echo '<p> * Prefijo: <input type="number" name="Prefix" maxlength="5" required>';
        echo ' * Teléfono: <input type="tel" name="PhoneNumber" maxlength="11" required><br>';
        echo '<p> * Dirección física: <input type="text" name="Address" maxlength="200" required><br>';
        echo '<p> * Correo electrónico: <input type="email" name="Email" maxlength="200" required><br>';
        echo '<p> * Confirmar Correo electrónico: <input type="email" name="ConfirmEmail" maxlength="200" required><br>';
        echo '<p> * Contraseña: <input type="password" name="Password" maxlength="300" required><br>';
        echo '<p> * Confirmar Contraseña: <input type="password" name="ConfirmPassword" maxlength="300" required><br>';
        echo '<br><br><input type="submit" name="register" value="Registrar usuario">';
        echo '</form>';
    }

    // Obtener los datos específicos del vendedor
    elseif ($UserType == 'company') {
        echo '<br><br>';
        echo '<h2>Registro de Empresa</h2> ';
        echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">';
        echo '<p> * Nombre de la empresa: <input type="text" name="CompanyName" maxlength="100" required><br>';
        echo '<p> * CIF: <input type="text" name="Cif" maxlength="12" required><br>';
        echo '<p> * Dirección comercial: <input type="text" name="Address" maxlength="100" required><br>';
        echo '<p> * Prefijo: <input type="number" name="Prefix" maxlength="5" required><br>';
        echo '<p> * Teléfono de contacto: <input type="tel" name="PhoneNumber" maxlength="11" required><br>';
        echo '<p> * Web de tienda: <input type="text" name="Web" maxlength="50" required><br>';
        echo '<p> * Correo electrónico: <input type="email" name="Email" maxlength="200" required><br>';
        echo '<p> * Confirmar Correo electrónico: <input type="email" name="ConfirmEmail" maxlength="200" required><br>';
        echo '<p> * Contraseña: <input type="password" name="Password" maxlength="300" required><br>';
        echo '<p> * Confirmar Contraseña: <input type="password" name="ConfirmPassword" maxlength="300" required><br>';
        echo '<br><br><input type="submit" name="register" value="Registrar empresa">';
        echo '</form>';
    }

    // Verificar si el formulario ha sido enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
        $Prefix = $_POST['Prefix'];
        $PhoneNumber = $_POST['PhoneNumber'];
        $Address = $_POST['Address'];
        $Email = $_POST['Email'];
        $ConfirmEmail = $_POST['ConfirmEmail'];
        $Password = $_POST['Password'];
        $ConfirmPassword = $_POST['ConfirmPassword'];
        $VerificationCode ='';

        
        if ($UserType == 'customer') {
            $CustomerName = $_POST['CustomerName'];
            $CustomerSurNames = $_POST['CustomerSurNames'];
        }

        
        if ($UserType == 'company') {
            $CompanyName = $_POST['CompanyName'];
            $Cif = $_POST['Cif'];
            $CompanyWeb = $_POST['CompanyWeb'];

            // Comprobar CIF
            $PatternCIF = '/^[AB]\d{7}$|^[AB]\d{6}[A-Z]$/i';
            if (!preg_match($PatternCIF, $Cif)){
                die("CIF inválido!");
            }
        }

        // Validar que las contraseñas coincidan
        if ($Password !== $ConfirmPassword) {
            die("Las contraseñas no coinciden.");
        }

        // Validar que las contraseñas cumplen los requisitos mínimos
        // Al menos 8 caracteres
        // Al menos 1 caracter minuscula
        // Al menos 1 caracter mayuscula
        // Al menos un número
        $PatternPassword = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
        if (!preg_match($PatternPassword, $Password)) {
            die("Invalid Password.");
        }

        // Hash de la contraseña
        $Password = password_hash($Password, PASSWORD_DEFAULT);

        // Comprobar si el usuario ya existe en la DB
        //$Sql = $Conn->prepare("SELECT users FROM etc")

        // Generar codigo de verificacion enviado al correo
        //$VerificationCode='patata'

        // Validar que los correos electrónicos coincidan
        if ($Email !== $ConfirmEmail) {
            die("Los correos electrónicos no coinciden.");
        }


        // Variable con fecha y hora
        $DateTime = date('YmdHis');

        // Insertar en base de datos al usuario/cliente
        if ($UserType == 'customer') {
            $Query = $Conn->prepare("INSERT INTO pps_users (usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_name, usu_surnames, usu_prefix, usu_phone, usu_email, usu_password) VALUES ('U', 'U', 'N', ?, ?, ?, ?, ?, ?, ?, ?)");
                $Query->bind_param("sssssssss", $VerificationCode, $DateTime, $CustomerName, $CustomerSurNames, $Prefix, $PhoneNumber, $Email, $Password);
                $Query->execute();
                $Query->close();
            echo "$VerificationCode $DateTime $CustomerName $CustomerSurNames $Prefix $PhoneNumber $Email $Password";
        }

        // Insertar en base de datos al usuario/empresa
        elseif ($UserType == 'company') {
            $Query = $Conn->prepare("INSERT INTO pps_users ( usu_type, usu_rol, usu_status, usu_verification_code, usu_datetime, usu_prefix, usu_phone, usu_email, usu_password, usu_company, usu_cif, usu_web, usu_documents ) VALUES ( 'V', 'V', 'N', $VerificationCode, $DateTime, $Prefix, $PhoneNumber, $Email, $Password, $CompanyName, $Cif, $CompanyWeb, $CompanyDocuments )");
        }

        // Query a la base de datos
        if (mysqli_query($Conn, $Query)) {
          echo "New record created successfully";
        } else {
          echo "Error: " . $Query . "<br>" . mysqli_error($Conn);
        }

        $Conn->close();
    }
}
?>

</body>
</html>