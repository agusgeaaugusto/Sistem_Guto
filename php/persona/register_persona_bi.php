<?php
// register_persona_bi.php
// - GET  : lista personas (JSON)
// - POST : inserta persona (JSON)

include __DIR__ . '/conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

if (!$conexion) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos.']);
  exit;
}

// Helper
function clean($v){
  if ($v === null) return '';
  $v = trim((string)$v);
  // Evita basura de control
  $v = preg_replace('/[\x00-\x1F\x7F]/u', '', $v);
  return $v;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre_per = clean($_POST['nombre_per'] ?? '');
  $cedula_per = clean($_POST['cedula_per'] ?? '');
  $direccion  = clean($_POST['direccion']  ?? '');
  $telefono   = clean($_POST['telefono']   ?? '');
  $correo     = clean($_POST['correo']     ?? '');

  if ($nombre_per === '' || $cedula_per === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre y cédula son obligatorios.']);
    exit;
  }

  // Insert con todos los campos
  $sql = "INSERT INTO persona (nombre_per, cedula_per, direccion, telefono, correo)
          VALUES ($1, $2, $3, $4, $5)";

  $res = pg_query_params($conexion, $sql, [$nombre_per, $cedula_per, $direccion, $telefono, $correo]);

  if (!$res) {
    $err = pg_last_error($conexion);
    // Mensaje amigable para duplicado de cédula (si existe UNIQUE)
    if (stripos($err, 'duplicate key') !== false || stripos($err, 'unique') !== false) {
      http_response_code(409);
      echo json_encode(['success' => false, 'message' => 'Ya existe una persona con esa cédula.']);
      exit;
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $err]);
    exit;
  }

  echo json_encode(['success' => true]);
  exit;
}

// GET -> listar
$sql = "SELECT id_per, nombre_per, cedula_per, direccion, telefono, correo
        FROM persona
        ORDER BY id_per ASC";
$res = pg_query($conexion, $sql);
if (!$res) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error al listar: ' . pg_last_error($conexion)]);
  exit;
}

$rows = [];
while ($r = pg_fetch_assoc($res)) {
  $rows[] = $r;
}

echo json_encode($rows);
