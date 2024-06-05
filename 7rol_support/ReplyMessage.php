<?php
	session_start();
	require '../autoload.php'; // Archivo donde configuras la conexión a la base de datos

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$sender_id = $_SESSION['UserID'];
		$message = $_POST['message'];
		$rol_from = $_SESSION['UserRol'];
		$rol_to = $rol_from == 'U' ? 'S' : 'U';
		$replied_to = $_POST['replied_to']; // ID del mensaje al que se está respondiendo
        $IsReplied = "N";

		$pdo = database::LoadDatabase();

		$stmt2 = $pdo->prepare("SELECT * FROM pps_messages WHERE msg_id = :id");
		$stmt2->bindParam(':id', $replied_to, PDO::PARAM_INT);
		$stmt2->execute();

		$MessageReceived = $stmt2->fetch(PDO::FETCH_ASSOC);


		// Insertar el nuevo mensaje
		$stmt = $pdo->prepare("INSERT INTO pps_messages (msg_user_sender, msg_user_receiver, msg_message, msg_rol_from, msg_rol_to, msg_is_replied ,msg_datetime) VALUES (?, ?, ?, ?, ?, ?, NOW())");
		if ($stmt->execute([$sender_id, $MessageReceived['msg_user_sender'], $message, $rol_from, $rol_to, $IsReplied])) {
			// Obtener el ID del último mensaje insertado
			$last_message_id = $pdo->lastInsertId();

			// Actualizar el mensaje original para marcarlo como respondido
			$update_stmt = $pdo->prepare("UPDATE pps_messages SET msg_is_replied = 'Y' WHERE msg_id = ?");
			if ($update_stmt->execute([$replied_to])) {
				$_SESSION["msg_message_success"] = "Respuesta enviada con éxito.";
				header("Location: ViewMessage.php"); // Redirigir
                exit();
			} else
            {
				$error_message = "Error al actualizar el mensaje original.";
				$_SESSION["msg_message_error"]= "Error al actualizar el mensaje original.";
			}
		} else {
			$error_message = "Error al enviar la respuesta.";
			$_SESSION["msg_message_error"] = "Error al enviar la respuesta.";
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Mensaje</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php" ?>

<div class="container mt-5">
    <h1 class="mb-4">Responder Mensaje</h1>
	<?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
	<?php endif; ?>
	<?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
	<?php endif; ?>
    <form action="ReplyMessage.php" method="post">
        <div class="mb-3">
            <label for="sender_id" class="form-label">ID del remitente:</label>
            <input type="text" class="form-control" id="sender_id" name="sender_id" required readonly value="<?php echo htmlspecialchars($_POST['sender_id']); ?>">
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Mensaje:</label>
            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
        </div>
        <input type="hidden" name="replied_to" value="<?php echo htmlspecialchars($_POST['replied_to']); ?>">
        <button type="submit" class="btn btn-primary">Enviar Respuesta</button>
    </form>
<!--    <a href="javascript:history.back()" class="btn btn-secondary mt-3">Atrás</a>-->
</div>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
