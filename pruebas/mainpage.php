<!DOCTYPE html>
<html>
<head>
  <title>Página Principal</title>
  <link rel="stylesheet" href="estilos/style.css?v=0.0.1">
</head>
<body>
    <div id="contenido"><br>
        <form method="post" action="nuevo_producto.php" id="mainform">
            <?php
                // Generar y almacenar un token CSRF en la sesión
                session_start();
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="submit" name="Nuevo producto" value="Nuevo producto" class="boton">
            <input type="submit" name="Stats de ventas" value="Stats de ventas" class="boton">
        </form><br>
    </div>

    <?php
        include "biblioteca.php";
        
        $conn = connection();
        
        // Verificar si la conexión fue exitosa
        if ($conn) {
            $result = consulta($conn);
            
            // Verificar si la consulta devolvió resultados
            if ($result) {
                mostrar_tabla($result);
            } else {
                echo "<p>Error al obtener los datos.</p>";
            }
            
            cerrar_conexion($conn);
        } else {
            echo "<p>No se pudo conectar a la base de datos.</p>";
        }
    ?>
</body>
</html>
