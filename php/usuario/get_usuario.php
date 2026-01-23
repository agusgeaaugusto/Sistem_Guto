<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

if (!$conexion) {
  http_response_code(500);
  echo json_encode(['error'=>'Sin conexión a la base de datos']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
  http_response_code(400);
  echo json_encode(['error'=>'ID de usuario no válido']);
  exit;
}

$id = $_GET['id'];
if (!filter_var($id, FILTER_VALIDATE_INT)) {
  http_response_code(400);
  echo json_encode(['error'=>'ID inválido']);
  exit;
}

$q = "SELECT id_usu, nombre_usu, estado_usu,
             to_char(fecha_creado_usu,'YYYY-MM-DD') AS fecha_creado_usu,
             to_char(fecha_actualiza_usu,'YYYY-MM-DD') AS fecha_actualiza_usu,
             id_rol
      FROM public.usuario
      WHERE id_usu = $1";
$r = pg_query_params($conexion, $q, [$id]);

if (!$r) {
  http_response_code(500);
  echo json_encode(['error'=>'Error en consulta: '.pg_last_error($conexion)]);
  exit;
}

$row = pg_fetch_assoc($r);
if (!$row) {
  http_response_code(404);
  echo json_encode(['error'=>'Usuario no encontrado']);
  exit;
}

echo json_encode($row);
