<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frutería del Barrio</title>
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Frutería del Barrio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contacto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="4profile/main_profile.php">Perfil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="jumbotron">
            <h1 class="display-4">¡Bienvenidos a Frutería del Barrio!</h1>
            <p class="lead">Los mejores frutos frescos directo de los agricultores a tu mesa.</p>
            <hr class="my-4">
            <p>Visita nuestra sección de ofertas especiales.</p>
            <a class="btn btn-primary btn-lg" href="#" role="button">Ver Ofertas</a>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card">
                    <img src="https://via.placeholder.com/150" class="card-img-top" alt="Manzanas">
                    <div class="card-body">
                        <h5 class="card-title">Manzanas</h5>
                        <p class="card-text">Frescas y jugosas manzanas rojas listas para tu consumo.</p>
                        <a href="#" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <img src="https://via.placeholder.com/150" class="card-img-top" alt="Naranjas">
                    <div class="card-body">
                        <h5 class="card-title">Naranjas</h5>
                        <p class="card-text">Perfectas para un jugo lleno de vitaminas por las mañanas.</p>
                        <a href="#" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <img src="https://via.placeholder.com/150" class="card-img-top" alt="Plátanos">
                    <div class="card-body">
                        <h5 class="card-title">Plátanos</h5>
                        <p class="card-text">Dulces y nutritivos, ideales para cualquier hora del día.</p>
                        <a href="#" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>