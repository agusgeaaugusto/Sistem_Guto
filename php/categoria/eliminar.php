<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para eliminar una categoría
function eliminarCategoria($id) {
    global $conexion;

    if (!validarID($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de categoría no válido.']);
        exit();
    }

    // Consulta preparada para evitar inyecciones SQL
    $query = "DELETE FROM Categoria WHERE id_cat = $1";
    $result = pg_query_params($conexion, $query, [$id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría: ' . pg_last_error($conexion)]);
    }
    exit();
}

// Verificar si se proporciona un ID válido en la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria = $_POST['id_cat'] ?? null;

    eliminarCategoria($id_categoria);
} else {
    // Responder con un error si el método HTTP no es POST
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
