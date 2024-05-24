<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analítica de Tienda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-top: 50px;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: auto;
            text-align: center;
        }

        .card {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            margin: 20px;
            display: inline-block;
            width: 200px;
            vertical-align: top;
        }

        .card h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>

<header>
    <h1>Analítica de Tienda</h1>
</header>

<div class="container">
    <div class="card">
        <h2>Ventas</h2>
        <p>Analiza tus ventas para identificar patrones y tendencias.</p>
        <a href="Datos_Ventas.php">Ir a análisis de ventas</a>
    </div>

    <div class="card">
        <h2>Inventario</h2>
        <p>Controla tu inventario para asegurarte de mantener stock suficiente.</p>
        <a href="Inventario.php">Ir a análisis de inventario</a>
    </div>

    <div class="card">
        <h2>Tráfico Web</h2>
        <p>Monitoriza el tráfico de tu sitio web para optimizar la experiencia del usuario.</p>
        <a href="Trafico_Web.php">Ir a análisis de tráfico web</a>
    </div>

    <div class="card">
        <h2>Comportamiento del Usuario</h2>
        <p>Entiende el comportamiento de los usuarios para mejorar la conversión.</p>
        <a href="analisis_comportamiento.php">Ir a análisis de comportamiento del usuario</a>
    </div>
</div>

</body>
</html>
