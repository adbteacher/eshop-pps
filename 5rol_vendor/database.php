<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    public static function LoadDatabase()
    {
        $Server       = getenv('DB_HOST');
        $DatabaseName = getenv('DB_NAME');
        $User         = getenv('DB_USER');
        $Password     = getenv('DB_PASSWORD');
        $Charset      = 'utf8mb4';

        $Dsn = "mysql:host=$Server;dbname=$DatabaseName;charset=$Charset";

        $Options = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
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
}