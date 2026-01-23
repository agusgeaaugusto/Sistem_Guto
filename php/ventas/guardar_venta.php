<?php
/**
 * guardar_venta.php
 * Guarda la venta, sus detalles y emite números de ticket y factura.
 * Responde JSON. Incluye clearCarrito=true para que el front limpie el carrito.
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

// Helpers
function fail(string $msg, int $code = 400): void {
  http_response_code($code);
  echo json_encode(['ok'=>false,'msg'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}
function ok(array $d, int $code = 201): void {
  http_response_code($code);
  echo json_encode(['ok'=>true] + $d, JSON_UNESCAPED_UNICODE);
  exit;
}

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { fail('Sin conexión DB', 500); }



/* -----------------------------------------------------------
   🟦 FUNCIÓN CRÍTICA: DESCONTAR STOCK FIFO DE productodetalle
----------------------------------------------------------- */
function descontar_stock_productodetalle($conexion, int $id_pro, float $cantidad_necesaria) {

    if ($cantidad_necesaria <= 0) return;

    // Obtener lotes ordenados por fecha de vencimiento (FIFO)
    $sql = "SELECT id_det_pro, cantidad_uni_pro
            FROM productodetalle
            WHERE id_pro = $1 AND cantidad_uni_pro > 0
            ORDER BY fecha_ven_pro ASC NULLS LAST, id_det_pro ASC";

    $res = pg_query_params($conexion, $sql, [$id_pro]);
    if (!$res) {
        throw new Exception("Error consultando lotes: " . pg_last_error($conexion));
    }

    $restante = $cantidad_necesaria;

    while ($lote = pg_fetch_assoc($res)) {

        $id_det   = (int)$lote['id_det_pro'];
        $stockLot = (int)$lote['cantidad_uni_pro'];

        if ($restante <= 0) break;

        // Cuánto descontar en este lote
        $desc = min($stockLot, $restante);

        // Actualizar el lote
        $u = pg_query_params(
            $conexion,
            "UPDATE productodetalle
             SET cantidad_uni_pro = cantidad_uni_pro - $1
             WHERE id_det_pro = $2",
            [$desc, $id_det]
        );

        if (!$u) {
            throw new Exception("Error actualizando lote: " . pg_last_error($conexion));
        }

        $restante -= $desc;
    }

    if ($restante > 0) {
        throw new Exception("Stock insuficiente: faltan {$restante} unidades en producto ID {$id_pro}");
    }
}






// Leer JSON o POST
$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '{}', true);
if (!is_array($payload)) $payload = $_POST;

// Normalización de campos
$items = $payload['items'] ?? $payload['carrito'] ?? [];
if (!is_array($items) || count($items) === 0) fail('Carrito vacío.', 422);

$descuento   = floatval($payload['descuento_venta'] ?? $payload['descuento'] ?? 0);
$total_in    = $payload['total_venta'] ?? $payload['total'] ?? null;
$pago        = is_array($payload['pago'] ?? null) ? $payload['pago'] : [];
$recibido    = floatval($payload['recibido'] ?? ($pago['recibido'] ?? 0));
$vuelto      = floatval($payload['vuelto']   ?? ($pago['vuelto']   ?? 0));
$id_per      = isset($payload['id_per'])      ? intval($payload['id_per'])      : null;
$id_usuario  = isset($payload['id_usuario'])  ? intval($payload['id_usuario'])  : null;
$id_comp     = isset($payload['id_comprobante']) ? intval($payload['id_comprobante']) : null;
$estado      = (string)($payload['estado_venta'] ?? 'CER');
$obs         = trim((string)($payload['observacion'] ?? ''));

// Recalcular totales propios
$total_calc = 0.0;
$calc_items = [];
foreach ($items as $i) {
  $id_pro  = intval($i['id_pro'] ?? $i['id_producto'] ?? 0);
  $nombre  = trim((string)($i['nombre_pro'] ?? $i['descripcion'] ?? $i['nombre'] ?? ''));
  $cant    = floatval($i['cantidad'] ?? 0);
  $precio  = floatval($i['precio_unit'] ?? $i['precio'] ?? 0);
  $iva_pct = floatval($i['iva_porcentaje'] ?? $i['tipo_impuesto'] ?? 0);
  $sub     = isset($i['subtotal']) ? floatval($i['subtotal']) : round($cant * $precio, 2);

  if ($id_pro <= 0 || $cant <= 0 || $precio < 0) fail('Ítem inválido.', 422);

  $total_calc += $sub;
  $iva_monto   = isset($i['iva_monto']) ? floatval($i['iva_monto']) : round($sub * ($iva_pct/100), 2);
  $desc_it     = floatval($i['descuento'] ?? 0);

  $calc_items[] = [
    'id_pro'    => $id_pro,
    'nombre'    => $nombre,
    'cant'      => $cant,
    'precio'    => $precio,
    'sub'       => $sub,
    'iva_pct'   => $iva_pct,
    'iva_monto' => $iva_monto,
    'desc'      => $desc_it,
  ];
}
$total = ($total_in !== null) ? floatval($total_in) : $total_calc;
$total = max(0, round($total - $descuento, 2));
if ($recibido <= 0) $recibido = 0.0;
if ($vuelto   <= 0) $vuelto   = max(0.0, round($recibido - $total, 2));

// Transacción
pg_query($conexion, 'BEGIN');
try {
  // Insertar cabecera
  $res = pg_query_params(
    $conexion,
    'INSERT INTO venta(fecha_venta, descuento_venta, total_venta, estado_venta, id_per, id_usuario, id_comprobante, recibido, vuelto, observacion)
     VALUES (now(), $1, $2, $3, $4, $5, $6, $7, $8, $9) RETURNING id_venta',
    [$descuento, $total, $estado, $id_per, $id_usuario, $id_comp, $recibido, $vuelto, $obs]
  );
  if (!$res) throw new Exception(pg_last_error($conexion));
  $id_venta = intval(pg_fetch_result($res, 0, 0));

  // Insertar detalles + descontar stock FIFO
  $sqlDet = 'INSERT INTO venta_detalle(id_venta, id_pro, nombre_pro, cantidad, precio_unit, subtotal, iva_porcentaje, iva_monto, descuento)
             VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9)';

  foreach ($calc_items as $d) {

    // Insertar detalle
    $ok = pg_query_params($conexion, $sqlDet, [
      $id_venta, $d['id_pro'], $d['nombre'], $d['cant'], $d['precio'],
      $d['sub'], $d['iva_pct'], $d['iva_monto'], $d['desc']
    ]);
    if (!$ok) throw new Exception(pg_last_error($conexion));

    // 🔥 DESCONTAR STOCK FIFO DE LOS LOTES
    descontar_stock_productodetalle($conexion, $d['id_pro'], $d['cant']);
  }

  // Evitar carreras al generar número: bloquea filas existentes
  if (!pg_query($conexion, 'LOCK TABLE ticket IN EXCLUSIVE MODE')) throw new Exception(pg_last_error($conexion));
  if (!pg_query($conexion, 'LOCK TABLE factura IN EXCLUSIVE MODE')) throw new Exception(pg_last_error($conexion));

  $nextT = intval(pg_fetch_result(pg_query($conexion, 'SELECT COALESCE(MAX(numero),0)+1 FROM ticket'), 0, 0));
  $nextF = intval(pg_fetch_result(pg_query($conexion, 'SELECT COALESCE(MAX(numero),0)+1 FROM factura'), 0, 0));

  $rt = pg_query_params($conexion, 'INSERT INTO ticket(id_venta, numero, fecha) VALUES ($1,$2, now()) RETURNING numero', [$id_venta, $nextT]);
  if (!$rt) throw new Exception(pg_last_error($conexion));
  $rf = pg_query_params($conexion, 'INSERT INTO factura(id_venta, numero, fecha) VALUES ($1,$2, now()) RETURNING numero', [$id_venta, $nextF]);
  if (!$rf) throw new Exception(pg_last_error($conexion));

  $nrt = intval(pg_fetch_result($rt, 0, 0));
  $nrf = intval(pg_fetch_result($rf, 0, 0));

  pg_query($conexion, 'COMMIT');
  ok([
    'id_venta'    => $id_venta,
    'nroticket'   => $nrt,
    'nrofactura'  => $nrf,
    'total'       => $total,
    'vuelto'      => $vuelto,
    'clearCarrito'=> true
  ], 201);

} catch (Throwable $e) {
  pg_query($conexion, 'ROLLBACK');
  fail('No se pudo guardar la venta: '.$e->getMessage(), 500);
}
?>
