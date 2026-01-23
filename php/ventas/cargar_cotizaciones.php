<?php
require_once 'conexion_bi.php';

$query = "SELECT nombre_mon, tasa_cambio FROM Moneda WHERE nombre_mon IN ('guarani', 'real', 'dolar')";
$result = pg_query($conexion, $query);

$monedas = [];

while ($row = pg_fetch_assoc($result)) {
    $monedas[strtolower($row['nombre_mon'])] = $row['tasa_cambio'];
}

echo json_encode($monedas);
pg_close($conexion);
?>
