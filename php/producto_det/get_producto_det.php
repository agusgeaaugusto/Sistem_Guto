<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=UTF-8');

if(!$conexion){ http_response_code(500); echo json_encode(['error'=>'Sin conexión DB']); exit; }

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
  http_response_code(405);
  echo json_encode(['error'=>'Método no permitido']);
  exit;
}

$codigo = isset($_GET['codigo_barra']) ? trim((string)$_GET['codigo_barra']) : '';
$id_com = isset($_GET['id_com']) && $_GET['id_com'] !== '' ? (int)$_GET['id_com'] : null;

if($codigo === ''){ http_response_code(400); echo json_encode(['error'=>'Código de barras no válido']); exit; }

// Producto base
$qP = "SELECT id_pro, nombre_pro, uni_caja_pro, iva_pro FROM producto WHERE codigo_barra_pro=$1";
$rP = pg_query_params($conexion, $qP, [$codigo]);
if(!$rP || pg_num_rows($rP)===0){ echo json_encode(['existe'=>false,'error'=>'Producto no encontrado']); pg_close($conexion); exit; }
$prod = pg_fetch_assoc($rP);

// Último detalle (por compra si viene)
if($id_com && $id_com>0){
  $qD = "SELECT cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro, porcen_pro,
                precio1_pro, precio2_pro, precio3_pro, fecha_ven_pro
          FROM productodetalle
          WHERE codigo_barra_pro=$1 AND id_com=$2
          ORDER BY id_det_pro DESC
          LIMIT 1";
  $rD = pg_query_params($conexion, $qD, [$codigo, $id_com]);
} else {
  $qD = "SELECT cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro, porcen_pro,
                precio1_pro, precio2_pro, precio3_pro, fecha_ven_pro
          FROM productodetalle
          WHERE codigo_barra_pro=$1
          ORDER BY id_det_pro DESC
          LIMIT 1";
  $rD = pg_query_params($conexion, $qD, [$codigo]);
}
$det = ($rD && pg_num_rows($rD)>0) ? pg_fetch_assoc($rD) : [];
$fecha = $det['fecha_ven_pro'] ?? '';
if($fecha) $fecha = substr((string)$fecha, 0, 10);

echo json_encode([
  'existe' => true,
  'id_pro' => $prod['id_pro'],
  'nombre_pro' => $prod['nombre_pro'],
  'uni_caja_pro' => $prod['uni_caja_pro'],
  'iva_pro' => $prod['iva_pro'],

  'cantidad_caja_pro' => $det['cantidad_caja_pro'] ?? '',
  'cantidad_uni_pro' => $det['cantidad_uni_pro'] ?? '',
  'costo_caja_pro' => $det['costo_caja_pro'] ?? '',
  'costo_uni_pro' => $det['costo_uni_pro'] ?? '',
  'porcen_pro' => $det['porcen_pro'] ?? '',
  'precio1_pro' => $det['precio1_pro'] ?? '',
  'precio2_pro' => $det['precio2_pro'] ?? '',
  'precio3_pro' => $det['precio3_pro'] ?? 0,
  'fecha_ven_pro' => $fecha
]);

pg_close($conexion);
