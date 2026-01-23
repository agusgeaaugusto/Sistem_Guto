<?php
include __DIR__ . '/conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

if (!$conexion) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
  exit;
}

$id = $_GET['id'] ?? ($_GET['eliminar'] ?? null);
if ($id === null || !filter_var($id, FILTER_VALIDATE_INT) || (int)$id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'ID de persona no válido.']);
  exit;
}

$sql = "DELETE FROM persona WHERE id_per = $1";
$res = pg_query_params($conexion, $sql, [$id]);

if (!$res) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . pg_last_error($conexion)]);
  exit;
}

echo json_encode(['success' => true]);
