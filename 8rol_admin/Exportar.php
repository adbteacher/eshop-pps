<?php
/*
	 Este codigo realiza la logica para exportar el ficehro CSV de los productos 
     con el nombre y la fecha.
	 */
// Establecer conexión a la base de datos
require_once '../autoload.php';
session_start();
functions::checkAdminAccess();
$conexion = database::LoadDatabase();

// Consulta para obtener los productos
$query = "SELECT * FROM pps_products";
$stmt  = $conexion->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre del archivo CSV a generar con la fecha actual
$fechaActual = date('Y-m-d');
$nombreArchivo = 'Productos_' . $fechaActual . '.csv';

// Cabeceras para forzar la descarga del archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

// Abrir el archivo CSV para escritura
$archivo = fopen('php://output', 'w');

// Escribir la cabecera del archivo CSV
fputcsv($archivo, array('ID', 'Nombre', 'Categoría', 'Detalles', 'Precio', 'Cantidad en Tienda', 'Stock', 'Imagen', 'Descripción'));

// Escribir los datos de los productos en el archivo CSV
foreach ($productos as $producto)
{
    fputcsv($archivo, $producto);
}

// Cerrar el archivo CSV
fclose($archivo);

// Cerrar la conexión a la base de datos
$conexion = null;