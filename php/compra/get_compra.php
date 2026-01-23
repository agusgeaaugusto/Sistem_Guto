<?php
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_com = $_GET['id'];

    // Consulta para obtener los detalles de la compra
    $query = "SELECT id_com, id_proveedor FROM Compra WHERE id_com = $1";
    $result = pg_query_params($conexion, $query, array($id_com));

    if ($result) {
        // Si se encuentra la compra, devolver los detalles en formato JSON
        $compra = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($compra);
    } else {
        // Si no se encuentra la compra, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Compra no encontrada'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de compra no válido'));
}
?>
