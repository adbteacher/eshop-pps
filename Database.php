<?php

class database
{

	public static function LoadDatabase()
	{
		$Server       = getenv('DB_HOST') ?: '192.168.56.200';
		$DatabaseName = getenv('DB_NAME') ?: 'eshop_pps';
		$User         = getenv('DB_USER') ?: 'root';
		$Password     = getenv('DB_PASSWORD') ?: 'Desarrollo@2404';
		$Charset      = 'utf8mb4';

		$Dsn = "mysql:host=$Server;dbname=$DatabaseName;charset=$Charset";

		$Options = [
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		try
		{
			$Connection = new PDO($Dsn, $User, $Password, $Options);
			return $Connection;
		}
		catch (PDOException $e)
		{
			error_log('Error de conexión: ' . $e->getMessage());
			die('Error de conexión con la base de datos.');
		}


	}

}