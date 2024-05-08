<?php
    include("../Database.php");

?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Método de Pago</title>
    <style>
        .form-row {
            margin-bottom: 15px;
        }
        .form-row label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <h1 class="Titles">2. Seleccione su método de Pago.</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <p>Pago Seguro por tarjeta de Crédito o Débito</p>
        <button type="submit" name="metodo_pago" value="tarjeta_credito">Tarjeta de Crédito</button>
        <button type="submit" name="metodo_pago" value="paypal">PayPal</button><p>
        

        <label for="nombre">Titular de la Tarjeta:</label><p>
        <input type="text" id="nombre" name="nombre" maxlength="50" required><br><br>
        
        <label for="Credit_Number">Número de la Tarjeta:</label><p>
        <input type="text" id="Credit_Number" name="Credit_Number" pattern="[0-9]{16}" title="Debe contener exactamente 16 dígitos" maxlength="16" required><br><br>
        
        <div class="form-row">
            <label for="fecha_vencimiento">Fecha de Vencimiento (MM/AA):</label>
            <input type="text" id="fecha_vencimiento" name="fecha_vencimiento" pattern="^(0[1-9]|1[0-2])\/[0-9]{2}$" placeholder="MM/AA" maxlength="5" required>
        </div>
        
        <div class="form-row">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" pattern="[0-9]{3}" title="Debe contener exactamente tres números" maxlength="3" required>
        </div>
        
        <input type="submit" value="Pagar">
    </form>

    <?php
    // Verifica si se ha enviado el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recupera los datos del formulario
        $nombre = $_POST["nombre"];
        $metodo_pago = $_POST["metodo_pago"];
        $Credit_Number = $_POST["Credit_Number"];
        $fecha_vencimiento = $_POST["fecha_vencimiento"];
        $cvv = $_POST["cvv"];

        // Procesa los datos del pago
        // Aquí podrías realizar operaciones como conectar a una pasarela de pago, registrar la transacción, etc.

        // Muestra un mensaje de confirmación
        echo "<h2>Procesando Pago</h2>";
        echo "<p>¡Hola, $nombre!</p>";
        echo "<p>Has seleccionado pagar usando $metodo_pago.</p>";
        echo "<p>Número de Tarjeta: $Credit_Number</p>";
        echo "<p>Fecha de Vencimiento: $fecha_vencimiento</p>";
        echo "<p>CVV: $cvv</p>";
        echo "<p>¡Gracias por tu compra!</p>";
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Si el formulario no se envió correctamente, muestra un mensaje de error
        echo "<p>Error: El formulario no fue enviado correctamente.</p>";
    }
    ?>
</body>
</html>
