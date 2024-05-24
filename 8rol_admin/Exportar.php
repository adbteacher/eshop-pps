<?php
// Establecer conexión a la base de datos
require_once '../Database.php';
$conexion = database::LoadDatabase();

// Consulta para obtener los productos
$query = "SELECT * FROM pps_products";
$stmt = $conexion->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre del archivo CSV a generar
$nombreArchivo = 'productos2.csv';

// Cabeceras para forzar la descarga del archivo CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

// Abrir el archivo CSV para escritura
$archivo = fopen('php://output', 'w');

// Escribir la cabecera del archivo CSV
fputcsv($archivo, array('ID', 'Nombre', 'Categoría', 'Detalles', 'Precio', 'Cantidad en Tienda', 'Stock', 'Imagen', 'Descripción'));

// Escribir los datos de los productos en el archivo CSV
foreach ($productos as $producto) {
    fputcsv($archivo, $producto);
}

// Cerrar el archivo CSV
fclose($archivo);

// Cerrar la conexión a la base de datos
$conexion = null;
?>
