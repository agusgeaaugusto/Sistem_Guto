<?php
// Incluye el archivo de conexión a la base de datos
include 'conexion_bi.php';

// Verifica si se proporciona un ID válido en la solicitud
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id']) && is_numeric($_GET['id'])) {
    // Obtiene el ID del proveedor desde la solicitud y lo limpia para evitar inyecciones SQL
    $id_proveedor = htmlspecialchars($_GET['id']);

    // Consulta para obtener los detalles del proveedor utilizando una consulta preparada para evitar inyecciones SQL
    $query = "SELECT id_proveedor, nombre_prove, ruc_prove, direccion_prove, telefono_prove FROM Proveedor WHERE id_proveedor = $1";
    $result = pg_query_params($conexion, $query, array($id_proveedor));

    if ($result) {
        // Verifica si se encontraron resultados
        if (pg_num_rows($result) > 0) {
            // Si se encuentra el proveedor, devuelve los detalles en formato JSON
            $proveedor = pg_fetch_assoc($result);
            header('Content-Type: application/json');
            echo json_encode($proveedor);
        } else {
            // Si no se encuentra el proveedor, devuelve un mensaje de error y establece el código de respuesta HTTP 404 (No encontrado)
            http_response_code(404);
            echo json_encode(array('error' => 'Proveedor no encontrado'));
        }
    } else {
        // Si ocurre algún error en la consulta, devuelve un mensaje de error y establece el código de respuesta HTTP 500 (Error interno del servidor)
        http_response_code(500);
        echo json_encode(array('error' => 'Error en la consulta de la base de datos'));
    }
} else {
    // Si no se proporciona un ID válido, devuelve un mensaje de error y establece el código de respuesta HTTP 400 (Solicitud incorrecta)
    http_response_code(400);
    echo json_encode(array('error' => 'ID de proveedor no válido'));
}

// Cierra la conexión a la base de datos
pg_close($conexion);
?>
