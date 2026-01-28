<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=UTF-8');

// =========================================================
// API ProductoDetalle
// - GET  : lista (filtra por id_com opcional) o detalle por id_det_pro
//          /register_producto_detalle_bi.php?id_com=12
//          /register_producto_detalle_bi.php?id_det_pro=5
// - POST : crear/actualizar (por codigo_barra_pro + id_com)
// - POST : eliminar (action=delete&id_det_pro=5)
// =========================================================

function out($arr, int $code = 200){
  http_response_code($code);
  echo json_encode($arr);
  exit;
}

function requireInt($key){
  if(!isset($_REQUEST[$key]) || $_REQUEST[$key] === '') return null;
  if(!is_numeric($_REQUEST[$key])) return null;
  return (int)$_REQUEST[$key];
}

function verificarCompra($cn, int $id_com): bool {
  $q = pg_query_params($cn, "SELECT 1 FROM compra WHERE id_com=$1 LIMIT 1", [$id_com]);
  return $q && pg_num_rows($q) > 0;
}

if(!$conexion){ out(['success'=>false,'error'=>'Sin conexión DB'], 500); }

// -------------------- GET --------------------
if($_SERVER['REQUEST_METHOD'] === 'GET'){
  // 1) Traer un detalle por id
  $id_det_pro = requireInt('id_det_pro');
  if($id_det_pro){
    $q = "SELECT * FROM productodetalle WHERE id_det_pro=$1";
    $r = pg_query_params($conexion, $q, [$id_det_pro]);
    if($r && ($row = pg_fetch_assoc($r))){
      out(['success'=>true,'data'=>$row]);
    }
    out(['success'=>false,'error'=>'Producto detalle no encontrado'], 404);
  }

  // 2) Listar (por compra si viene)
  $id_com = requireInt('id_com');
  if($id_com){
    $q = "SELECT * FROM productodetalle WHERE id_com=$1 ORDER BY id_det_pro DESC";
    $r = pg_query_params($conexion, $q, [$id_com]);
  } else {
    $q = "SELECT * FROM productodetalle ORDER BY id_det_pro DESC";
    $r = pg_query($conexion, $q);
  }

  if(!$r){ out(['success'=>false,'error'=>pg_last_error($conexion)], 500); }

  $data = [];
  while($row = pg_fetch_assoc($r)){
    // normalizar fecha
    if(isset($row['fecha_ven_pro']) && $row['fecha_ven_pro'] !== null){
      $row['fecha_ven_pro'] = substr((string)$row['fecha_ven_pro'], 0, 10);
    }
    $data[] = $row;
  }
  out(['success'=>true,'data'=>$data]);
}

// -------------------- POST --------------------
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  // Eliminar
  $action = isset($_POST['action']) ? (string)$_POST['action'] : '';
  $id_det_pro = requireInt('id_det_pro');
  if(($action === 'delete' || ($id_det_pro && $action === '')) && $id_det_pro){
    $r = pg_query_params($conexion, "DELETE FROM productodetalle WHERE id_det_pro=$1", [$id_det_pro]);
    if(!$r){ out(['success'=>false,'error'=>pg_last_error($conexion)], 500); }
    out(['success'=>true,'message'=>'Eliminado']);
  }

  // Crear / actualizar
  $id_pro = isset($_POST['id_pro']) ? (int)$_POST['id_pro'] : 0;
  $id_com = isset($_POST['id_com']) ? (int)$_POST['id_com'] : 0;
  $codigo = isset($_POST['codigo_barra_pro']) ? trim((string)$_POST['codigo_barra_pro']) : '';

  $cantidad_caja = isset($_POST['cantidad_caja_pro']) ? (float)$_POST['cantidad_caja_pro'] : 0;
  $uni_caja = isset($_POST['uni_caja_pro']) ? (float)$_POST['uni_caja_pro'] : 0;

  $costo_caja = isset($_POST['costo_caja_pro']) ? (float)$_POST['costo_caja_pro'] : 0;
  $porcen = isset($_POST['porcen_pro']) ? (float)$_POST['porcen_pro'] : 0;

  // 👉 Si el usuario editó manualmente, respetamos lo que venga.
  $cantidad_uni = isset($_POST['cantidad_uni_pro']) && $_POST['cantidad_uni_pro'] !== ''
    ? (float)$_POST['cantidad_uni_pro']
    : ($cantidad_caja * $uni_caja);

  $costo_uni = isset($_POST['costo_uni_pro']) && $_POST['costo_uni_pro'] !== ''
    ? (float)$_POST['costo_uni_pro']
    : (($uni_caja > 0) ? ($costo_caja / $uni_caja) : 0);

  $precio1 = isset($_POST['precio1_pro']) ? (float)$_POST['precio1_pro'] : 0;
  $precio2 = isset($_POST['precio2_pro']) ? (float)$_POST['precio2_pro'] : 0;
  $precio3 = isset($_POST['precio3_pro']) ? (float)$_POST['precio3_pro'] : 0;

  $fecha_ven = (isset($_POST['fecha_ven_pro']) && trim((string)$_POST['fecha_ven_pro']) !== '')
    ? trim((string)$_POST['fecha_ven_pro'])
    : null;

  if($id_pro<=0 || $id_com<=0 || $codigo==='' || $cantidad_caja<=0 || $uni_caja<=0){
    out(['success'=>false,'message'=>'Faltan datos obligatorios (id_com, id_pro, código, cajas, unid/caja).'], 400);
  }

  if(!verificarCompra($conexion, $id_com)){
    out(['success'=>false,'message'=>'El ID de compra no existe en la base de datos.'], 400);
  }

  // Si ya existe el mismo producto en la misma compra, acumulamos (tu lógica original)
  $qExist = "SELECT id_det_pro, cantidad_caja_pro, cantidad_uni_pro
             FROM productodetalle
             WHERE codigo_barra_pro=$1 AND id_com=$2
             ORDER BY id_det_pro DESC
             LIMIT 1";
  $rExist = pg_query_params($conexion, $qExist, [$codigo, $id_com]);

  if($rExist && ($ex = pg_fetch_assoc($rExist))){
    $nuevoCaja = (float)$ex['cantidad_caja_pro'] + $cantidad_caja;
    $nuevoUni  = (float)$ex['cantidad_uni_pro'] + $cantidad_uni;

    $qUp = "UPDATE productodetalle SET
              id_pro=$1,
              cantidad_caja_pro=$2,
              cantidad_uni_pro=$3,
              costo_caja_pro=$4,
              costo_uni_pro=$5,
              precio1_pro=$6,
              precio2_pro=$7,
              precio3_pro=$8,
              uni_caja_pro=$9,
              fecha_ven_pro=$10,
              porcen_pro=$11
            WHERE id_det_pro=$12";

    $params = [$id_pro, $nuevoCaja, $nuevoUni, $costo_caja, $costo_uni, $precio1, $precio2, $precio3, $uni_caja, $fecha_ven, $porcen, (int)$ex['id_det_pro']];
    $rUp = pg_query_params($conexion, $qUp, $params);
    if(!$rUp){ out(['success'=>false,'message'=>'Error al actualizar','error'=>pg_last_error($conexion)], 500); }

    out(['success'=>true,'message'=>'Producto detalle actualizado']);
  }

  $qIns = "INSERT INTO productodetalle
            (id_pro, id_com, codigo_barra_pro, cantidad_caja_pro, cantidad_uni_pro,
             costo_caja_pro, costo_uni_pro, precio1_pro, precio2_pro, precio3_pro,
             uni_caja_pro, fecha_ven_pro, porcen_pro)
           VALUES
            ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13)
           RETURNING id_det_pro";
  $params = [$id_pro, $id_com, $codigo, $cantidad_caja, $cantidad_uni, $costo_caja, $costo_uni, $precio1, $precio2, $precio3, $uni_caja, $fecha_ven, $porcen];
  $rIns = pg_query_params($conexion, $qIns, $params);
  if(!$rIns){ out(['success'=>false,'message'=>'Error al insertar','error'=>pg_last_error($conexion)], 500); }

  $new = pg_fetch_assoc($rIns);
  out(['success'=>true,'message'=>'Producto detalle guardado','id_det_pro'=>$new['id_det_pro'] ?? null]);
}

out(['success'=>false,'error'=>'Método no permitido'], 405);
