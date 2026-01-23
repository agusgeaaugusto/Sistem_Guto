<?php
include 'conexion_bi.php';
header('Content-Type: application/json');

// ✅ CORREGIDO: ahora espera 'codigo' (no 'codigo_barra')
$codigo_barra = $_GET['codigo'] ?? '';

if (!$codigo_barra) {
    echo json_encode(["error" => "Código de barra vacío"]);
    exit();
}

// Buscar el producto por código de barra en la tabla producto
$query = "SELECT * FROM producto WHERE codigo_barra_pro = $1 LIMIT 1";
$result = pg_query_params($conexion, $query, [$codigo_barra]);
$producto = pg_fetch_assoc($result);

if (!$producto) {
    echo json_encode(["error" => "Producto no encontrado"]);
    exit();
}

// Buscar el último detalle del producto en productodetalle
$id_pro = $producto['id_pro'];
$queryDetalle = "SELECT * FROM productodetalle WHERE id_pro = $1 ORDER BY id_det_pro DESC LIMIT 1";
$resultDetalle = pg_query_params($conexion, $queryDetalle, [$id_pro]);
$detalle = pg_fetch_assoc($resultDetalle);

// Combinar datos del producto y detalle
$response = [
    "id_pro"           => $producto["id_pro"],
    "nombre_pro"       => $producto["nombre_pro"],
    "codigo_barra_pro" => $producto["codigo_barra_pro"],
    "imagen_pro"       => $producto["imagen_pro"],
    "uni_caja_pro"     => intval($producto["uni_caja_pro"])
];

// Si hay detalle, agregamos datos de precios y stock
if ($detalle) {
    $response += [
        "precio1_pro"      => floatval($detalle["precio1_pro"]),
        "precio2_pro"      => floatval($detalle["precio2_pro"]),
        "precio3_pro"      => floatval($detalle["precio3_pro"]),
        "porcen_pro"       => floatval($detalle["porcen_pro"]),
        "cantidad_uni_pro" => intval($detalle["cantidad_uni_pro"]),
        "costo_uni_pro"    => floatval($detalle["costo_uni_pro"]),
        "fecha_ven_pro"    => $detalle["fecha_ven_pro"]
    ];
} else {
    // Si no hay detalle, devolver precios en cero
    $response += [
        "precio1_pro" => 0,
        "precio2_pro" => 0,
        "precio3_pro" => 0
    ];
}

echo json_encode($response);
pg_close($conexion);
