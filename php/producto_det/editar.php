<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id) && $id > 0;
}

// Función para validar datos numéricos
function validarNumerico($valor) {
    return isset($valor) && is_numeric($valor);
}

// Función para editar un producto detalle
function editarProductoDetalle($id, $idPro, $idCom, $codigoBarra, $cantidadCaja, $cantidadUni, $costoCaja, $costoUni, $precio1, $precio2, $precio3, $fechaVen, $porcenPro) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE ProductoDetalle SET 
            id_pro = $1, 
            id_com = $2,
            codigo_barra_pro = $3, 
            cantidad_caja_pro = $4, 
            cantidad_uni_pro = $5, 
            costo_caja_pro = $6, 
            costo_uni_pro = $7, 
            precio1_pro = $8, 
            precio2_pro = $9, 
            precio3_pro = $10,
            fecha_ven_pro = $11,
            porcen_pro = $12 
            WHERE id_det_pro = $13";

        $params = array($idPro, $idCom, $codigoBarra, $cantidadCaja, $cantidadUni, $costoCaja, $costoUni, $precio1, $precio2, $precio3, $fechaVen, $porcenPro, $id);
        $result = pg_query_params($conexion, $query, $params);

        if (!$result) {
            echo json_encode(array('success' => false, 'message' => 'Error en la consulta de actualización: ' . pg_last_error()));
            return;
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de producto detalle no válido.'));
    }
}

// Verificar si se proporciona un ID válido y los nuevos datos del producto detalle
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_det_pro'])) {
    $idProductoDetalleEditar = $_POST['id_det_pro'];
    $idPro = $_POST['id_pro'];
    $idCom = $_POST['id_com'];
    $codigoBarra = $_POST['codigo_barra_pro'];
    $cantidadCaja = $_POST['cantidad_caja_pro'];
    $cantidadUni = $_POST['cantidad_uni_pro'];
    $costoCaja = $_POST['costo_caja_pro'];
    $costoUni = $_POST['costo_uni_pro'];
    $precio1 = $_POST['precio1_pro'];
    $precio2 = $_POST['precio2_pro'];
    $precio3 = $_POST['precio3_pro'];
    $fechaVen = $_POST['fecha_ven_pro'];
    $porcenPro = $_POST['porcen_pro'];

    // Validar los datos
    if (!validarID($idPro) || !validarID($idCom)) {
        echo json_encode(array('success' => false, 'message' => 'ID de producto o ID de compra no válido.'));
    } elseif (!validarNumerico($cantidadCaja) || !validarNumerico($cantidadUni) || !validarNumerico($costoCaja) || !validarNumerico($costoUni)) {
        echo json_encode(array('success' => false, 'message' => 'Cantidad, costo o precios no son válidos.'));
    } else {
        // Editar el producto detalle con los nuevos datos
        editarProductoDetalle($idProductoDetalleEditar, $idPro, $idCom, $codigoBarra, $cantidadCaja, $cantidadUni, $costoCaja, $costoUni, $precio1, $precio2, $precio3, $fechaVen, $porcenPro);
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'ID de producto detalle o datos del producto no proporcionados.'));
}
?>
