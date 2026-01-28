<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

function validarID($id): bool { return isset($id) && is_numeric($id) && intval($id) > 0; }

if (isset($_GET['eliminar'])) {
  $id_mon = $_GET['eliminar'];

  if (!validarID($id_mon)) {
    echo json_encode(['success'=>false,'message'=>'ID de moneda no válido.']); exit;
  }

  $q = "DELETE FROM moneda WHERE id_mon = $1";
  $r = pg_query_params($conexion, $q, [$id_mon]);

  if (!$r) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Error DB: '.pg_last_error($conexion)]);
    exit;
  }

  echo json_encode(['success'=>true]);
  pg_close($conexion);
  exit;
}

echo json_encode(['success'=>false,'message'=>'Parámetro eliminar faltante.']);
pg_close($conexion);
