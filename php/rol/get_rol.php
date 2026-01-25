<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';

function json_out(array $payload, int $code = 200): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

$id = $_GET['id'] ?? null;
if ($id === null || !ctype_digit((string)$id)) {
  json_out(['success'=>false,'error'=>'ID de rol no válido'], 400);
}

$sql = "SELECT id_rol, descripcion_rol,
               accesos_rol::text AS accesos_rol,
               creado_rol::text  AS creado_rol,
               fecha_rol::text   AS fecha_rol
        FROM roles
        WHERE id_rol = $1";
$q = pg_query_params($conexion, $sql, [$id]);

if (!$q) json_out(['success'=>false,'error'=>'Error DB: '.pg_last_error($conexion)], 500);

$rol = pg_fetch_assoc($q);
if (!$rol) json_out(['success'=>false,'error'=>'Rol no encontrado'], 404);

json_out($rol);
?>