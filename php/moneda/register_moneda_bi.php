<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

function num4($v): ?string {
  if ($v === null) return null;
  $s = trim((string)$v);
  if ($s === '') return null;
  // Permitir coma decimal
  $s = str_replace([' ', ','], ['', '.'], $s);
  if (!is_numeric($s)) return null;
  return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $real   = num4($_POST['real']  ?? null);
  $dolar  = num4($_POST['dolar'] ?? null);
  $estado = strtoupper(trim((string)($_POST['estado'] ?? 'ACTIVO')));

  if ($real === null || $dolar === null) {
    echo json_encode(['success'=>false,'message'=>'Real y Dólar son obligatorios y deben ser numéricos.']); exit;
  }
  if ($estado !== 'ACTIVO' && $estado !== 'INACTIVO') $estado = 'ACTIVO';

  $q = "INSERT INTO moneda (guarani, real, dolar, estado) VALUES (1.0000, $1, $2, $3)";
  $r = pg_query_params($conexion, $q, [$real, $dolar, $estado]);

  if (!$r) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Error DB: '.pg_last_error($conexion)]);
    exit;
  }

  echo json_encode(['success'=>true]);
  pg_close($conexion);
  exit;
}

// GET: listado
$q = "SELECT id_mon, guarani, real, dolar, estado, fecha_inicio
      FROM moneda
      ORDER BY (CASE WHEN estado='ACTIVO' THEN 0 ELSE 1 END) ASC, creado_en DESC, id_mon DESC;";
$r = pg_query($conexion, $q);
$data = [];
if ($r) {
  while ($row = pg_fetch_assoc($r)) { $data[] = $row; }
}
echo json_encode($data);
pg_close($conexion);
