<?php
	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}
	require '../autoload.php'; // Archivo donde configuras la conexión a la base de datos

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	$user_id   = $_SESSION['UserID'];
	$user_role = $_SESSION['UserRol'];

	$pdo = database::LoadDatabase();

	if ($user_role == 'S')
	{
		// Si el rol del usuario es 'S', ejecutar la consulta original
		$stmt = $pdo->prepare("
        SELECT * 
        FROM PPS_MESSAGES 
        WHERE MSG_ROL_TO = :user_role 
        AND MSG_IS_REPLIED = 'N' 
        ORDER BY MSG_DATETIME DESC
    ");
		$stmt->execute(['user_role' => $user_role]);
	}
	else
	{
		// Si el rol del usuario es cualquier otro, ejecutar la nueva consulta
		$stmt = $pdo->prepare("
        SELECT * 
        FROM PPS_MESSAGES 
        WHERE MSG_IS_REPLIED = 'N' 
        AND MSG_USER_RECEIVER = :user_id 
        ORDER BY MSG_DATETIME DESC
    ");
		$stmt->execute(['user_id' => $user_id]);
	}
	$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$Quantity = count($messages);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Mensajes</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php" ?>

<div class="container mt-5">
    <?php if (!empty($_SESSION["msg_message_success"]))
		{ ?>
        <div class="alert alert-success"><?php echo $_SESSION["msg_message_success"]; ?></div>
			<?php $_SESSION["msg_message_success"] = "";
        }elseif (!empty($_SESSION["msg_message_error"]))
    {?>
    <div class="alert alert-error"><?php echo $_SESSION["msg_message_error"]; ?></div>
		<?php
    }
    ?>
    <h1 class="mb-4">Mensajes recibidos <?php echo "(" . $Quantity . ")" ?></h1>
	<?php if (count($messages) > 0): ?>
        <div class="row">
			<?php foreach ($messages as $message):
				$Query = $pdo->prepare("SELECT usu_name FROM pps_users WHERE usu_id = " . $message['msg_user_sender']);
				$Query->execute();
				$UserName = $Query->fetchColumn();
				?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">De: <?php echo htmlspecialchars($UserName); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($message['msg_message'])); ?></p>
                            <p class="card-text"><small class="text-muted">Recibido: <?php echo htmlspecialchars($message['msg_datetime']); ?></small></p>
                            <form action="ReplyMessage.php" method="post">
                                <input type="hidden" name="sender_id" value="<?php echo $message['msg_user_sender']; ?>">
                                <input type="hidden" name="replied_to" value="<?php echo $message['msg_id']; ?>">
                                <textarea class="form-control mb-2" name="message" rows="2" placeholder="Escribe tu respuesta"></textarea>
                                <button type="submit" class="btn btn-primary">Responder</button>
                            </form>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay mensajes disponibles.
        </div>
	<?php endif; ?>
    <a href="SendMessage.php" class="btn btn-primary">Crear Mensaje</a>
</div>
</body>
</html>
