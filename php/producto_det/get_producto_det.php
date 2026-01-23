<?php
include 'conexion_bi.php';

// Verificar si la conexión a la base de datos es válida
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Verificar si la solicitud es GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Obtener producto por código de barras
    if (isset($_GET['codigo_barra'])) { // Corregido para coincidir con el AJAX
        $codigo_barra = trim($_GET['codigo_barra']);

        if (empty($codigo_barra)) {
            http_response_code(400);
            echo json_encode(['error' => 'Código de barras no válido']);
            exit();
        }

        // Consulta segura
        $query = "SELECT * FROM ProductoDetalle WHERE codigo_barra_pro = $1";
        $result = pg_query_params($conexion, $query, array($codigo_barra));

        header('Content-Type: application/json');

        if ($productoDetalle = pg_fetch_assoc($result)) {
            echo json_encode([
                'existe' => true,  // Se agrega para que el JS pueda validarlo
                'id_pro' => $productoDetalle['id_pro'],
                'nombre_pro' => $productoDetalle['nombre_pro'],
                'uni_caja_pro' => $productoDetalle['uni_caja_pro'],
                'cantidad_caja_pro' => $productoDetalle['cantidad_caja_pro'],
                'cantidad_uni_pro' => $productoDetalle['cantidad_uni_pro'],
                'costo_caja_pro' => $productoDetalle['costo_caja_pro'],
                'costo_uni_pro' => $productoDetalle['costo_uni_pro'],
                'porcen_pro' => $productoDetalle['porcen_pro'],
                'precio1_pro' => $productoDetalle['precio1_pro'],
                'precio2_pro' => $productoDetalle['precio2_pro'],
                'precio3_pro' => $productoDetalle['precio3_pro']
            ]);
        } else {
            echo json_encode(['existe' => false, 'error' => 'Producto detalle no encontrado']);
        }

        pg_close($conexion);
        exit();
    }

    // Si no se envió un parámetro válido
    http_response_code(400);
    echo json_encode(['error' => 'Parámetro no válido']);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método de solicitud no permitido']);
}

pg_close($conexion);
?>
