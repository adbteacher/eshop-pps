<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .loader-wrapper {
            text-align: center;
            position: relative;
        }

        .loader {
            position: relative;
            width: 80px;
            height: 80px;
            margin: auto;
        }

        .loader::before,
        .loader::after {
            content: '';
            position: absolute;
            border: 8px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loader::before {
            width: 80px;
            height: 80px;
            border-top: 8px solid #3498db;
            top: 0;
            left: 0;
            transform-origin: center center;
        }

        .loader::after {
            width: 60px;
            height: 60px;
            top: 10px;
            left: 10px;
            border-top: 8px solid #e74c3c;
            animation-duration: 1.5s;
            transform-origin: center center;
        }

        .loader-inner {
            width: 40px;
            height: 40px;
            border: 8px solid transparent;
            border-radius: 50%;
            border-top: 8px solid #f1c40f;
            animation: spin 2s linear infinite reverse;
            position: absolute;
            top: 20px;
            left: 20px;
            transform-origin: center center;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        h2 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-top: 20px;
            animation: fadeIn 2s ease-in-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }
    </style>
    <script>
        // Redirige al index después de 2 segundos
        setTimeout(function () {
            window.location.href = '/index.php';
        }, 2000);
    </script>
</head>
<body>
    <div class="loader-wrapper">
        <div class="loader">
            <div class="loader-inner"></div>
        </div>
        <h2>Cerrando sesión...</h2>
    </div>
</body>
</html>
