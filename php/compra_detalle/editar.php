<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar un detalle de compra
function editarCompraDetalle($id_comp_det, $subtotal_comp_det, $id_com, $id_pro) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id_comp_det)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE Compra_Detalle SET subtotal_comp_det = $1, id_com = $2, id_pro = $3 WHERE id_comp_det = $4";
        $result = pg_query_params($conexion, $query, array($subtotal_comp_det, $id_com, $id_pro, $id_comp_det));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error());
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de detalle de compra no válido.'));
    }
}

// Verificar si se proporciona un ID válido y los nuevos datos
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_comp_det']) && isset($_POST['subtotal_comp_det']) && isset($_POST['id_com']) && isset($_POST['id_pro'])) {
    $id_comp_det = $_POST['id_comp_det'];
    $subtotal_comp_det = $_POST['subtotal_comp_det'];
    $id_com = $_POST['id_com'];
    $id_pro = $_POST['id_pro'];

    // Editar el detalle de compra con los nuevos datos
    editarCompraDetalle($id_comp_det, $subtotal_comp_det, $id_com, $id_pro);
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o los nuevos datos
    echo json_encode(array('success' => false, 'message' => 'Datos del detalle de compra no proporcionados.'));
}

// Cerrar conexión
pg_close($conexion);
?>
