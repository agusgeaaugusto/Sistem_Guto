<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';

function json_out(array $payload, int $code = 200): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

function clean_str(string $s): string {
  $s = trim($s);
  $s = preg_replace('/\s+/', ' ', $s) ?? $s;
  return $s;
}

function csv_to_pg_text_array(string $csv): string {
  $csv = trim($csv);
  if ($csv === '') return '{}';
  $parts = array_filter(array_map('trim', explode(',', $csv)), fn($x) => $x !== '');
  if (!$parts) return '{}';
  $escaped = array_map(function($v){
    $v = str_replace(['\\','"'], ['\\\\','\\"'], $v);
    return '"' . $v . '"';
  }, $parts);
  return '{' . implode(',', $escaped) . '}';
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  json_out(['success'=>false,'message'=>'Método no permitido'], 405);
}

$id = $_POST['id_rol'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
  json_out(['success'=>false,'message'=>'ID de rol no válido'], 400);
}

// Compatibilidad: el front manda nueva_descripcion_rol
$desc = isset($_POST['nueva_descripcion_rol']) ? clean_str((string)$_POST['nueva_descripcion_rol'])
       : (isset($_POST['descripcion_rol']) ? clean_str((string)$_POST['descripcion_rol']) : '');

$accesos_raw = isset($_POST['accesos_rol']) ? (string)$_POST['accesos_rol'] : null;
$fecha_rol   = isset($_POST['fecha_rol']) ? (string)$_POST['fecha_rol'] : null;

$set = [];
$params = [];
$i = 1;

if ($desc !== '') {
  if (mb_strlen($desc) < 2) json_out(['success'=>false,'message'=>'Descripción inválida (mínimo 2).'], 422);
  $set[] = "descripcion_rol = $" . $i;
  $params[] = $desc; $i++;
}

if ($accesos_raw !== null) {
  $set[] = "accesos_rol = $" . $i . "::text[]";
  $params[] = csv_to_pg_text_array($accesos_raw); $i++;
}

if ($fecha_rol !== null && $fecha_rol !== '') {
  $set[] = "fecha_rol = $" . $i . "::date";
  $params[] = $fecha_rol; $i++;
}

if (!$set) {
  json_out(['success'=>false,'message'=>'Nada para actualizar.'], 422);
}

$params[] = $id;
$sql = "UPDATE roles SET " . implode(', ', $set) . " WHERE id_rol = $" . $i;

$q = pg_query_params($conexion, $sql, $params);
if (!$q) json_out(['success'=>false,'message'=>'Error al actualizar: '.pg_last_error($conexion)], 500);

json_out(['success'=>true]);
?>