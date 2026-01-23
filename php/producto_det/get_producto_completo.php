<?php
include 'conexion_bi.php';

// Verificar si se envió el código de barras
if (isset($_GET['codigo_barra'])) {
    $codigo_barra = $_GET['codigo_barra'];


    

    // Consulta para obtener los datos de la tabla Producto
    $queryProducto = "SELECT id_pro, nombre_pro, uni_caja_pro FROM Producto WHERE codigo_barra_pro = $1";
    $resultProducto = pg_query_params($conexion, $queryProducto, array($codigo_barra));

    // Consulta para obtener los datos de la tabla ProductoDetalle
    $queryDetalle = "SELECT cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro, porcen_pro, 
                            precio1_pro, precio2_pro, precio3_pro 
                     FROM ProductoDetalle WHERE codigo_barra_pro = $1";
    $resultDetalle = pg_query_params($conexion, $queryDetalle, array($codigo_barra));

    header('Content-Type: application/json');

    if ($producto = pg_fetch_assoc($resultProducto)) {
        // Si el producto existe, obtener también el detalle
        $detalle = pg_fetch_assoc($resultDetalle);

        echo json_encode([
            'existe' => true,
            'id_pro' => $producto['id_pro'],
            'nombre_pro' => $producto['nombre_pro'],
            'uni_caja_pro' => $producto['uni_caja_pro'],
            'cantidad_caja_pro' => $detalle['cantidad_caja_pro'] ?? '',
            'cantidad_uni_pro' => $detalle['cantidad_uni_pro'] ?? '',
            'costo_caja_pro' => $detalle['costo_caja_pro'] ?? '',
            'costo_uni_pro' => $detalle['costo_uni_pro'] ?? '',
            'porcen_pro' => $detalle['porcen_pro'] ?? '',
            'precio1_pro' => $detalle['precio1_pro'] ?? '',
            'precio2_pro' => $detalle['precio2_pro'] ?? '',
            'precio3_pro' => $detalle['precio3_pro'] ?? 0 // Si está vacío, lo pone en 0
        ]);
    } else {
        // Si el producto no existe
        echo json_encode(['existe' => false, 'error' => 'Producto no encontrado']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Código de barra no proporcionado']);
}

pg_close($conexion);
?>
