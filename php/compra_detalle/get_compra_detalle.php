<?php
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_comp_det = $_GET['id'];

    // Consulta para obtener los detalles de la compra_detalle
    $query = "SELECT * FROM Compra_Detalle WHERE id_comp_det = $1";
    $result = pg_query_params($conexion, $query, array($id_comp_det));

    if ($result) {
        // Si se encuentra la compra_detalle, devolver los detalles en formato JSON
        $compra_detalle = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($compra_detalle);
    } else {
        // Si no se encuentra la compra_detalle, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Detalle de compra no encontrado'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de detalle de compra no válido'));
}

// Cerrar conexión
pg_close($conexion);
?>
