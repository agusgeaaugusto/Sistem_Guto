<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cat = $_POST['id_cat'] ?? null;
    $nuevo_nombre = $_POST['nuevo_nombre_categoria'] ?? '';

    if (!validarID($id_cat)) {
        echo json_encode(['success' => false, 'message' => 'ID de categoría no válido.']);
        exit();
    }

    if (empty($nuevo_nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la categoría no puede estar vacío.']);
        exit();
    }

    // Consulta preparada
    $query = "UPDATE Categoria SET nombre_cat = $1 WHERE id_cat = $2";
    $result = pg_query_params($conexion, $query, [$nuevo_nombre, $id_cat]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría: ' . pg_last_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
