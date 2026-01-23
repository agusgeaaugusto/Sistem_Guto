<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && filter_var($id, FILTER_VALIDATE_INT) !== false && $id > 0;
}

// Función para eliminar un proveedor
function eliminarProveedor($id) {
    global $conexion;

    // Validar el ID antes de realizar la eliminación
    if (validarID($id)) {
        // Utilizar consultas preparadas para evitar inyecciones SQL
        $query = "DELETE FROM Proveedor WHERE id_proveedor = $1";
        $result = pg_query_params($conexion, $query, array($id));

        if (!$result) {
            die("Error en la consulta de eliminación: " . pg_last_error($conexion));
        }

        // Retornar una respuesta JSON indicando éxito
        echo json_encode(array("success" => true));
    } else {
        // Retornar una respuesta JSON indicando error
        echo json_encode(array("success" => false, "message" => "ID de proveedor no válido"));
    }
}

// Verificar si se proporciona un ID en la URL para eliminar
if (isset($_GET['id'])) {
    // Escapar el valor del ID para evitar inyecciones de SQL
    $id_proveedor = intval($_GET['id']);
    eliminarProveedor($id_proveedor);
} else {
    // Retornar una respuesta JSON indicando error si no se proporciona un ID
    echo json_encode(array("success" => false, "message" => "No se proporcionó un ID de proveedor"));
}
?>
