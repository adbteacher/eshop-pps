<?php
require_once 'funciones.php';
require_once("../autoload.php");

if (session_status() == PHP_SESSION_NONE)
{
    session_start(); // Start session
}

// Generate CSRF token if it does not exist
if (empty($_SESSION['csrf_token']))
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['UserID']))
{
    header('Location: login.php');
    exit();
}

$UserId        = $_SESSION['UserID'];
$LoginAttempts = [];

// Pagination variables
$attemptsPerPage = isset($_POST['attempts_per_page']) ? (int)$_POST['attempts_per_page'] : 15;
$currentPage     = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset          = ($currentPage - 1) * $attemptsPerPage;

try
{
    $conn = database::LoadDatabase(); // Database connection

    // Count the total number of login attempts for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM pps_logs_login WHERE lol_user = :user_id");
    $countStmt->bindParam(':user_id', $UserId, PDO::PARAM_INT);
    $countStmt->execute();
    $totalAttempts = $countStmt->fetchColumn();
    $totalPages    = ceil($totalAttempts / $attemptsPerPage);

    // Retrieve login attempts with limit and offset
    $stmt = $conn->prepare("
        SELECT lol_ip, lol_was_correct_login, lol_datetime
        FROM pps_logs_login
        WHERE lol_user = :user_id
        ORDER BY lol_datetime DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':user_id', $UserId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $attemptsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $LoginAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e)
{
    error_log($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Web page for PPS at CIPFP Mislata, by smontesa">
    <meta name="keywords" content="smontesa, pps, mislata, cipfpmislata">
    <meta name="author" content="Sergio Montesa">

    <!-- Title -->
    <title>Actividad reciente</title>

    <!-- CSS / Bootstrap stylesheet -->
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
    <link rel="manifest" href="/0images/site.webmanifest">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .status-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success {
            background-color: green;
        }

        .status-failed {
            background-color: red;
        }
    </style>
</head>

<body>
<?php include "../nav.php"; // Include the Navbar ?>

<div class="container mt-4 mb-4">
    <div class="jumbotron">
        <h1 class="display-4">Actividad reciente</h1>
        <p class="lead">Revisa los últimos intentos de inicio de sesión en tu cuenta.</p>
        <hr class="my-4">
    </div>

    <?php if (!empty($LoginAttempts)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Hora</th>
                    <th>Tipo de acceso</th>
                    <th>Dirección IP</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($LoginAttempts as $Attempt): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($Attempt['lol_datetime']); ?></td>
                        <td>
                            <span class="status-icon <?php echo $Attempt['lol_was_correct_login'] ? 'status-success' : 'status-failed'; ?>"></span>
                            <?php echo $Attempt['lol_was_correct_login'] ? 'Exitoso' : 'Fallido'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($Attempt['lol_ip']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="javascript:;" onclick="changePage(1)">&laquo;&laquo;</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $currentPage - 1; ?>)">&laquo;</a>
                    </li>
                <?php endif; ?>
                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                    <li class="page-item <?php echo $currentPage == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $page; ?>)"><?php echo $page; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $currentPage + 1; ?>)">&raquo;</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $totalPages; ?>)">&raquo;&raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    <?php else: ?>
        <div class="no-records">No login records found.</div>
    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="../4profile/usu_sec.php" class="btn btn-primary">Volver</a>
    </div>
</div>

<?php include "../footer.php"; // Include the footer ?>

<!-- Script to change page in pagination -->
<script>
    function changePage(page) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>';

        const pageInput = document.createElement('input');
        pageInput.type = 'hidden';
        pageInput.name = 'page';
        pageInput.value = page;
        form.appendChild(pageInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>

</body>
</html>

<?php
$stmt = null;
?>
