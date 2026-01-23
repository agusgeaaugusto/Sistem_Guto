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
        $this->Cell(0,10,'Reporte de Ventas',0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Cabecera de la tabla
$pdf->Cell(40,10,'Fecha',1);
$pdf->Cell(50,10,'Cliente',1);
$pdf->Cell(30,10,'Total (Gs)',1);
$pdf->Ln();

$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

if ($desde && $hasta) {
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,10,"Desde: $desde  Hasta: $hasta",0,1,'L');
    $pdf->Ln(2);

    $query = "SELECT v.fecha_venta, p.nombre_completo, v.total
    FROM venta v
    JOIN persona p ON v.id_persona = p.id_persona
    WHERE v.fecha_venta BETWEEN '$desde' AND '$hasta' ORDER BY v.fecha_venta DESC
";

$resultado = pg_query($conexion, $query);

while ($fila = pg_fetch_assoc($resultado)) {
    $pdf->Cell(40,10,$fila['fecha_venta'],1);
    $pdf->Cell(50,10,utf8_decode($fila['nombre_completo']),1);
    $pdf->Cell(30,10,number_format($fila['total'], 0, ',', '.'),1);
    $pdf->Ln();
}

} else {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,10,'Debe proporcionar un rango de fechas válido.',0,1,'C');
}

$pdf->Output();
?>
