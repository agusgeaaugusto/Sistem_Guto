<?php
// Suponiendo que tienes una conexión a la base de datos establecida
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_cargo = $_GET['id'];

    // Consulta para obtener los detalles del cargo
    $query = "SELECT id_cargo, nombre_cargo FROM Cargo WHERE id_cargo = $1";
    $result = pg_query_params($conexion, $query, array($id_cargo));

    if ($result) {
        // Si se encuentra el cargo, devolver los detalles en formato JSON
        $cargo = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($cargo);
    } else {
        // Si no se encuentra el cargo, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Cargo no encontrado'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de cargo no válido'));
}
?>
