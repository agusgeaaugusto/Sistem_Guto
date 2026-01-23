<?php
include 'conexion_bi.php';

function validarID($id) {
    return isset($id) && is_numeric($id);
}

function eliminarMoneda($id) {
    global $conexion;

    if (validarID($id)) {
        $query = "DELETE FROM Moneda WHERE id_mon = $1";
        $result = pg_query_params($conexion, $query, array($id));

        if (!$result) {
            die("Error en la consulta de eliminación: " . pg_last_error());
        }

        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'ID de moneda no válido.'));
    }
}

if (isset($_GET['eliminar'])) {
    $id_mon = $_GET['eliminar'];
    eliminarMoneda($id_mon);
}
?>
