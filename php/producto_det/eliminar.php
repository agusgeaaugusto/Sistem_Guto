<?php
include 'conexion_bi.php';

function validarID($id) {
    return isset($id) && is_numeric($id);
}

function redireccionar() {
    header("Location: register_producto_bi.php");
    exit();
}

function eliminarProductoDetalle($id) {
    global $conexion;

    if (validarID($id)) {
        $query = "DELETE FROM ProductoDetalle WHERE id_det_pro = $1";
        $result = pg_query_params($conexion, $query, array($id));

        if (!$result) {
            die("Error en la consulta de eliminación: " . pg_last_error());
        }

        redireccionar();
    } else {
        redireccionar();
    }
}

if (isset($_GET['eliminar'])) {
    $id_det_pro = $_GET['eliminar'];
    eliminarProductoDetalle($id_det_pro);
}
?>
