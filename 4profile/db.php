<?php

/**
 * Gestión de la conexión a la base de datos utilizando PDO.
 *
 * Utiliza variables de entorno para configurar la conexión de manera segura.
 * Establece opciones de PDO para mejorar la seguridad y la eficiencia en el manejo de la base de datos.
 */

/**
 * Retorna una conexión segura a la base de datos.
 *
 * @return PDO Objeto PDO para la conexión a la base de datos.
 * @throws PDOException Si la conexión falla, se maneja lanzando una excepción.
 */
function GetDatabaseConnection()
{
    $Server = getenv('DB_HOST') ?: 'localhost';
    $DatabaseName = getenv('DB_NAME') ?: 'eshop_pps';
    $User = getenv('DB_USER') ?: 'root';
    $Password = getenv('DB_PASSWORD') ?: '';
    $Charset = 'utf8mb4';

    $Dsn = "mysql:host=$Server;dbname=$DatabaseName;charset=$Charset";

    $Options = [
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $Connection = new PDO($Dsn, $User, $Password, $Options);
        return $Connection;
    } catch (PDOException $e) {
        error_log('Error de conexión: ' . $e->getMessage());
        die('Error de conexión con la base de datos.');
    }
}
