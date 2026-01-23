<?php
require_once 'conexion_bi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id_pro'] ?? null;
    $favorito = $_POST['favorito'] ?? null;

    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }

    $query = "UPDATE producto SET favorito = $1 WHERE id_pro = $2";
    $result = pg_query_params($conexion, $query, [$favorito, $id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => pg_last_error()]);
    }

    pg_close($conexion);
}
?>
