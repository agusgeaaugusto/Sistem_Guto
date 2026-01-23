<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar una compra
function editarCompra($id_com, $fecha_com, $id_proveedor) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id_com)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE Compra SET fecha_com = $1, id_proveedor = $2 WHERE id_com = $3";
        $result = pg_query_params($conexion, $query, array($fecha_com, $id_proveedor, $id_com));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error());
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de compra no válido.'));
    }
}

// Verificar si se proporciona un ID válido y los nuevos datos
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_com']) && isset($_POST['fecha_com']) && isset($_POST['id_proveedor'])) {
    $id_com = $_POST['id_com'];
    $fecha_com = $_POST['fecha_com'];
    $id_proveedor = $_POST['id_proveedor'];

    // Editar la compra con los nuevos datos
    editarCompra($id_com, $fecha_com, $id_proveedor);
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o los nuevos datos
    echo json_encode(array('success' => false, 'message' => 'Datos de la compra no proporcionados.'));
}
?>
