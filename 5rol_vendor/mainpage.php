<!DOCTYPE html>
<html>
<head>
  <title>Página Principal</title>
  <link rel="stylesheet" href="estilos/style.css?v=0.0.1">
</head>
<body>
    <div id="contenido"><br>
        <form method="post" action="nuevo_producto.php" id="mainform">
            <input type="submit" name="Nuevo producto" value="Nuevo producto" class="boton">
        </form><br>
    </div>

    <?php
        include "biblioteca.php";
        $conn = connection();
        $result = consulta($conn);
        if ($result) {
            mostrar_tabla($result);
        }
        cerrar_conexion($conn);
    ?>

</body>
</html>
