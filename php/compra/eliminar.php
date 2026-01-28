<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

function jsonOut($arr, int $code=200): void {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

function validarID($id): bool { return isset($id) && is_numeric($id) && intval($id) > 0; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  jsonOut(['success'=>false,'message'=>'Método no permitido.'], 405);
}

$id_com = $_POST['id_com'] ?? null;
if (!validarID($id_com)) {
  jsonOut(['success'=>false,'message'=>'ID de compra no válido.'], 422);
}

$r = pg_query_params($conexion, "DELETE FROM compra WHERE id_com=$1", [(int)$id_com]);
if(!$r){
  jsonOut(['success'=>false,'message'=>'Error DB: '.pg_last_error($conexion)], 500);
}

jsonOut(['success'=>true]);
