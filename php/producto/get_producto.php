<?php
// Suponiendo que tienes una conexión a la base de datos establecida
include 'conexion_bi.php';

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $id_pro = $_GET['id'];

    // Consulta para obtener los detalles del producto
    $query = "SELECT id_pro, nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat FROM Producto WHERE id_pro = $1";
    $result = pg_query_params($conexion, $query, array($id_pro));

    if ($result && pg_num_rows($result) > 0) {
        // Si se encuentra el producto, devolver los detalles en formato JSON
        $producto = pg_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($producto);
    } else {
        // Si no se encuentra el producto, devolver un mensaje de error
        http_response_code(404);
        echo json_encode(array('error' => 'Producto no encontrado'));
    }
} else {
    // Si no se proporciona un ID válido, devolver un mensaje de error
    http_response_code(400);
    echo json_encode(array('error' => 'ID de producto no válido'));
}
?>
