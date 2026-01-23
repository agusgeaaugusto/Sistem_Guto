<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../cargos/conexion_bi.php';
try {
  if (!isset($conexion) || !$conexion) throw new Exception('Sin conexión a la BD');
  $sql = "SELECT id_venta FROM public.venta ORDER BY id_venta DESC LIMIT 1";
  $rs = pg_query($conexion, $sql);
  if (!$rs) throw new Exception(pg_last_error($conexion));
  $row = pg_fetch_assoc($rs);
  if (!$row) { echo json_encode(['ok'=>false, 'msg'=>'No hay ventas registradas']); exit; }
  echo json_encode(['ok'=>true, 'id_venta'=>intval($row['id_venta'])], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}


