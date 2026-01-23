<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para redirigir a la página principal después de realizar una operación
function redireccionar() {
    header("Location: register_compra_bi.php");
    exit();
}

// Función para eliminar una compra
function eliminarCompra($id) {
    global $conexion;

    // Validar el ID antes de realizar la eliminación
    if (validarID($id)) {
        // Utilizar consultas preparadas para evitar inyecciones SQL
        $query = "DELETE FROM Compra WHERE id_com = $1";
        $result = pg_query_params($conexion, $query, array($id));

        if (!$result) {
            die("Error en la consulta de eliminación: " . pg_last_error());
        }

        redireccionar();
    } else {
        redireccionar();
    }
}

// Verificar si se proporciona un ID en la URL para eliminar
if (isset($_GET['eliminar'])) {
    $id_com = $_GET['eliminar'];
    eliminarCompra($id_com);
}
?>
