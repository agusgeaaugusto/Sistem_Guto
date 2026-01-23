<?php
include __DIR__ . '/conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

if (!$conexion) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
  exit;
}

function clean($v){
  if ($v === null) return '';
  $v = trim((string)$v);
  $v = preg_replace('/[\x00-\x1F\x7F]/u', '', $v);
  return $v;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
  exit;
}

$id = $_POST['id_per'] ?? null;
if ($id === null || !is_numeric($id)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'ID de persona no válido.']);
  exit;
}

// Acepta nombres nuevos y legacy (por si tu JS viejo manda otra key)
$nombre  = clean($_POST['nuevo_nombre_per'] ?? $_POST['nombre_per'] ?? '');
$cedula  = clean($_POST['nuevo_cedula_per'] ?? $_POST['nueva_cedula_per'] ?? $_POST['cedula_per'] ?? '');
$direccion = clean($_POST['nueva_direccion'] ?? $_POST['direccion'] ?? '');
$telefono  = clean($_POST['nuevo_telefono'] ?? $_POST['telefono'] ?? '');
$correo    = clean($_POST['nuevo_correo'] ?? $_POST['correo'] ?? '');

if ($nombre === '' || $cedula === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Nombre y cédula son obligatorios.']);
  exit;
}

$sql = "UPDATE persona
        SET nombre_per=$1,
            cedula_per=$2,
            direccion=$3,
            telefono=$4,
            correo=$5
        WHERE id_per=$6";

$res = pg_query_params($conexion, $sql, [$nombre, $cedula, $direccion, $telefono, $correo, $id]);

if (!$res) {
  $err = pg_last_error($conexion);
  if (stripos($err, 'duplicate key') !== false || stripos($err, 'unique') !== false) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Esa cédula ya está registrada por otra persona.']);
    exit;
  }
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $err]);
  exit;
}

echo json_encode(['success' => true]);
