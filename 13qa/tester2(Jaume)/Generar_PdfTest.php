<?php
use PHPUnit\Framework\TestCase;

require_once './8rol_admin/Generar_Pdf.php';

class GenerarPdfTest extends TestCase {

    public function testCreatePdf() {
        
        $productos = [
            [
                'prd_name' => 'Producto 1',
                'prd_category' => 'Categoría 1',
                'prd_price' => 10.0,
                'prd_stock' => 100,
                'prd_on_offer' => 1,
                'prd_offer_price' => 8.0
            ]
        ];

        $productosEnOferta = [
            [
                'prd_name' => 'Producto 1',
                'prd_category' => 'Categoría 1',
                'prd_price' => 10.0,
                'prd_stock' => 100,
                'prd_offer_price' => 8.0
            ]
        ];

        $productosPorCategoriaStock = [
            [
                'cat_description' => 'Categoría 1',
                'total_stock' => 100
            ]
        ];

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
       
        $this->assertInstanceOf(TCPDF::class, $pdf);

        $pdfText = $pdf->getText();
        $this->assertStringContainsString('Inventario de Productos', $pdfText);
        $this->assertStringContainsString('Producto 1', $pdfText);
        $this->assertStringContainsString('Categoría 1', $pdfText);
        $this->assertStringContainsString('10.0', $pdfText);
        $this->assertStringContainsString('100', $pdfText);
        $this->assertStringContainsString('8.0', $pdfText);
    }
}
?>