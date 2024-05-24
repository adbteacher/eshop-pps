<?php
require_once '../autoload.php'; // Incluye el archivo de conexión PDO
session_start();

if (!isset($_SESSION['UserRol']) || $_SESSION["UserRol"] !== 'A') {
    echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
    exit;
}

// Generar token CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Establecer conexión a la base de datos
$conexion = database::LoadDatabase();

// Consulta para obtener el número total de intentos de inicio de sesión
$query = "SELECT COUNT(*) AS total_logins FROM pps_logs_login";
$stmt = $conexion->prepare($query);
$stmt->execute();
$totalLogins = $stmt->fetch(PDO::FETCH_ASSOC)['total_logins'];

// Consulta para obtener el número de inicios de sesión correctos
$query = "SELECT COUNT(*) AS correct_logins FROM pps_logs_login WHERE lol_was_correct_login = 1";
$stmt = $conexion->prepare($query);
$stmt->execute();
$correctLogins = $stmt->fetch(PDO::FETCH_ASSOC)['correct_logins'];

// Consulta para obtener el número de inicios de sesión fallidos
$query = "SELECT COUNT(*) AS failed_logins FROM pps_logs_login WHERE lol_was_correct_login = 0";
$stmt = $conexion->prepare($query);
$stmt->execute();
$failedLogins = $stmt->fetch(PDO::FETCH_ASSOC)['failed_logins'];

// Consulta para obtener la distribución de inicios de sesión por usuario
$query = "SELECT pps_users.usu_name, COUNT(*) AS logins 
          FROM pps_logs_login 
          JOIN pps_users ON pps_logs_login.lol_user = pps_users.usu_id 
          GROUP BY pps_users.usu_name 
          ORDER BY logins DESC";
$stmt = $conexion->prepare($query);
$stmt->execute();
$loginsByUser = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la distribución de inicios de sesión por fecha
$query = "SELECT DATE(lol_datetime) AS login_date, COUNT(*) AS logins FROM pps_logs_login GROUP BY login_date ORDER BY login_date DESC";
$stmt = $conexion->prepare($query);
$stmt->execute();
$loginsByDate = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cerrar la conexión a la base de datos
$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Tráfico Web</title>
    <!-- Incluye Bootstrap CSS -->
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../nav.php"; ?>
<div class="container mt-5">
    <h1>Análisis de Tráfico Web</h1>

    <!-- Mostrar estadísticas generales -->
    <h2>Estadísticas Generales</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Métrica</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total de intentos de inicio de sesión</td>
                    <td><?php echo htmlspecialchars($totalLogins); ?></td>
                </tr>
                <tr>
                    <td>Inicios de sesión correctos</td>
                    <td><?php echo htmlspecialchars($correctLogins); ?></td>
                </tr>
                <tr>
                    <td>Inicios de sesión fallidos</td>
                    <td><?php echo htmlspecialchars($failedLogins); ?></td>
                </tr>
            </tbody>
        </table>
    </form>

    <!-- Mostrar distribución de inicios de sesión por usuario -->
    <h2>Distribución de Inicios de Sesión por Usuario</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Usuario</th>
                <th>Inicios de Sesión</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loginsByUser as $login): ?>
            <tr>
                <td><?php echo htmlspecialchars($login['usu_name']); ?></td>
                <td><?php echo htmlspecialchars($login['logins']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mostrar distribución de inicios de sesión por fecha -->
    <h2>Distribución de Inicios de Sesión por Fecha</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Fecha</th>
                <th>Inicios de Sesión</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loginsByDate as $login): ?>
            <tr>
                <td><?php echo htmlspecialchars($login['login_date']); ?></td>
                <td><?php echo htmlspecialchars($login['logins']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Gráfico de inicios de sesión por fecha -->
    <h2>Gráfico de Inicios de Sesión por Fecha</h2>
    <canvas id="loginsByDateChart"></canvas>
    <script>
        var ctx = document.getElementById('loginsByDateChart').getContext('2d');
        var loginsByDateChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($loginsByDate, 'login_date')); ?>,
                datasets: [{
                    label: 'Inicios de Sesión',
                    data: <?php echo json_encode(array_column($loginsByDate, 'logins')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</div>

<!-- Incluye Bootstrap JS y dependencias -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>



