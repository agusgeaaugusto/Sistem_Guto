<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../cargos/conexion_bi.php';
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido', 405);
  $json = file_get_contents('php://input');
  if (!$json) throw new Exception('Sin datos');
  $data = json_decode($json, true);
  if (!is_array($data)) throw new Exception('JSON inválido');
  $cliente   = $data['cliente'] ?? [];
  $items     = $data['items']   ?? [];
  $tipo_comp = $data['tipo_comprobante'] ?? 'TICKET';
  $usuario   = $data['usuario_id'] ?? 1;
  $obs       = $data['observacion'] ?? '';
  if (!isset($conexion) || !$conexion) throw new Exception('Sin conexión a la BD');
  if (empty($items)) throw new Exception('No hay ítems en la venta');
  $total_py = 0;
  foreach ($items as $it) {
    $cant = floatval($it['cantidad'] ?? 0);
    $pre  = floatval($it['precio_unit'] ?? 0);
    if ($cant <= 0 || $pre < 0) throw new Exception('Ítem con cantidad/precio inválido');
    $total_py += $cant * $pre;
  }
  pg_query($conexion, 'BEGIN');
  $sqlVenta = 'INSERT INTO public.venta
    (fecha, id_cliente, cliente_nombre, cliente_ruc, cliente_direccion,
     total_py, tipo_comprobante, usuario_id, observacion, timbrado, punto_emision, nro_factura)
     VALUES (NOW(), $1, $2, $3, $4, $5, $6, $7, $8,
             COALESCE((SELECT timbrado FROM public.parametros LIMIT 1),\'00000000\'),
             COALESCE((SELECT punto_emision FROM public.parametros LIMIT 1),\'001-001\'),
             COALESCE((SELECT next_factura FROM public.parametros LIMIT 1),\'0000001\'))
     RETURNING id_venta';
  $paramsVenta = [
    $cliente['id'] ?? null,
    $cliente['nombre'] ?? 'CONSUMIDOR FINAL',
    $cliente['ruc'] ?? '',
    $cliente['direccion'] ?? '',
    $total_py, $tipo_comp, $usuario, $obs
  ];
  $res = pg_query_params($conexion, $sqlVenta, $paramsVenta);
  if (!$res) throw new Exception('No se pudo guardar cabecera venta: ' . pg_last_error($conexion));
  $row = pg_fetch_assoc($res); $id_venta = intval($row['id_venta']);
  $itemN = 1;
  foreach ($items as $it) {
    $id_pro = intval($it['id_pro']); $cant = floatval($it['cantidad']); $pre = floatval($it['precio_unit']);
    $sqlDet = 'INSERT INTO public.venta_detalle (id_venta, item, id_pro, cantidad, precio_unit) VALUES ($1, $2, $3, $4, $5)';
    $ok = pg_query_params($conexion, $sqlDet, [$id_venta, $itemN, $id_pro, $cant, $pre]);
    if (!$ok) throw new Exception('No se pudo guardar detalle: ' . pg_last_error($conexion));
    $itemN++;
  }
  $upd = 'UPDATE public.parametros SET next_factura = LPAD((LPAD(next_factura,7,\'0\')::int + 1)::text,7,\'0\')';
  @pg_query($conexion, $upd);
  pg_query($conexion, 'COMMIT');
  echo json_encode(['ok'=>true, 'id_venta'=>$id_venta, 'total_py'=>$total_py], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  @pg_query($conexion, 'ROLLBACK');
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}


