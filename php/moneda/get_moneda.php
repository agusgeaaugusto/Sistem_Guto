<?php
include 'conexion_bi.php';

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_mon = $_GET['id'];

    $query = "SELECT id_mon, nombre_mon, tasa_cambio FROM Moneda WHERE id_mon = $1";
    $result = pg_query_params($conexion, $query, array($id_mon));

    if ($result) {
        $moneda = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($moneda);
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Moneda no encontrada'));
    }
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'ID de moneda no válido'));
}
?>
