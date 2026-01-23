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
        $this->Cell(0,10,'Reporte Detallado por Producto',0,1,'C');
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
        SELECT p.nombre_producto, v.fecha_venta, per.nombre_completo, vd.cantidad, vd.precio
        FROM venta_detalle vd
        JOIN venta v ON vd.id_venta = v.id_venta
        JOIN producto p ON vd.id_producto = p.id_producto
        JOIN persona per ON v.id_persona = per.id_persona
        WHERE v.fecha_venta BETWEEN $1 AND $2
        ORDER BY p.nombre_producto, v.fecha_venta
    ";

    $result = pg_query_params($conexion, $query, array($desde, $hasta));

    // Cabecera de tabla
    $pdf->Cell(50,10,'Producto',1);
    $pdf->Cell(30,10,'Fecha',1);
    $pdf->Cell(50,10,'Cliente',1);
    $pdf->Cell(20,10,'Cant.',1);
    $pdf->Cell(30,10,'Total (Gs)',1);
    $pdf->Ln();

    while ($fila = pg_fetch_assoc($result)) {
        $total = $fila['cantidad'] * $fila['precio'];
        $pdf->Cell(50,10,utf8_decode($fila['nombre_producto']),1);
        $pdf->Cell(30,10,$fila['fecha_venta'],1);
        $pdf->Cell(50,10,utf8_decode($fila['nombre_completo']),1);
        $pdf->Cell(20,10,$fila['cantidad'],1);
        $pdf->Cell(30,10,number_format($total, 0, ',', '.'),1);
        $pdf->Ln();
    }
} else {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,10,'Debe proporcionar un rango de fechas válido.',0,1,'C');
}

$pdf->Output();
?>
