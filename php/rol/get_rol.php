<?php
// Suponiendo que tienes una conexión a la base de datos establecida
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_rol = $_GET['id'];

    // Consulta para obtener los detalles del rol
    $query = "SELECT id_rol, descripcion_rol, accesos_rol, creado_rol, fecha_rol FROM Roles WHERE id_rol = $1";
    $result = pg_query_params($conexion, $query, array($id_rol));

    if ($result) {
        // Si se encuentra el rol, devolver los detalles en formato JSON
        $rol = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($rol);
    } else {
        // Si no se encuentra el rol, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Rol no encontrado'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de rol no válido'));
}
?>
