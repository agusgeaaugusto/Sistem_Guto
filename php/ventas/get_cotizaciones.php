<?php
include 'conexion_bi.php';
header('Content-Type: application/json');

$query = "SELECT nombre_mon, tasa_cambio FROM moneda";
$result = pg_query($conexion, $query);

$respuesta = [];

while ($row = pg_fetch_assoc($result)) {
    $nombre = strtolower($row['nombre_mon']);
    $respuesta[$nombre] = floatval($row['tasa_cambio']);
}

echo json_encode($respuesta);
pg_close($conexion);
?>
