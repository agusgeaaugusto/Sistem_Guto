<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion_bi.php';

$ruc = trim($_POST['ruc'] ?? '');
if ($ruc === '') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Falta CI/RUC']); exit; }

$q = "SELECT id_per, nombre_per, cedula_per, direccion, telefono, correo
      FROM persona
      WHERE cedula_per = $1
      LIMIT 1";
$res = pg_query_params($conexion, $q, [$ruc]);

if ($res && pg_num_rows($res) > 0) {
  $row = pg_fetch_assoc($res);
  echo json_encode([
    'success' => true,
    'found' => true,
    'data' => [
      'id_persona' => $row['id_per'],
      'ruc_ci'     => $row['cedula_per'],
      'nombre'     => $row['nombre_per'],
        'direccion'  => $row['direccion'] ?? '',
        'telefono'   => $row['telefono'] ?? '',
        'correo'     => $row['correo'] ?? ''
      ]
  ]);
  pg_close($conexion); exit;
}

echo json_encode(['success'=>true,'found'=>false]);
pg_close($conexion);
