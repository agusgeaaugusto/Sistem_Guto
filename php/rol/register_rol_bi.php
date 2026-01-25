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

/**
 * Convierte "a,b, c" => {"a","b","c"} (array literal PostgreSQL para text[])
 * Evita problemas con espacios y comillas.
 */
function csv_to_pg_text_array(string $csv): string {
  $csv = trim($csv);
  if ($csv === '') return '{}';
  $parts = array_filter(array_map('trim', explode(',', $csv)), fn($x) => $x !== '');
  if (!$parts) return '{}';

  $escaped = array_map(function($v){
    // escapamos comillas y backslash en literal de array
    $v = str_replace(['\\','"'], ['\\\\','\\"'], $v);
    return '"' . $v . '"';
  }, $parts);

  return '{' . implode(',', $escaped) . '}';
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
  if ($method === 'POST') {
    $descripcion_rol = clean_str((string)($_POST['descripcion_rol'] ?? ''));
    $accesos_raw     = (string)($_POST['accesos_rol'] ?? '');
    $fecha_rol       = (string)($_POST['fecha_rol'] ?? '');

    if ($descripcion_rol === '' || mb_strlen($descripcion_rol) < 2) {
      json_out(['success'=>false,'message'=>'Descripción inválida (mínimo 2 caracteres).'], 422);
    }
    if ($fecha_rol === '') {
      json_out(['success'=>false,'message'=>'Fecha inválida.'], 422);
    }

    $creado_rol = date('Y-m-d');
    $accesos_rol = csv_to_pg_text_array($accesos_raw);

    // OJO: en PostgreSQL, Roles sin comillas => roles
    $sql = "INSERT INTO roles (descripcion_rol, accesos_rol, creado_rol, fecha_rol)
            VALUES ($1, $2::text[], $3::date, $4::date)
            RETURNING id_rol";
    $q = pg_query_params($conexion, $sql, [$descripcion_rol, $accesos_rol, $creado_rol, $fecha_rol]);
    if (!$q) {
      json_out(['success'=>false,'message'=>'Error al guardar rol: '.pg_last_error($conexion)], 500);
    }
    $row = pg_fetch_assoc($q) ?: [];
    json_out(['success'=>true,'id_rol'=> $row['id_rol'] ?? null]);
  }

  // GET: devolver listado JSON
  $sql = "SELECT id_rol, descripcion_rol,
                 accesos_rol::text AS accesos_rol,
                 creado_rol::text  AS creado_rol,
                 fecha_rol::text   AS fecha_rol
          FROM roles
          ORDER BY id_rol ASC";
  $q = pg_query($conexion, $sql);
  if (!$q) {
    json_out(['success'=>false,'message'=>'Error al listar roles: '.pg_last_error($conexion)], 500);
  }

  $data = [];
  while ($r = pg_fetch_assoc($q)) { $data[] = $r; }
  json_out($data);

} catch (Throwable $e) {
  json_out(['success'=>false,'message'=>'Excepción: '.$e->getMessage()], 500);
}
?>