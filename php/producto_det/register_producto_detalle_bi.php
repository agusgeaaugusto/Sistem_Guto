<?php
include 'conexion_bi.php'; // Conexión con la base de datos

// Función para redirigir después de la operación
function redireccionar() {
    header("Location: register_producto_detalle_bi.php");
    exit();
}

// Función para verificar si un ID de compra existe en la base de datos
function verificarCompra($conexion, $id_com) {
    $query = "SELECT id_com FROM Compra WHERE id_com = $1";
    $result = pg_query_params($conexion, $query, array($id_com));
    return pg_num_rows($result) > 0;
}

// Manejo de solicitudes POST (inserción o actualización de productos detalle)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos del formulario
    $id_pro = isset($_POST['id_pro']) ? (int)$_POST['id_pro'] : null;
    $id_com = isset($_POST['id_com']) ? (int)$_POST['id_com'] : null;
    $codigo_barra_pro = isset($_POST['codigo_barra_pro']) ? htmlspecialchars($_POST['codigo_barra_pro']) : '';
    $cantidad_caja_pro = isset($_POST['cantidad_caja_pro']) ? (int)$_POST['cantidad_caja_pro'] : 0;
    $uni_caja_pro = isset($_POST['uni_caja_pro']) ? (int)$_POST['uni_caja_pro'] : 0;
    $costo_caja_pro = isset($_POST['costo_caja_pro']) ? (float)$_POST['costo_caja_pro'] : 0.0;
    $precio1_pro = isset($_POST['precio1_pro']) ? (float)$_POST['precio1_pro'] : 0.0;
    $precio2_pro = isset($_POST['precio2_pro']) ? (float)$_POST['precio2_pro'] : 0.0;
    $precio3_pro = isset($_POST['precio3_pro']) ? (float)$_POST['precio3_pro'] : 0.0;
    $porcen_pro = isset($_POST['porcen_pro']) ? (float)$_POST['porcen_pro'] : 0.0;
    $fecha_ven_pro = isset($_POST['fecha_ven_pro']) && !empty($_POST['fecha_ven_pro']) ? $_POST['fecha_ven_pro'] : null;

    // Calcular cantidad_uni_pro y costo_uni_pro
    $cantidad_uni_pro = $cantidad_caja_pro * $uni_caja_pro;
    $costo_uni_pro = ($uni_caja_pro > 0) ? $costo_caja_pro / $uni_caja_pro : 0.0;

    // Validar los campos obligatorios
    if ($id_pro === null || $id_com === null || empty($codigo_barra_pro) || $cantidad_caja_pro == 0 || $uni_caja_pro == 0) {
        echo json_encode(["success" => false, "error" => "Todos los campos son obligatorios"]);
        exit();
    }

    // Verificar si el ID de compra existe
    if (!verificarCompra($conexion, $id_com)) {
        echo json_encode(["success" => false, "error" => "El ID de compra no existe en la base de datos"]);
        exit();
    }

    // Verificar si el producto ya existe en ProductoDetalle
    $query = "SELECT * FROM ProductoDetalle WHERE codigo_barra_pro = $1 AND id_com = $2";
    $result = pg_query_params($conexion, $query, array($codigo_barra_pro, $id_com));

    if ($producto_existente = pg_fetch_assoc($result)) {
        // Actualizar el producto existente
        $nuevaCantidadCaja = $producto_existente['cantidad_caja_pro'] + $cantidad_caja_pro;
        $nuevaCantidadUni = $producto_existente['cantidad_uni_pro'] + $cantidad_uni_pro;

        $updateQuery = "UPDATE ProductoDetalle SET 
                        cantidad_caja_pro = $1, 
                        cantidad_uni_pro = $2, 
                        costo_caja_pro = $3, 
                        costo_uni_pro = $4, 
                        precio1_pro = $5, 
                        precio2_pro = $6, 
                        precio3_pro = $7, 
                        uni_caja_pro = $8,
                        fecha_ven_pro = $9,
                        porcen_pro = $10
                        WHERE codigo_barra_pro = $11 AND id_com = $12";
        $params = array(
            $nuevaCantidadCaja, 
            $nuevaCantidadUni, 
            $costo_caja_pro, 
            $costo_uni_pro, 
            $precio1_pro, 
            $precio2_pro, 
            $precio3_pro, 
            $uni_caja_pro, 
            $fecha_ven_pro, 
            $porcen_pro, 
            $codigo_barra_pro, 
            $id_com
        );
        pg_query_params($conexion, $updateQuery, $params);
    } else {
        // Insertar nuevo producto detalle
        $insertQuery = "INSERT INTO ProductoDetalle (id_pro, id_com, codigo_barra_pro, cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro, precio1_pro, precio2_pro, precio3_pro, uni_caja_pro, fecha_ven_pro, porcen_pro) 
                        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)";
        $params = array(
            $id_pro,
            $id_com,
            $codigo_barra_pro, 
            $cantidad_caja_pro, 
            $cantidad_uni_pro, 
            $costo_caja_pro, 
            $costo_uni_pro, 
            $precio1_pro, 
            $precio2_pro, 
            $precio3_pro, 
            $uni_caja_pro, 
            $fecha_ven_pro, 
            $porcen_pro
        );
        pg_query_params($conexion, $insertQuery, $params);
    }

    echo json_encode(["success" => true, "message" => "Producto detalle guardado correctamente"]);
    pg_close($conexion);
    exit();
}

// Obtener todos los productos detalle (solicitud GET para listado)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $query = "SELECT * FROM ProductoDetalle ORDER BY id_det_pro ASC";
    $result = pg_query($conexion, $query);

    if (!$result) {
        echo json_encode(["success" => false, "error" => pg_last_error()]);
        exit();
    }

    $productos = array();
    while ($row = pg_fetch_assoc($result)) {
        $productos[] = $row;
    }

    echo json_encode(["success" => true, "data" => $productos]);
    pg_close($conexion);
    exit();
}

// Obtener datos de un producto detalle por ID (solicitud GET)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id_det_pro'])) {
    $id_det_pro = (int)$_GET['id_det_pro'];
    $query = "SELECT * FROM ProductoDetalle WHERE id_det_pro = $1";
    $result = pg_query_params($conexion, $query, array($id_det_pro));

    if ($producto = pg_fetch_assoc($result)) {
        echo json_encode(["success" => true, "data" => $producto]);
    } else {
        echo json_encode(["success" => false, "error" => "Producto detalle no encontrado"]);
    }
    pg_close($conexion);
    exit();
}
?>
