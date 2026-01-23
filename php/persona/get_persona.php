<?php
include __DIR__ . '/conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

if (!$conexion) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
  exit;
}

$id = $_GET['id'] ?? null;
if ($id === null || !is_numeric($id)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'ID de persona no válido.']);
  exit;
}

$sql = "SELECT id_per, nombre_per, cedula_per, direccion, telefono, correo
        FROM persona
        WHERE id_per = $1";
$res = pg_query_params($conexion, $sql, [$id]);

if (!$res) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . pg_last_error($conexion)]);
  exit;
}

if (pg_num_rows($res) === 0) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Persona no encontrada.']);
  exit;
}

echo json_encode(pg_fetch_assoc($res));
