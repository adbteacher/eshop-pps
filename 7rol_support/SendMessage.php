<?php
	session_start();
	require_once '../autoload.php'; // Archivo donde configuras la conexión a la base de datos

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$sender_id = $_SESSION['UserID'];
		$sender_role = $_SESSION['UserRol'];
		$message = $_POST['message'];
		$receiver_role = $sender_role == 'U' ? 'S' : 'U';
        $is_replied = "N";

		$pdo = database::LoadDatabase();

		$stmt = $pdo->prepare("INSERT INTO pps_messages (msg_user_sender, msg_message, msg_rol_from, msg_rol_to, msg_is_replied, msg_datetime) VALUES (?, ?, ?, ?, ?, NOW())");
		if ($stmt->execute([$sender_id, $message, $sender_role, $receiver_role, $is_replied])) {
			$success_message = "Mensaje enviado con éxito.";
		} else {
			$error_message = "Error al enviar el mensaje.";
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Mensaje</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php" ?>

<div class="container mt-5">
    <h1 class="mb-4">Enviar Mensaje</h1>
	<?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php header("refresh:3;url=ViewMessage.php"); // Redirigir después de 3 segundos ?>
	<?php endif; ?>
	<?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
	<?php endif; ?>
    <form action="SendMessage.php" method="post">
        <div class="mb-3">
            <label for="message" class="form-label">Mensaje:</label>
            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
        <a href="ViewMessage.php" class="btn btn-secondary">Atrás</a>
    </form>
</div>

<!--<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>-->
</body>
</html>
