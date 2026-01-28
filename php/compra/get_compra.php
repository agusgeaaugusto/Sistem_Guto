<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

$id = $_GET['id'] ?? null;
if ($id === null || $id === '' || !is_numeric($id)) {
  http_response_code(422);
  echo json_encode(['success'=>false,'message'=>'ID inválido']);
  exit;
}

$r = pg_query_params($conexion, "SELECT * FROM compra WHERE id_com=$1 LIMIT 1", [(int)$id]);
if(!$r){
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Error DB: '.pg_last_error($conexion)]);
  exit;
}
$row = pg_fetch_assoc($r);
if(!$row){
  http_response_code(404);
  echo json_encode(['success'=>false,'message'=>'Compra no encontrada']);
  exit;
}
echo json_encode(['success'=>true,'compra'=>$row], JSON_UNESCAPED_UNICODE);
