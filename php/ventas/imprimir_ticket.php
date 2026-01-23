<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); die('Sin conexión DB'); }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function nf0($n){ return number_format((float)$n, 0, ',', '.'); }
function nf2($n){ return number_format((float)$n, 2, ',', '.'); }

function hasCol($cn, $table, $col){
  $q = pg_query_params($cn, "SELECT 1 FROM information_schema.columns WHERE table_name=$1 AND column_name=$2 LIMIT 1", [$table, $col]);
  return $q && pg_num_rows($q) > 0;
}

function hasTable($cn, $table){
  $q = pg_query_params($cn, "SELECT 1 FROM information_schema.tables WHERE table_name=$1 LIMIT 1", [$table]);
  return $q && pg_num_rows($q) > 0;
}

/**
 * Obtiene cotización desde:
 * 1) GET (cotiz_br/cotiz_usd)
 * 2) columnas en venta (si existen)
 * 3) tabla moneda (si existe) buscando por código/nombre
 * 4) fallback al default
 *
 * Nota: Se asume que la cotización está expresada en GUARANÍES por 1 unidad de moneda (₲ / 1 USD, ₲ / 1 BRL).
 */
function getCotizacion($cn, array $venta, string $mon, float $default): float {
  $monU = strtoupper(trim($mon));

  // 1) venta (si trae cotización)
  $ventaKeys = [
    $monU === 'USD' ? ['cotiz_usd','cotizacion_usd','tc_usd','tipo_cambio_usd','usd'] : ['cotiz_br','cotizacion_br','tc_br','tipo_cambio_br','brl','real']
  ];
  foreach ($ventaKeys[0] as $k) {
    if (isset($venta[$k]) && is_numeric($venta[$k]) && (float)$venta[$k] > 0) return (float)$venta[$k];
  }

  // 2) tabla moneda
  if (hasTable($cn, 'moneda')) {
    // columnas candidatas
    $colCode = null;
    foreach (['codigo','sigla','abreviatura','moneda','nombre_mon','nombre'] as $c) { if (hasCol($cn,'moneda',$c)) { $colCode = $c; break; } }
    $colRate = null;
    foreach (['cotizacion','cotizacion_mon','tipo_cambio','valor','cambio','cotiz','rate','valor_mon'] as $c) { if (hasCol($cn,'moneda',$c)) { $colRate = $c; break; } }
    $colFecha = null;
    foreach (['fecha','fecha_act','updated_at','fecha_actualiza','fecha_actualiza_mon'] as $c) { if (hasCol($cn,'moneda',$c)) { $colFecha = $c; break; } }
    $colId = null;
    foreach (['id_mon','id','id_moneda'] as $c) { if (hasCol($cn,'moneda',$c)) { $colId = $c; break; } }

    if ($colRate) {
      $hints = ($monU==='USD')
        ? ['USD','DOLAR','DÓLAR','US$','$']
        : ['BRL','REAL','R$','BRASIL','BRAZIL'];

      // Armamos un WHERE flexible
      $where = "1=1";
      $params = [];
      if ($colCode) {
        $ors = [];
        foreach ($hints as $h) { $params[] = $h; $ors[] = "UPPER(COALESCE({$colCode}::text,'')) LIKE '%'||$".count($params)."||'%'"; }
        $where = "(" . implode(" OR ", $ors) . ")";
      }

      $order = "";
      if ($colFecha) $order = " ORDER BY {$colFecha} DESC NULLS LAST";
      else if ($colId) $order = " ORDER BY {$colId} DESC";

      $sql = "SELECT {$colRate} AS rate FROM moneda WHERE {$where}{$order} LIMIT 1";
      $q = pg_query_params($cn, $sql, $params);
      if ($q && pg_num_rows($q) > 0) {
        $r = pg_fetch_assoc($q);
        if (isset($r['rate']) && is_numeric($r['rate']) && (float)$r['rate'] > 0) return (float)$r['rate'];
      }
    }
  }

  return $default;
}

$id_venta = isset($_GET['id_venta']) ? (int)$_GET['id_venta'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id_venta <= 0) { http_response_code(400); die('Falta id_venta'); }

$cotiz_br_get  = isset($_GET['cotiz_br'])  ? (float)$_GET['cotiz_br']  : 0.0;
$cotiz_usd_get = isset($_GET['cotiz_usd']) ? (float)$_GET['cotiz_usd'] : 0.0;

// Defaults (si no hay tabla/venta/GET)
$default_br  = 1450.0;
$default_usd = 7600.0;

/* =========================
   DATOS DE VENTA
   ========================= */
$qv = pg_query_params($conexion, 'SELECT * FROM venta WHERE id_venta=$1', [$id_venta]);
if (!$qv || pg_num_rows($qv) === 0) { http_response_code(404); die('Venta no encontrada'); }
$venta = pg_fetch_assoc($qv);

// Cotizaciones: prioridad GET > venta > tabla moneda > defaults
$cotiz_br  = ($cotiz_br_get  > 0) ? $cotiz_br_get  : getCotizacion($conexion, $venta, 'BRL', $default_br);
$cotiz_usd = ($cotiz_usd_get > 0) ? $cotiz_usd_get : getCotizacion($conexion, $venta, 'USD', $default_usd);

/* =========================
   CLIENTE / CAJERO
   ========================= */
$cli_nom = $_GET['cliente_nombre'] ?? $_GET['razon'] ?? null;
$cli_doc = $_GET['cliente_doc'] ?? $_GET['ruc'] ?? null;
$cajero  = $_GET['cajero'] ?? ($venta['cajero'] ?? 'CARVALLO');

$ventaIdPer = null;
foreach (['id_per','id_persona','persona_id','idcliente','id_cli'] as $k) {
  if (!empty($venta[$k])) { $ventaIdPer = $venta[$k]; break; }
}

if (!$cli_nom || !$cli_doc) {
  $personaIdCol = null;
  foreach (['id_persona','id_per','id','persona_id'] as $c) {
    if (hasCol($conexion, 'persona', $c)) { $personaIdCol = $c; break; }
  }
  if ($ventaIdPer !== null && $personaIdCol) {
    $qp = pg_query_params($conexion, "SELECT * FROM persona WHERE {$personaIdCol}=$1", [$ventaIdPer]);
    if ($qp && pg_num_rows($qp) > 0) {
      $per = pg_fetch_assoc($qp);
      if (!$cli_nom) {
        foreach (['nombre_per','nombre','razon_social'] as $c) { if (!empty($per[$c])) { $cli_nom = $per[$c]; break; } }
      }
      if (!$cli_doc) {
        foreach (['ruc_ci','ruc','ci','documento'] as $c) { if (!empty($per[$c])) { $cli_doc = $per[$c]; break; } }
      }
    }
  }
}
if (!$cli_nom) $cli_nom = 'CONSUMIDOR FINAL';
if (!$cli_doc) $cli_doc = '';

/* =========================
   TICKET (si existe)
   ========================= */
$ticketq = pg_query_params($conexion, 'SELECT numero, fecha FROM ticket WHERE id_venta=$1', [$id_venta]);
$tk = ($ticketq && pg_num_rows($ticketq) > 0) ? pg_fetch_assoc($ticketq) : ['numero'=>null,'fecha'=>date('Y-m-d H:i:s')];

/* =========================
   DETALLE
   ========================= */
$vd_cols = [];
$detCols = pg_query_params($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name=$1", ['venta_detalle']);
if ($detCols) while($r = pg_fetch_assoc($detCols)) $vd_cols[$r['column_name']] = true;

$descExpr = "''";
if (isset($vd_cols['nombre_pro'])) $descExpr = "COALESCE(d.nombre_pro, '')";
if (hasCol($conexion, 'producto', 'nombre_pro') && isset($vd_cols['id_pro'])) $descExpr = "COALESCE(p.nombre_pro, " . $descExpr . ", '')";

$cantExpr = isset($vd_cols['cantidad']) ? "d.cantidad" : (isset($vd_cols['cantidad_det_ven']) ? "d.cantidad_det_ven" : "0");
$precExpr = isset($vd_cols['precio_unit']) ? "d.precio_unit" : (isset($vd_cols['precio']) ? "d.precio" : (isset($vd_cols['precio_venta']) ? "d.precio_venta" : "0"));
$subtExpr = isset($vd_cols['subtotal']) ? "d.subtotal" : "({$cantExpr} * {$precExpr})";

$hasIdPro = isset($vd_cols['id_pro']);
$join = $hasIdPro && hasCol($conexion, 'producto', 'id_pro') ? "LEFT JOIN producto p ON p.id_pro = d.id_pro" : "";
$order = $hasIdPro ? "ORDER BY d.id_pro" : "";

$sql = "SELECT " . ($hasIdPro ? "d.id_pro, " : "") .
       $descExpr . " AS descripcion, " .
       "COALESCE({$cantExpr},0) AS cantidad, " .
       "COALESCE({$precExpr},0) AS precio_unit, " .
       "COALESCE({$subtExpr},0) AS subtotal " .
       "FROM venta_detalle d " . $join . " WHERE d.id_venta=$1 " . $order;

$qd = pg_query_params($conexion, $sql, [$id_venta]);

/* =========================
   TOTALES
   ========================= */
$total = (float)($venta['total_venta'] ?? $venta['total'] ?? 0);
$gs = $total;
$rs = ($cotiz_br  > 0) ? ($total / $cotiz_br)  : 0;
$us = ($cotiz_usd > 0) ? ($total / $cotiz_usd) : 0;

$recibido_txt = $_GET['recibido_txt'] ?? null;
$recibido_gs  = isset($venta['recibido']) ? (float)$venta['recibido'] : (isset($_GET['recibido']) ? (float)$_GET['recibido'] : 0);
$vuelto_gs    = isset($venta['vuelto'])   ? (float)$venta['vuelto']   : (isset($_GET['vuelto'])   ? (float)$_GET['vuelto']   : 0);

$fecha = $tk['fecha'] ?? date('Y-m-d H:i:s');
$fecha_fmt = date('d/m/Y H:i', strtotime((string)$fecha));

// Teléfono del local
$tel_local = '0981 742 163';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket <?= h($tk['numero'] ?? '') ?></title>

<style>
  :root{ --w: <?= (isset($_GET['paper']) && (int)$_GET['paper']===58) ? '58mm' : '80mm' ?>; }

  @page { size: var(--w) auto; margin: 0; }
  html, body { width: var(--w) !important; margin:0 !important; padding:0 !important; }

  body{
    font-family: "Courier New", ui-monospace, monospace;
    color:#000;
    background:#fff;

    /* Más negro/contraste (térmica) */
    font-weight: 700;
    -webkit-font-smoothing: none;
    text-rendering: geometricPrecision;
  }

  @media print{
    *{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    html, body{ height:auto; }
  }

  .ticket{ padding: 6mm 4mm; text-shadow: 0.15px 0 #000; }
  .c{ text-align:center; }
  .r{ text-align:right; }
  .l{ text-align:left; }

  /* Fuente un poco más grande en general */
  .title{ font-weight: 900; font-size: 16px; letter-spacing: .5px; }
  .meta{ font-size: 12px; line-height: 1.25; }
  .muted{ opacity:.85; }

  .line{ border-top: 2px dashed #000; margin: 7px 0; }

  table{ width:100%; border-collapse:collapse; table-layout: fixed; }
  th, td{ font-size: 12px; padding: 2px 0; vertical-align: top; font-weight: 700; }
  th{ font-weight: 900; }

  .desc{ word-break: break-word; overflow-wrap: anywhere; padding-top: 3px; }
  .qty{ width: 22%; }
  .unit{ width: 28%; }
  .subt{ width: 50%; }

  .totals td{ font-size: 13px; padding: 2px 0; }
  .big{ font-size: 14px; font-weight: 900; }

  .thanks{ margin-top: 6px; font-size: 12px; }
</style>

<script>
  function doPrint(){
    try{ window.focus(); }catch(e){}
    window.print();
  }
  window.onload = () => { doPrint(); };
  window.onafterprint = () => { setTimeout(() => window.close(), 350); };
</script>
</head>

<body>
  <div class="ticket">
    <div class="c title">CARVALLO BODEGA</div>
    <div class="c meta muted">Tel: <?= h($tel_local) ?></div>
    <div class="c meta muted">Ticket <?= h($tk['numero'] ?? '-') ?> · <?= h($fecha_fmt) ?></div>

    <div class="line"></div>

    <!-- CENTRADO: Venta / Cajero / Cliente -->
    <div class="c meta">
      <div><strong>Venta:</strong> <?= h($venta['id_venta'] ?? $id_venta) ?></div>
      <div><strong>Cajero:</strong> <?= h($cajero) ?></div>
      <div><strong>Cliente:</strong> <?= h($cli_doc ? ($cli_nom.' ('.$cli_doc.')') : $cli_nom) ?></div>
    </div>

    <div class="line"></div>

    <table>
      <thead>
        <tr>
          <th class="l qty">Cant</th>
          <th class="r unit">Precio</th>
          <th class="r subt">Subt</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($qd && pg_num_rows($qd) > 0): ?>
        <?php while($d = pg_fetch_assoc($qd)): ?>
          <tr>
            <td colspan="3" class="desc">
              <?= h((isset($d['id_pro']) ? $d['id_pro'].' - ' : '') . ($d['descripcion'] ?? '')) ?>
            </td>
          </tr>
          <tr>
            <td class="l"><?= nf0($d['cantidad'] ?? 0) ?></td>
            <td class="r"><?= nf0($d['precio_unit'] ?? 0) ?></td>
            <td class="r"><?= nf0($d['subtotal'] ?? 0) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="3" class="c">Sin ítems</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <div class="line"></div>

    <table class="totals">
      <tbody>
        <tr><td class="l">₲ Guaraní</td><td class="r big"><?= nf0($gs) ?></td></tr>
        <tr><td class="l">R$ Real</td><td class="r"><?= nf2($rs) ?></td></tr>
        <tr><td class="l">US$ Dólar</td><td class="r"><?= nf2($us) ?></td></tr>

        <?php if($recibido_txt || $recibido_gs > 0): ?>
          <tr><td class="l">Recibido</td><td class="r"><?= h($recibido_txt ?: ('₲ '.nf0($recibido_gs))) ?></td></tr>
          <tr><td class="l">Vuelto</td><td class="r">₲ <?= nf0($vuelto_gs) ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="line"></div>

    <div class="c thanks">¡Gracias por su preferencia!</div>
  </div>
</body>
</html>
