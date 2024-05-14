<style>
    /* Alineación a la derecha del perfil */
    .profile-user {
        margin-left: auto;
        /* Empuja el resto a la izquierda, alinea este ítem a la derecha */
    }

    /* Estilo para la imagen del perfil */
    .profile-image {
        width: 40px;
        /* Tamaño de la imagen */
        height: 40px;
        /* Altura de la imagen */
        border-radius: 50%;
        /* Hace que la imagen sea un círculo */
        margin-right: 5px;
        /* Espacio entre la imagen y el texto */
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">Frutería del Barrio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Carrito</a>
                </li>
                <li class="nav-item, profile-user">
                    <a href="../4profile/main_profile.php">
                        <img src="../0images/default_user.png" alt="User" class="profile-image">
                        Perfil
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>