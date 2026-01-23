<?php
include 'conexion_bi.php'; // Conexión con la base de datos

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['codigo_barra'])) {
    $codigo_barra = $_GET['codigo_barra'];

    // Buscar el producto en la tabla Producto
    $query = "SELECT * FROM Producto WHERE codigo_barra_pro = $1 LIMIT 1";
    $result = pg_query_params($conexion, $query, array($codigo_barra));
    $producto = pg_fetch_assoc($result);

    if ($producto) {
        $id_pro = $producto['id_pro'];

        // Buscar si ya tiene compras en ProductoDetalle
        $queryDetalle = "SELECT * FROM ProductoDetalle WHERE id_pro = $1 ORDER BY id_det_pro DESC LIMIT 1";
        $resultDetalle = pg_query_params($conexion, $queryDetalle, array($id_pro));
        $detalle = pg_fetch_assoc($resultDetalle);

        // Unir los datos del producto con el detalle (si existe)
        $response = [
            "id_pro" => $producto["id_pro"],
            "nombre_pro" => $producto["nombre_pro"],
            "codigo_barra_pro" => $producto["codigo_barra_pro"],
            "uni_caja_pro" => $producto["uni_caja_pro"],
            "precio1_pro" => $producto["precio1_pro"],
            "precio2_pro" => $producto["precio2_pro"],
            "precio3_pro" => $producto["precio3_pro"]
        ];

        if ($detalle) {
            $response += [
                "id_com" => $detalle["id_com"],
                "cantidad_caja_pro" => $detalle["cantidad_caja_pro"],
                "cantidad_uni_pro" => $detalle["cantidad_uni_pro"],
                "costo_caja_pro" => $detalle["costo_caja_pro"],
                "costo_uni_pro" => $detalle["costo_uni_pro"],
                "fecha_ven_pro" => $detalle["fecha_ven_pro"],
                "porcen_pro" => $detalle["porcen_pro"]
            ];
        }

        echo json_encode($response);
    } else {
        echo json_encode(["error" => "Producto no encontrado"]);
    }
}
pg_close($conexion);
?>
