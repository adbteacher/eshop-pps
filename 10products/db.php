<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    // Conexión a la base de datos en LOCAL
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pps";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Chequear conexión
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>