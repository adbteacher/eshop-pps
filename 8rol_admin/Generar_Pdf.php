<?php
require_once '../autoload.php';
require_once '../vendor/autoload.php';

use TCPDF;

$conexion = database::LoadDatabase();

// Consulta para obtener el inventario de productos con campos relevantes
$query = "SELECT prd_id, prd_name, prd_price, prd_quantity_shop, prd_stock FROM pps_products";
$stmt = $conexion->prepare($query);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la suma de productos por categoría en tienda
$queryCategoriaTienda = "SELECT pps_categories.cat_description, SUM(pps_products.prd_quantity_shop) as total_tienda
                         FROM pps_products 
                         JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id 
                         GROUP BY pps_categories.cat_description";
$stmtCategoriaTienda = $conexion->prepare($queryCategoriaTienda);
$stmtCategoriaTienda->execute();
$productosPorCategoriaTienda = $stmtCategoriaTienda->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la suma de productos por categoría en stock
$queryCategoriaStock = "SELECT pps_categories.cat_description, SUM(pps_products.prd_stock) as total_stock
                        FROM pps_products 
                        JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id 
                        GROUP BY pps_categories.cat_description";
$stmtCategoriaStock = $conexion->prepare($queryCategoriaStock);
$stmtCategoriaStock->execute();
$productosPorCategoriaStock = $stmtCategoriaStock->fetchAll(PDO::FETCH_ASSOC);

// Cerrar la conexión a la base de datos
$conexion = null;

// Crear nueva instancia de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Nombre');
$pdf->SetTitle('Informe de Inventario de Productos');
$pdf->SetSubject('Informe de Inventario de Productos');
$pdf->SetKeywords('Inventario, Productos, PDF');
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();

// Contenido del PDF
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->Cell(0, 10, 'Inventario de Productos', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('dejavusans', '', 10);

// Tabla de inventario de productos
$html = '<h2>Inventario de Productos</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Cantidad en Tienda</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>';
foreach ($productos as $producto) {
    $html .= '<tr>
        <td>' . htmlspecialchars($producto['prd_id']) . '</td>
        <td>' . htmlspecialchars($producto['prd_name']) . '</td>
        <td>' . htmlspecialchars($producto['prd_price']) . '</td>
        <td>' . htmlspecialchars($producto['prd_quantity_shop']) . '</td>
        <td>' . htmlspecialchars($producto['prd_stock']) . '</td>
    </tr>';
}
$html .= '</tbody></table>';

// Tabla de suma de productos por categoría en tienda
$html .= '<h2>Suma de Productos por Categoría (En Tienda)</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Categoría</th>
            <th>Total en Tienda</th>
        </tr>
    </thead>
    <tbody>';
foreach ($productosPorCategoriaTienda as $categoria) {
    $html .= '<tr>
        <td>' . htmlspecialchars($categoria['cat_description']) . '</td>
        <td>' . htmlspecialchars($categoria['total_tienda']) . '</td>
    </tr>';
}
$html .= '</tbody></table>';

// Tabla de suma de productos por categoría en stock
$html .= '<h2>Suma de Productos por Categoría (En Stock)</h2>
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Categoría</th>
            <th>Total en Stock</th>
        </tr>
    </thead>
    <tbody>';
foreach ($productosPorCategoriaStock as $categoria) {
    $html .= '<tr>
        <td>' . htmlspecialchars($categoria['cat_description']) . '</td>
        <td>' . htmlspecialchars($categoria['total_stock']) . '</td>
    </tr>';
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Salida del PDF
$pdf->Output('Informe_Inventario_Productos.pdf', 'I');
?>
