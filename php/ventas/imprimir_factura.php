<?php
/**
 * imprimir_factura_simple.php (A4 preimpresa) — Carvallo Bodega
 *
 * ✅ Imprime SOLO datos sobre factura preimpresa (sin fondo)
 * ✅ Ajuste fácil: cambiá SOLO el bloque $POS (mm)
 *
 * Ejemplos:
 *  imprimir_factura_simple.php?id_venta=225
 *  imprimir_factura_simple.php?id_venta=225&via=2
 *  imprimir_factura_simple.php?id_venta=225&x=-2&y=1&scale=1&debug=1
 */

declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); die('Sin conexión DB'); }

// ----------------- AJUSTÁ SOLO ESTO (mm) -----------------
$POS = [
  // Ubicación del bloque (formulario) en la hoja A4
  // via=1 usa top1, via=2 usa top2
  'form_left' => 8.0,
  'top1'      => 18.0,
  'top2'      => 165.0,

  // Cabecera
  'fecha'    => ['x'=> 34.0, 'y'=> 26.0],
  'ruc'      => ['x'=> 16.0,  'y'=> 30.0],
  'nombre'   => ['x'=> 42.0,  'y'=> 34.0],
  'direccion'=> ['x'=> 23.0,  'y'=> 40.0],

  // X en condición
  'x_contado'=> ['x'=> 164.0, 'y'=> 32.0],
  'x_credito'=> ['x'=> 197.0, 'y'=> 32.0],

  // Tabla detalle
  'row_top'  => 53.0,
  'row_h'    => 6.2,
  'rows_max' => 10,
  'col' => [
    'cod'   => 10.0,
    'cant'  => 16.0,
    'desc'  => 32.0,
    'punit' => 105.0,
    'exen'  => 130.0,
    'iva5'  => 150.0,
    'iva10' => 175.0,
  ],

  // Totales
  'parcial' => ['x'=> 175.0,  'y'=> 102.0],
  'total'   => ['x'=> 175.0,  'y'=> 110.0],
  'letras'  => ['x'=> 50.0,  'y'=> 110.0],

  // ✅ NUEVOS CAMPOS
  // Totales por tipo (VALORES DE VENTA)
  'tot_exenta_venta' => ['x'=> 140.0, 'y'=> 102.0],
  'tot_5_venta'      => ['x'=> 160.0, 'y'=> 102.0],

  // Totales IVA
  'iva5_total'  => ['x'=> 45.0, 'y'=> 119.0],
  'iva10_total' => ['x'=> 83.0, 'y'=> 119.0],
  'iva_total'   => ['x'=> 150.0, 'y'=> 119.0],

  // Estilo
  'font_pt' => 10,
];
// ----------------------------------------------------------

// ----------------- Helpers -----------------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function nf0($n){ return number_format((float)$n, 0, ',', '.'); }

function hasCol($cn, $table, $col){
  $q = pg_query_params($cn, "SELECT 1 FROM information_schema.columns WHERE table_name=$1 AND column_name=$2 LIMIT 1", [$table, $col]);
  return $q && pg_num_rows($q) > 0;
}

// --- número a letras (simple, suficiente para Guaraní) ---
function _letras_basicas($n){
  static $u = ['', 'uno','dos','tres','cuatro','cinco','seis','siete','ocho','nueve','diez','once','doce','trece','catorce','quince','dieciséis','diecisiete','dieciocho','diecinueve'];
  static $d = ['', 'diez','veinte','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa'];
  static $c = ['', 'cien','doscientos','trescientos','cuatrocientos','quinientos','seiscientos','setecientos','ochocientos','novecientos'];
  if ($n == 0) return 'cero';
  if ($n < 20) return $u[$n];
  if ($n < 100){
    $t = intdiv($n, 10); $r = $n % 10;
    if ($n == 20) return 'veinte';
    if ($n < 30) return 'veinti' . ($r ? $u[$r] : '');
    return $d[$t] . ($r ? ' y ' . $u[$r] : '');
  }
  if ($n < 1000){
    $t = intdiv($n, 100); $r = $n % 100;
    if ($n == 100) return 'cien';
    return $c[$t] . ($r ? ' ' . _letras_basicas($r) : '');
  }
  if ($n < 1000000){
    $t = intdiv($n, 1000); $r = $n % 1000;
    $pref = ($t==1 ? 'mil' : _letras_basicas($t).' mil');
    return $pref . ($r ? ' ' . _letras_basicas($r) : '');
  }
  if ($n < 1000000000){
    $t = intdiv($n, 1000000); $r = $n % 1000000;
    $pref = ($t==1 ? 'un millón' : _letras_basicas($t).' millones');
    return $pref . ($r ? ' ' . _letras_basicas($r) : '');
  }
  return (string)$n;
}
function numero_a_letras_guarani($monto){
  $entero = (int)floor((float)$monto + 0.00001);
  $txt = _letras_basicas($entero);
  $txt = preg_replace('/\buno\b$/u', 'un', $txt);
  $txt .= ' GUARANÍES';
  return mb_strtoupper($txt, 'UTF-8');
}

function mes_py_mayus($n){
  static $m = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SETIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
  $n = max(1, min(12, (int)$n));
  return $m[$n-1];
}
function fecha_py($ts){
  $d = (int)date('d',$ts);
  $m = mes_py_mayus((int)date('n',$ts));
  $y = (int)date('Y',$ts);
  return sprintf('%02d DE %s DEL %04d', $d, $m, $y);
}

function mm($n){ return rtrim(rtrim(sprintf('%.2F', (float)$n), '0'), '.'); }
function style_xy(array $p){ return 'left: '.mm($p['x']).'mm; top: '.mm($p['y']).'mm;'; }

// ----------------- Parámetros -----------------
$id_venta = isset($_GET['id_venta']) ? (int)$_GET['id_venta'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id_venta <= 0) { http_response_code(400); die('Falta id_venta'); }

$offx  = isset($_GET['x']) ? (float)$_GET['x'] : 0.0; // mm
$offy  = isset($_GET['y']) ? (float)$_GET['y'] : 0.0; // mm
$scale = isset($_GET['scale']) ? (float)$_GET['scale'] : 1.0;
$debug = isset($_GET['debug']) ? (int)$_GET['debug'] : 0;

// (se mantiene por compatibilidad, pero ahora imprimimos ambas vías)
$via   = isset($_GET['via']) ? max(1, min(2, (int)$_GET['via'])) : 1;

// ----------------- Venta -----------------
$qv = pg_query_params($conexion, 'SELECT * FROM venta WHERE id_venta=$1', [$id_venta]);
if (!$qv || pg_num_rows($qv) === 0) { http_response_code(404); die('Venta no encontrada'); }
$venta = pg_fetch_assoc($qv);

// ✅ Fecha: SIEMPRE del sistema de la PC que imprime (ignoramos BD y ts por URL)
date_default_timezone_set('America/Asuncion'); // Paraguay
$ts_fecha  = time();
$fecha_imp = fecha_py($ts_fecha);


// Cliente (compatibles con tu URL)
$cli_nom = $_GET['razon'] ?? $_GET['cliente_nombre'] ?? ($venta['cliente_nombre'] ?? null) ?? 'CONSUMIDOR FINAL';
$cli_doc = $_GET['ruc']   ?? $_GET['cliente_doc']     ?? ($venta['cliente_doc'] ?? '') ?? '';
$cli_dir = $_GET['direccion'] ?? $_GET['dir'] ?? $_GET['direccion_cli'] ?? ($venta['cliente_dir'] ?? '') ?? '';

// Condición contado/crédito
$cond_raw  = $_GET['cond'] ?? ($venta['condicion_venta'] ?? $venta['condicion'] ?? 'CONTADO');
$cond      = strtoupper(trim((string)$cond_raw));
$isCredito = in_array($cond, ['CREDITO','CRÉDITO'], true);

// ----------------- Detalle -----------------
$vd_cols = [];
$detCols = pg_query_params($conexion, "SELECT column_name FROM information_schema.columns WHERE table_name=$1", ['venta_detalle']);
if ($detCols) while($r = pg_fetch_assoc($detCols)) $vd_cols[$r['column_name']] = true;

$hasIdPro = isset($vd_cols['id_pro']);
$join = ($hasIdPro && hasCol($conexion, 'producto', 'id_pro')) ? "LEFT JOIN producto p ON p.id_pro = d.id_pro" : "";

$descExpr = "''";
if (isset($vd_cols['nombre_pro'])) $descExpr = "COALESCE(d.nombre_pro,'')";
if ($hasIdPro && hasCol($conexion,'producto','nombre_pro')) $descExpr = "COALESCE(p.nombre_pro, {$descExpr}, '')";
if (isset($vd_cols['descripcion'])) $descExpr = "COALESCE(d.descripcion, {$descExpr})";

$cantExpr = isset($vd_cols['cantidad']) ? "d.cantidad" : (isset($vd_cols['cantidad_det_ven']) ? "d.cantidad_det_ven" : "0");
$precExpr = isset($vd_cols['precio_unit']) ? "d.precio_unit" : (isset($vd_cols['precio']) ? "d.precio" : (isset($vd_cols['precio_venta']) ? "d.precio_venta" : "0"));
$subtExpr = isset($vd_cols['subtotal']) ? "d.subtotal" : "({$cantExpr} * {$precExpr})";
$ivaExpr  = isset($vd_cols['tipo_impuesto']) ? "LOWER(COALESCE(d.tipo_impuesto,'10'))" : (isset($vd_cols['iva']) ? "LOWER(COALESCE(d.iva,'10'))" : "'10'");

$order = isset($vd_cols['orden']) ? "ORDER BY d.orden NULLS LAST" : ($hasIdPro ? "ORDER BY d.id_pro" : "");

$sql = "SELECT " . ($hasIdPro ? "d.id_pro, " : "") .
       "{$descExpr} AS descripcion, " .
       "COALESCE({$cantExpr},0) AS cantidad, " .
       "COALESCE({$precExpr},0) AS precio_unit, " .
       "COALESCE({$subtExpr},0) AS subtotal, " .
       "{$ivaExpr} AS iva_tipo " .
       "FROM venta_detalle d {$join} WHERE d.id_venta=$1 {$order}";

$qd = pg_query_params($conexion, $sql, [$id_venta]);

$items = [];
if ($qd) while($d = pg_fetch_assoc($qd)){
  $items[] = [
    'codigo'      => $d['id_pro'] ?? '',
    'descripcion' => (string)($d['descripcion'] ?? ''),
    'cantidad'    => (float)($d['cantidad'] ?? 0),
    'precio_unit' => (float)($d['precio_unit'] ?? 0),
    'subtotal'    => (float)($d['subtotal'] ?? 0),
    'iva_tipo'    => (string)($d['iva_tipo'] ?? '10'),
  ];
}

// ----------------- Totales -----------------
$total = (float)($venta['total_venta'] ?? $venta['total'] ?? 0);
if ($total <= 0) {
  foreach ($items as $it) $total += (float)$it['subtotal'];
}

$ex = 0.0; $t5 = 0.0; $t10 = 0.0;
foreach ($items as $it) {
  $iva = strtolower(trim((string)$it['iva_tipo']));
  if ($iva === 'exenta' || $iva === '0') $ex += (float)$it['subtotal'];
  elseif ($iva === '5') $t5 += (float)$it['subtotal'];
  else $t10 += (float)$it['subtotal'];
}
$liq5  = round($t5/21);   // IVA 5%
$liq10 = round($t10/11);  // IVA 10%

// ✅ NUEVOS TOTALES
$tot_exenta_venta = $ex;
$tot_5_venta      = $t5;
$iva5_total       = $liq5;
$iva10_total      = $liq10;
$iva_total        = $liq5 + $liq10;
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura Preimpresa</title>
<style>
  @page { size: A4; margin: 0; }
  html, body { margin:0; padding:0; }
  .page { position:relative; width:210mm; height:297mm; }
  .canvas { position:absolute; left: <?= mm($offx) ?>mm; top: <?= mm($offy) ?>mm; width:210mm; height:297mm; transform: scale(<?= $scale ?>); transform-origin: 0 0; }

  .form { position:absolute; left: <?= mm($POS['form_left']) ?>mm; width: 200mm; height: 130mm;
          font-family: 'Courier New', monospace; font-size: <?= (int)$POS['font_pt'] ?>pt; color:#000; }
  .field { position:absolute; white-space:nowrap; }

  /* columnas */
  .cod   { left: <?= mm($POS['col']['cod']) ?>mm;   width: 22mm; text-align:left; }
  .cant  { left: <?= mm($POS['col']['cant']) ?>mm;  width: 12mm; text-align:center; }
  .desc  { left: <?= mm($POS['col']['desc']) ?>mm;  width: 108mm; text-align:left; overflow:hidden; }
  .punit { left: <?= mm($POS['col']['punit']) ?>mm; width: 18mm; text-align:right; }
  .exen  { left: <?= mm($POS['col']['exen']) ?>mm;  width: 12mm; text-align:right; }
  .iva5  { left: <?= mm($POS['col']['iva5']) ?>mm;  width: 12mm; text-align:right; }
  .iva10 { left: <?= mm($POS['col']['iva10']) ?>mm; width: 12mm; text-align:right; }

  <?php if ($debug): ?>
  .form::before { content:''; position:absolute; inset:0; border:1px dashed red; }
  .row { outline: 1px dotted rgba(0,0,255,.35); }
  <?php endif; ?>
</style>
</head>
<body onload="window.print && window.print();">
  <div class="page">
    <div class="canvas">

      <?php  // ✅ Subir SOLO la vía 2 (sin tocar $POS)
  $SUBIR_VIA2_MM =9.0;

  foreach ([1,2] as $viaActual):
    $formTop = ($viaActual === 2)
      ? ($POS['top2'] - $SUBIR_VIA2_MM)
      : $POS['top1'];
      ?>
      <div class="form" style="top: <?= mm($formTop) ?>mm;">

        <!-- CABECERA -->
        <div class="field" style="<?= style_xy($POS['ruc']) ?>"><?= h($cli_doc) ?></div>
        <div class="field" style="<?= style_xy($POS['fecha']) ?>"><?= h($fecha_imp) ?></div>
        <div class="field" style="<?= style_xy($POS['nombre']) ?> max-width:120mm; overflow:hidden;"><?= h($cli_nom) ?></div>
        <div class="field" style="<?= style_xy($POS['direccion']) ?> max-width:120mm; overflow:hidden;"><?= h($cli_dir) ?></div>

        <!-- CONDICIÓN -->
        <div class="field" style="<?= style_xy($POS['x_contado']) ?> font-weight:bold;"><?= $isCredito ? '' : 'X' ?></div>
        <div class="field" style="<?= style_xy($POS['x_credito']) ?> font-weight:bold;"><?= $isCredito ? 'X' : '' ?></div>

        <!-- FILAS (detalle) -->
        <?php
          $rowTop = (float)$POS['row_top'];
          $rowH   = (float)$POS['row_h'];
          $max    = (int)$POS['rows_max'];
          for ($i=0; $i<$max; $i++):
            $it = $items[$i] ?? null;
            $y  = $rowTop + $i*$rowH;
            $iva = $it ? strtolower(trim((string)$it['iva_tipo'])) : '';
            $esEx = $it && ($iva==='exenta' || $iva==='0');
            $es5  = $it && ($iva==='5');
            $es10 = $it && !$esEx && !$es5;
            $vEx  = $it ? ($esEx ? nf0($it['subtotal']) : '0') : '';
            $v5   = $it ? ($es5  ? nf0($it['subtotal']) : '0') : '';
            $v10  = $it ? ($es10 ? nf0($it['subtotal']) : '0') : '';
        ?>
          <div class="row" style="position:absolute; top: <?= mm($y) ?>mm; left:0; right:0; height: <?= mm($rowH) ?>mm;">
            <div class="field cod"><?= $it ? h($it['codigo']) : '' ?></div>
            <div class="field cant"><?= $it ? nf0($it['cantidad']) : '' ?></div>
            <div class="field desc"><?= $it ? h($it['descripcion']) : '' ?></div>
            <div class="field punit"><?= $it ? nf0($it['precio_unit']) : '' ?></div>
            <div class="field exen"><?= $it ? $vEx : '' ?></div>
            <div class="field iva5"><?= $it ? $v5  : '' ?></div>
            <div class="field iva10"><?= $it ? $v10 : '' ?></div>
          </div>
        <?php endfor; ?>

        <!-- TOTALES -->
        <?php $parcial = $ex + $t5 + $t10; ?>
        <div class="field" style="<?= style_xy($POS['parcial']) ?>"><?= nf0($parcial) ?></div>
        <div class="field" style="<?= style_xy($POS['total']) ?> font-weight:bold;"><?= nf0($parcial) ?></div>

        <div class="field" style="<?= style_xy($POS['letras']) ?> width:120mm; overflow:hidden;">
          <?= h(numero_a_letras_guarani($total)) ?>
        </div>

        <!-- Totales por tipo (VALORES DE VENTA) -->
        <div class="field" style="<?= style_xy($POS['tot_exenta_venta']) ?>"><?= nf0($tot_exenta_venta) ?></div>
        <div class="field" style="<?= style_xy($POS['tot_5_venta']) ?>"><?= nf0($tot_5_venta) ?></div>

        <!-- Totales IVA -->
        <div class="field" style="<?= style_xy($POS['iva5_total']) ?>"><?= nf0($iva5_total) ?></div>
        <div class="field" style="<?= style_xy($POS['iva10_total']) ?>"><?= nf0($iva10_total) ?></div>
        <div class="field" style="<?= style_xy($POS['iva_total']) ?> font-weight:bold;"><?= nf0($iva_total) ?></div>

      </div>
      <?php endforeach; ?>

    </div>
  </div>
</body>
</html>
