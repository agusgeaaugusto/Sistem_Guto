<?php
include 'conexion_bi.php';

$query = "SELECT id_com, fecha_com FROM Compra ORDER BY id_com ASC";
$result = pg_query($conexion, $query);

$compras = array();
while ($row = pg_fetch_assoc($result)) {
    $compras[] = $row;
}

echo json_encode($compras);
pg_close($conexion);
?>
