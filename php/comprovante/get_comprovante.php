<?php
// Suponiendo que tienes una conexión a la base de datos establecida
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_comprovante = $_GET['id'];

    // Consulta para obtener los detalles del comprovante
    $query = "SELECT id_comprovante, nombre_comprovante FROM TipoComprovante WHERE id_comprovante = $1";
    $result = pg_query_params($conexion, $query, array($id_comprovante));

    if ($result) {
        // Si se encuentra el comprovante, devolver los detalles en formato JSON
        $comprovante = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($comprovante);
    } else {
        // Si no se encuentra el comprovante, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Comprovante no encontrado'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de comprovante no válido'));
}
?>
