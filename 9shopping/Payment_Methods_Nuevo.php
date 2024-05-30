<?php
require_once "../Database.php";

// Función para limpiar y validar los datos de entrada
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Procesar formulario de pago
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metodoPago = $_POST['metodo_pago'];
    $db = database::LoadDatabase();

    // ID de usuario válido (asegúrate de cambiar esto al ID de usuario actual)
    $user_id = 1; // Cambiar esto al ID de usuario válido

    if ($metodoPago == 'tarjeta_credito') {
        $cardNumber = sanitizeInput($_POST['card_number']);
        $cveNumber = sanitizeInput($_POST['cve_number']);
        $cardholder = sanitizeInput($_POST['cardholder']);
        $expirationDate = sanitizeInput($_POST['expiration_date']);

        try {
            $stmt = $db->prepare("INSERT INTO pps_payment_methods_per_user (
                pmu_user, pmu_payment_method, pmu_card_number, pmu_cve_number, pmu_cardholder, pmu_expiration_date
            ) VALUES (
                :user_id, 1, :card_number, :cve_number, :cardholder, :expiration_date
            )");

            $stmt->execute([
                ':user_id' => $user_id,
                ':card_number' => $cardNumber,
                ':cve_number' => $cveNumber,
                ':cardholder' => $cardholder,
                ':expiration_date' => $expirationDate
            ]);

            echo "Pago con tarjeta de crédito procesado correctamente.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif ($metodoPago == 'paypal') {
        $onlineAccount = sanitizeInput($_POST['online_account']);
        $onlinePassword = sanitizeInput($_POST['online_password']);

        try {
            $stmt = $db->prepare("INSERT INTO pps_payment_methods_per_user (
                pmu_user, pmu_payment_method, pmu_online_account, pmu_online_password
            ) VALUES (
                :user_id, 2, :online_account, :online_password
            )");

            $stmt->execute([
                ':user_id' => $user_id,
                ':online_account' => $onlineAccount,
                ':online_password' => $onlinePassword
            ]);

            echo "Pago con PayPal procesado correctamente.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif ($metodoPago == 'transferencia_bancaria') {
        $accountNumber = sanitizeInput($_POST['account_number']);

        try {
            $stmt = $db->prepare("INSERT INTO pps_payment_methods_per_user (
                pmu_user, pmu_payment_method, pmu_account_number
            ) VALUES (
                :user_id, 3, :account_number
            )");

            $stmt->execute([
                ':user_id' => $user_id,
                ':account_number' => $accountNumber
            ]);

            echo "Pago con transferencia bancaria procesado correctamente.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
