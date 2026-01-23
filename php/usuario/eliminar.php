<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

function jerror($msg, $code=400){
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg]);
  exit;
}

if (!$conexion) jerror('Sin conexión a la base de datos', 500);

$id = $_GET['eliminar'] ?? ($_GET['id'] ?? null);
if (!filter_var($id, FILTER_VALIDATE_INT)) jerror('ID inválido');

$q = "DELETE FROM public.usuario WHERE id_usu = $1";
$r = pg_query_params($conexion, $q, [$id]);

if(!$r) jerror('Error al eliminar: '.pg_last_error($conexion), 500);

echo json_encode(['success'=>true]);
