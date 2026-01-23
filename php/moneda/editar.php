<?php
require_once 'conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Verifica que el ID sea válido
 */
function validarID($id) {
    return isset($id) && is_numeric($id) && intval($id) > 0;
}

/**
 * Actualiza el registro de la moneda
 */
function editarMoneda($id, $nuevoNombre, $nuevaTasa) {
    global $conexion;

    $query = "UPDATE Moneda SET nombre_mon = $1, tasa_cambio = $2 WHERE id_mon = $3";
    $result = pg_query_params($conexion, $query, [$nuevoNombre, $nuevaTasa, $id]);

    if (!$result) {
        return ['success' => false, 'message' => 'Error en la consulta: ' . pg_last_error($conexion)];
    }

    return ['success' => true, 'message' => 'Moneda actualizada correctamente.'];
}

/**
 * Punto de entrada principal
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id     = $_POST['id_mon'] ?? null;
    $nombre = trim($_POST['nombre_mon'] ?? '');
    $tasa   = trim($_POST['tasa_cambio'] ?? '');

    if (!validarID($id) || empty($nombre) || !is_numeric($tasa)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos o incompletos. Verifica los campos.'
        ]);
        exit;
    }

    $respuesta = editarMoneda($id, $nombre, $tasa);
    echo json_encode($respuesta);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

pg_close($conexion);
?>
