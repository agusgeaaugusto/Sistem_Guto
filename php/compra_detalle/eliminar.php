<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para eliminar un detalle de compra
function eliminarCompraDetalle($id) {
    global $conexion;

    // Validar el ID antes de realizar la eliminación
    if (validarID($id)) {
        // Utilizar consultas preparadas para evitar inyecciones SQL
        $query = "DELETE FROM Compra_Detalle WHERE id_comp_det = $1";
        $result = pg_query_params($conexion, $query, array($id));

        if (!$result) {
            die("Error en la consulta de eliminación: " . pg_last_error());
        }

        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'message' => 'ID de detalle de compra no válido.'));
    }
}

// Verificar si se proporciona un ID en la URL para eliminar
if (isset($_GET['id'])) {
    $id_comp_det = $_GET['id'];
    eliminarCompraDetalle($id_comp_det);
}

// Cerrar conexión
pg_close($conexion);
?>
