<?php
require_once '../cargos/conexion_bi.php';
require '../../vendor/fpdf/fpdf.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Reporte de Compras',0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

if ($desde && $hasta) {
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,10,"Desde: $desde  Hasta: $hasta",0,1,'L');
    $pdf->Ln(2);

    $query = "
        SELECT c.fecha_com, p.nombre_proveedor, c.total
        FROM compra c
        JOIN proveedor p ON c.id_proveedor = p.id_proveedor
        WHERE c.fecha_com BETWEEN $1 AND $2
        ORDER BY c.fecha_com DESC
    ";

    $result = pg_query_params($conexion, $query, array($desde, $hasta));

    // Cabecera de tabla
    $pdf->Cell(40,10,'Fecha',1);
    $pdf->Cell(70,10,'Proveedor',1);
    $pdf->Cell(30,10,'Total (Gs)',1);
    $pdf->Ln();

    while ($fila = pg_fetch_assoc($result)) {
        $pdf->Cell(40,10,$fila['fecha_com'],1);
        $pdf->Cell(70,10,utf8_decode($fila['nombre_proveedor']),1);
        $pdf->Cell(30,10,number_format($fila['total'], 0, ',', '.'),1);
        $pdf->Ln();
    }
} else {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,10,'Debe proporcionar un rango de fechas válido.',0,1,'C');
}

$pdf->Output();
?>
