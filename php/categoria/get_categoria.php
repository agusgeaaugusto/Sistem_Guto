<?php
// Suponiendo que tienes una conexión a la base de datos establecida
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_categoria = $_GET['id'];

    // Consulta para obtener los detalles de la categoría
    $query = "SELECT id_cat, nombre_cat FROM Categoria WHERE id_cat = $1";
    $result = pg_query_params($conexion, $query, array($id_cat));

    if ($result) {
        // Si se encuentra la categoría, devolver los detalles en formato JSON
        $categoria = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($categoria);
    } else {
        // Si no se encuentra la categoría, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Categoría no encontrada'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de categoría no válido'));
}
?>