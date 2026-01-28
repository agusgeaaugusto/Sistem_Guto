<?php
include 'conexion_bi.php';
header('Content-Type: application/json; charset=UTF-8');

// ✅ GET: ?codigo_barra=XXXX[&id_com=123]
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['existe' => false, 'error' => 'Método no permitido']);
  exit;
}

$codigo_barra = isset($_GET['codigo_barra']) ? trim((string)$_GET['codigo_barra']) : '';
$id_com = isset($_GET['id_com']) && $_GET['id_com'] !== '' ? (int)$_GET['id_com'] : null;

if ($codigo_barra === '') {
  http_response_code(400);
  echo json_encode(['existe' => false, 'error' => 'Código de barra no proporcionado']);
  exit;
}

// Producto base (incluye IVA)
$qProd = "SELECT id_pro, nombre_pro, uni_caja_pro, iva_pro
          FROM Producto
          WHERE codigo_barra_pro = $1";
$rProd = pg_query_params($conexion, $qProd, [$codigo_barra]);

if (!$rProd || pg_num_rows($rProd) === 0) {
  echo json_encode(['existe' => false, 'error' => 'Producto no encontrado']);
  pg_close($conexion);
  exit;
}
$prod = pg_fetch_assoc($rProd);

// Detalle: si viene id_com => detalle de ESA compra, si no => último detalle registrado
if ($id_com !== null && $id_com > 0) {
  $qDet = "SELECT cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro,
                  porcen_pro, precio1_pro, precio2_pro, precio3_pro, fecha_ven_pro
           FROM ProductoDetalle
           WHERE codigo_barra_pro = $1 AND id_com = $2
           ORDER BY id_det_pro DESC
           LIMIT 1";
  $rDet = pg_query_params($conexion, $qDet, [$codigo_barra, $id_com]);
} else {
  $qDet = "SELECT cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro,
                  porcen_pro, precio1_pro, precio2_pro, precio3_pro, fecha_ven_pro
           FROM ProductoDetalle
           WHERE codigo_barra_pro = $1
           ORDER BY id_det_pro DESC
           LIMIT 1";
  $rDet = pg_query_params($conexion, $qDet, [$codigo_barra]);
}

$det = ($rDet && pg_num_rows($rDet) > 0) ? pg_fetch_assoc($rDet) : [];

// Normalizar fecha (YYYY-MM-DD) si viene timestamp
$fecha = $det['fecha_ven_pro'] ?? null;
if (!empty($fecha)) {
  $fecha = substr((string)$fecha, 0, 10);
}

echo json_encode([
  'existe' => true,
  'id_pro' => $prod['id_pro'],
  'nombre_pro' => $prod['nombre_pro'],
  'uni_caja_pro' => $prod['uni_caja_pro'],
  'iva_pro' => $prod['iva_pro'],

  // detalle (si existe)
  'cantidad_caja_pro' => $det['cantidad_caja_pro'] ?? '',
  'cantidad_uni_pro'  => $det['cantidad_uni_pro']  ?? '',
  'costo_caja_pro'    => $det['costo_caja_pro']    ?? '',
  'costo_uni_pro'     => $det['costo_uni_pro']     ?? '',
  'porcen_pro'        => $det['porcen_pro']        ?? '',
  'precio1_pro'       => $det['precio1_pro']       ?? '',
  'precio2_pro'       => $det['precio2_pro']       ?? '',
  'precio3_pro'       => $det['precio3_pro']       ?? 0,
  'fecha_ven_pro'     => $fecha ?? ''
]);

pg_close($conexion);
