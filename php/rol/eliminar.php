<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';

function json_out(array $payload, int $code = 200): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

$id = $_GET['eliminar'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
  json_out(['success'=>false,'message'=>'ID de rol no válido'], 400);
}

$sql = "DELETE FROM roles WHERE id_rol = $1";
$q = pg_query_params($conexion, $sql, [$id]);
if (!$q) json_out(['success'=>false,'message'=>'Error al eliminar: '.pg_last_error($conexion)], 500);

json_out(['success'=>true]);
?>