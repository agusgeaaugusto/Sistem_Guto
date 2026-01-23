<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion_bi.php';

$ruc       = trim($_POST['ruc'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$correo    = trim($_POST['correo'] ?? '');

if ($ruc === '' || $nombre === '') {
  http_response_code(400);
  echo json_encode(['success'=>false,'message'=>'Faltan datos: CI/RUC y Nombre son obligatorios']);
  exit;
}

/*
  Reglas:
  - Si existe persona con ese CI/RUC -> actualiza nombre y SOLO actualiza campos opcionales si vienen con algo.
  - Si no existe -> inserta con opcionales (pueden venir vacíos).
*/
$sel = pg_query_params($conexion,
  "SELECT id_per, nombre_per, cedula_per, direccion, telefono, correo
     FROM persona
    WHERE cedula_per = $1
    LIMIT 1",
  [$ruc]
);

if ($sel && pg_num_rows($sel) > 0) {
  $row = pg_fetch_assoc($sel);

  // UPDATE: nombre siempre; opcionales solo si llegan con valor
  $upd = pg_query_params($conexion,
    "UPDATE persona
        SET nombre_per = $1,
            direccion  = COALESCE(NULLIF($2,''), direccion),
            telefono   = COALESCE(NULLIF($3,''), telefono),
            correo     = COALESCE(NULLIF($4,''), correo)
      WHERE id_per = $5
  RETURNING id_per, nombre_per, cedula_per, direccion, telefono, correo",
    [$nombre, $direccion, $telefono, $correo, $row['id_per']]
  );

  if (!$upd) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'No se pudo actualizar la persona']);
    pg_close($conexion);
    exit;
  }

  $out = pg_fetch_assoc($upd);
  echo json_encode(['success'=>true,'data'=>[
    'id_persona' => $out['id_per'],
    'ruc_ci'     => $out['cedula_per'],
    'nombre'     => $out['nombre_per'],
    'direccion'  => $out['direccion'] ?? '',
    'telefono'   => $out['telefono'] ?? '',
    'correo'     => $out['correo'] ?? ''
  ]]);

  pg_close($conexion);
  exit;
}

$ins = pg_query_params($conexion,
  "INSERT INTO persona (cedula_per, nombre_per, direccion, telefono, correo)
   VALUES ($1, $2, NULLIF($3,''), NULLIF($4,''), NULLIF($5,''))
   RETURNING id_per, nombre_per, cedula_per, direccion, telefono, correo",
  [$ruc, $nombre, $direccion, $telefono, $correo]
);

if (!$ins) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'No se pudo registrar la persona']);
  pg_close($conexion);
  exit;
}

$out = pg_fetch_assoc($ins);
echo json_encode(['success'=>true,'data'=>[
  'id_persona' => $out['id_per'],
  'ruc_ci'     => $out['cedula_per'],
  'nombre'     => $out['nombre_per'],
  'direccion'  => $out['direccion'] ?? '',
  'telefono'   => $out['telefono'] ?? '',
  'correo'     => $out['correo'] ?? ''
]]);
pg_close($conexion);
