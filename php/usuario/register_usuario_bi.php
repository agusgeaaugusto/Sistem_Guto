<?php
// register_usuario_bi.php
// GET  -> lista usuarios (JSON)
// POST -> inserta usuario (JSON)

include 'conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

function jerror($msg, $code=400){
  http_response_code($code);
  echo json_encode(['success'=>false,'message'=>$msg]);
  exit;
}

if (!$conexion) {
  jerror('Sin conexión a la base de datos', 500);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $q = "SELECT id_usu, nombre_usu, estado_usu,
               to_char(fecha_creado_usu,'YYYY-MM-DD') AS fecha_creado_usu,
               to_char(fecha_actualiza_usu,'YYYY-MM-DD') AS fecha_actualiza_usu,
               id_rol
        FROM public.usuario
        ORDER BY id_usu DESC";
  $r = pg_query($conexion, $q);
  if(!$r) jerror('Error al listar: '.pg_last_error($conexion), 500);

  $out = [];
  while($row = pg_fetch_assoc($r)){
    $out[] = $row;
  }
  echo json_encode($out);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre_usu'] ?? '');
  $clave  = trim($_POST['clave_usu'] ?? '');
  $estado = isset($_POST['estado_usu']) ? (int)$_POST['estado_usu'] : 1;
  $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;

  if ($nombre === '' || $clave === '' || $id_rol <= 0) {
    jerror('Completa usuario, clave e id_rol');
  }

  // Hash seguro
  $hash = password_hash($clave, PASSWORD_DEFAULT);

  // Si id_usu no es SERIAL, calculamos el próximo ID
  $nextId = null;
  $resId = pg_query($conexion, "SELECT COALESCE(MAX(id_usu),0)+1 AS next_id FROM public.usuario");
  if ($resId) {
    $row = pg_fetch_assoc($resId);
    $nextId = (int)($row['next_id'] ?? 0);
  }

  // Intento con DEFAULT (si tu tabla ya tiene SERIAL/IDENTITY)
  // Si falla por NOT NULL sin default, usamos $nextId
  $sqlDefault = "INSERT INTO public.usuario (nombre_usu, clave_usu, estado_usu, fecha_creado_usu, fecha_actualiza_usu, id_rol)
                 VALUES ($1,$2,$3,CURRENT_DATE,CURRENT_DATE,$4)";
  $ok = @pg_query_params($conexion, $sqlDefault, [$nombre, $hash, $estado, $id_rol]);

  if(!$ok){
    // fallback con id_usu manual
    if(!$nextId) jerror('No se pudo calcular id_usu', 500);

    $sql = "INSERT INTO public.usuario (id_usu, nombre_usu, clave_usu, estado_usu, fecha_creado_usu, fecha_actualiza_usu, id_rol)
            VALUES ($1,$2,$3,$4,CURRENT_DATE,CURRENT_DATE,$5)";
    $r = pg_query_params($conexion, $sql, [$nextId, $nombre, $hash, $estado, $id_rol]);
    if(!$r){
      jerror('Error al insertar: '.pg_last_error($conexion), 500);
    }
  }

  echo json_encode(['success'=>true]);
  exit;
}

jerror('Método no soportado', 405);
