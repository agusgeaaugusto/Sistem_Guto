<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

function jerror($msg, $code=400){
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg]);
  exit;
}

if (!$conexion) jerror('Sin conexión a la base de datos', 500);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jerror('Método no permitido', 405);

$id     = $_POST['id_usu'] ?? null;
$nombre = trim($_POST['nombre_usu'] ?? '');
$clave  = trim($_POST['clave_usu'] ?? ''); // opcional
$estado = isset($_POST['estado_usu']) ? (int)$_POST['estado_usu'] : 1;
$id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;

if (!filter_var($id, FILTER_VALIDATE_INT)) jerror('ID inválido');
if ($nombre === '' || $id_rol <= 0) jerror('Completa usuario e id_rol');

if ($clave !== '') {
  $hash = $clave; // texto plano (compatibilidad)
  $q = "UPDATE public.usuario
        SET nombre_usu=$1, clave_usu=$2, estado_usu=$3, fecha_actualiza_usu=CURRENT_DATE, id_rol=$4
        WHERE id_usu=$5";
  $r = pg_query_params($conexion, $q, [$nombre, $hash, $estado, $id_rol, $id]);
} else {
  $q = "UPDATE public.usuario
        SET nombre_usu=$1, estado_usu=$2, fecha_actualiza_usu=CURRENT_DATE, id_rol=$3
        WHERE id_usu=$4";
  $r = pg_query_params($conexion, $q, [$nombre, $estado, $id_rol, $id]);
}

if(!$r) jerror('Error al actualizar: '.pg_last_error($conexion), 500);

echo json_encode(['success'=>true]);
