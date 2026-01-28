<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

function validarID($id): bool {
  return isset($id) && is_numeric($id) && intval($id) > 0;
}
function num4($v): ?string {
  if ($v === null) return null;
  $s = trim((string)$v);
  if ($s === '') return null;
  $s = str_replace([' ', ','], ['', '.'], $s);
  if (!is_numeric($s)) return null;
  return $s;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $id     = $_POST['id_mon'] ?? null;
  $real   = num4($_POST['real']  ?? null);
  $dolar  = num4($_POST['dolar'] ?? null);
  $estado = strtoupper(trim((string)($_POST['estado'] ?? 'ACTIVO')));

  if (!validarID($id) || $real === null || $dolar === null) {
    echo json_encode(['success'=>false,'message'=>'Datos inválidos o incompletos.']); exit;
  }
  if ($estado !== 'ACTIVO' && $estado !== 'INACTIVO') $estado = 'ACTIVO';

  $q = "UPDATE moneda SET real=$1, dolar=$2, estado=$3 WHERE id_mon=$4";
  $r = pg_query_params($conexion, $q, [$real, $dolar, $estado, $id]);

  if (!$r) {
    echo json_encode(['success'=>false,'message'=>'Error DB: '.pg_last_error($conexion)]); exit;
  }

  echo json_encode(['success'=>true,'message'=>'Cotización actualizada correctamente.']);
} else {
  echo json_encode(['success'=>false,'message'=>'Método no permitido.']);
}
pg_close($conexion);
