<?php
	require_once '../autoload.php';

	$conexion = database::LoadDatabase();

	// Consulta para obtener el inventario de productos con campos relevantes
	$query = "SELECT prd_id, prd_name, prd_price, prd_quantity_shop, prd_stock FROM pps_products";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
	$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->AddPage();
	$pdf->SetFont('dejavusans', 'B', 12);
	$pdf->Cell(0, 10, 'Inventario de Productos', 0, 1, 'C');
	$pdf->Ln(10);
	$pdf->SetFont('dejavusans', '', 10);
	$pdf->SetFillColor(200, 220, 255);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->SetLineWidth(0.1);
	$pdf->Cell(30, 10, 'ID', 1, 0, 'C', 1);
	$pdf->Cell(60, 10, 'Nombre', 1, 0, 'C', 1);
	$pdf->Cell(30, 10, 'Precio', 1, 0, 'C', 1);
	$pdf->Cell(40, 10, 'Cantidad en Tienda', 1, 0, 'C', 1);
	$pdf->Cell(30, 10, 'Stock', 1, 1, 'C', 1);
	foreach ($productos as $producto)
	{
		$pdf->Cell(30, 10, $producto['prd_id'], 1, 0, 'C');
		$pdf->Cell(60, 10, $producto['prd_name'], 1, 0, 'C');
		$pdf->Cell(30, 10, $producto['prd_price'], 1, 0, 'C');
		$pdf->Cell(40, 10, $producto['prd_quantity_shop'], 1, 0, 'C');
		$pdf->Cell(30, 10, $producto['prd_stock'], 1, 1, 'C');
	}
	$pdf->Output('Informe_Inventario_Productos.pdf', 'I');
