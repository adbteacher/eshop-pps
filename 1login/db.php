<?php
/**
 * Gestión de la conexión a la base de datos utilizando PDO.
 *
 * Utiliza variables de entorno para configurar la conexión de manera segura.
 * Establece opciones de PDO para mejorar la seguridad y la eficiencia en el manejo de la base de datos.
 */

/**
 * Crea y retorna una conexión segura a la base de datos utilizando PDO.
 *
 * Esta función construye el Data Source Name (DSN) a partir de las variables de entorno o valores predeterminados.
 * Configura opciones de PDO para desactivar la emulación de sentencias preparadas, habilitar el manejo de excepciones
 * y establecer el modo de búsqueda predeterminado a asociativo.
 *
 * @return PDO Objeto PDO para la conexión a la base de datos.
 * @throws PDOException Si la conexión falla, se maneja lanzando una excepción.
 */
function GetDatabaseConnection(): PDO
{
	// Configuración de los parámetros de conexión a partir de variables de entorno o valores predeterminados.
	$Server       = getenv('DB_HOST') ?: 'localhost';  // Servidor de la base de datos
	$DatabaseName = getenv('DB_NAME') ?: 'eshop_pps';  // Nombre de la base de datos
	$User         = getenv('DB_USER') ?: 'root';        // Usuario de la base de datos
	$Password     = getenv('DB_PASSWORD') ?: '';        // Contraseña del usuario de la base de datos
	$Charset      = 'utf8mb4';                          // Codificación de caracteres para la conexión

	// Construcción del DSN para la conexión.
	$Dsn = "mysql:host=$Server;dbname=$DatabaseName;charset=$Charset";

	// Opciones de configuración de PDO para mejorar la seguridad y eficiencia.
	$Options = [
		PDO::ATTR_EMULATE_PREPARES   => false,             // Desactivar emulación de sentencias preparadas
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Habilitar el manejo de excepciones
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC   // Establecer modo de búsqueda predeterminado a asociativo
	];

	try
	{
		// Intento de conexión y retorno del objeto PDO.
		$Connection = new PDO($Dsn, $User, $Password, $Options);
		return $Connection;
	}
	catch (PDOException $e)
	{
		// Registro y manejo de errores de conexión.
		error_log('Error de conexión: ' . $e->getMessage());
		die('Error de conexión con la base de datos.');  // Finaliza el script y muestra un mensaje de error
	}
}
