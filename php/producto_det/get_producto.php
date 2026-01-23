<?php
include 'conexion_bi.php';

// Verificar si se envió el código de barras
if (isset($_GET['codigo_barra'])) {
    $codigo_barra = $_GET['codigo_barra'];

    // Consulta segura con pg_query_params
    $query = "SELECT id_pro, nombre_pro, uni_caja_pro, codigo_barra_pro FROM Producto WHERE codigo_barra_pro = $1";
    $result = pg_query_params($conexion, $query, array($codigo_barra));

    header('Content-Type: application/json');

    if ($row = pg_fetch_assoc($result)) {
        echo json_encode([
            'existe' => true,  // Agregado para que el JavaScript pueda validar
            'id_pro' => $row['id_pro'],
            'nombre_pro' => $row['nombre_pro'],
            'uni_caja_pro' => $row['uni_caja_pro'],
            'codigo_barra_pro' => $row['codigo_barra_pro']
        ]);
    } else {
        echo json_encode(['existe' => false, 'error' => 'Producto no encontrado']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Código de barra no proporcionado']);
}

pg_close($conexion);
?>
