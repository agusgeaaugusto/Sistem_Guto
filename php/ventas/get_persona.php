<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion_bi.php';

$doc = $_GET['doc'] ?? $_GET['ruc'] ?? $_GET['ci'] ?? '';
$doc = trim($doc);
if ($doc === '') { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Documento vacío']); exit; }

$sql = "SELECT id_per, nombre_per, cedula_per FROM persona WHERE cedula_per = $1 LIMIT 1";
$res = pg_query_params($conexion, $sql, [$doc]);
if (!$res) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Error en la consulta']); exit; }
if (pg_num_rows($res) === 0) { echo json_encode(['success'=>false,'message'=>'No encontrado']); exit; }

$row = pg_fetch_assoc($res);
echo json_encode(['success'=>true,'data'=>[
  'id_persona' => $row['id_per'],
  'ruc_ci'     => $row['cedula_per'],
  'nombre'     => $row['nombre_per'],
]]);
pg_close($conexion);
