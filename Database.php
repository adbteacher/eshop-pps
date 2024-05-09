<?php

class database
{

	public static function LoadDatabase()
	{
		$Server       = 'localhost';
		$DatabaseName = 'ceti';
		$User         = 'root';
		$Password     = 'pps-2024';
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