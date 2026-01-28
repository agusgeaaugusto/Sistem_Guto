<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Sin conexión DB']); exit; }

function jsonOut($arr, int $code=200): void {
  http_response_code($code);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

function q1($cn, string $sql, array $params=[]){
  $r = $params ? pg_query_params($cn,$sql,$params) : pg_query($cn,$sql);
  return $r ?: null;
}

function findTableLike($cn, string $like): ?string {
  $r = q1($cn, "SELECT table_name
               FROM information_schema.tables
               WHERE table_schema='public' AND table_type='BASE TABLE' AND table_name ILIKE $1
               ORDER BY table_name
               LIMIT 1", [$like]);
  if(!$r) return null;
  $row = pg_fetch_assoc($r);
  return $row['table_name'] ?? null;
}

function findNameCol($cn, string $table, array $preferred): ?string {
  // 1) preferidos
  foreach($preferred as $c){
    $r = q1($cn,"SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=$1 AND column_name=$2 LIMIT 1",[$table,$c]);
    if($r && pg_num_rows($r)>0) return $c;
  }
  // 2) primer col tipo text/varchar con "nom"
  $r = q1($cn,"SELECT column_name
              FROM information_schema.columns
              WHERE table_schema='public' AND table_name=$1
                AND data_type IN ('character varying','text','character')
              ORDER BY (CASE WHEN column_name ILIKE '%nom%' THEN 0 ELSE 1 END), ordinal_position
              LIMIT 1",[$table]);
  if(!$r) return null;
  $row = pg_fetch_assoc($r);
  return $row['column_name'] ?? null;
}

function findIdCol($cn, string $table, array $preferred): ?string {
  foreach($preferred as $c){
    $r = q1($cn,"SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=$1 AND column_name=$2 LIMIT 1",[$table,$c]);
    if($r && pg_num_rows($r)>0) return $c;
  }
  // buscar primera columna integer que empiece con id_
  $r = q1($cn,"SELECT column_name
              FROM information_schema.columns
              WHERE table_schema='public' AND table_name=$1
                AND data_type IN ('integer','bigint','smallint')
              ORDER BY (CASE WHEN column_name ILIKE 'id_%' THEN 0 ELSE 1 END), ordinal_position
              LIMIT 1",[$table]);
  if(!$r) return null;
  $row = pg_fetch_assoc($r);
  return $row['column_name'] ?? null;
}


function metaProveedoresMonedas($cn): array {
  $out = ['proveedores'=>[], 'monedas'=>[]];

  // Proveedor
  $tProv = findTableLike($cn, 'proveedor%');
  if($tProv){
    $idCol = findIdCol($cn, $tProv, ['id_proveedor','id_prov','idproveedor','id']);
    if(!$idCol) $idCol = 'id_proveedor';
    $nameCol = findNameCol($cn, $tProv, ['nombre_proveedor','proveedor','nombre','razon_social','nombre_razon','razon_social_proveedor']);
    if($nameCol){
      $r = q1($cn, "SELECT {$idCol} AS id, {$nameCol} AS nombre FROM {$tProv} ORDER BY 2");
      if($r){
        while($row=pg_fetch_assoc($r)){
          $out['proveedores'][] = ['id'=>$row['id'], 'nombre'=>$row['nombre']];
        }
      }
    } else {
      // sin nombre: igual devolvemos ids
      $r = q1($cn, "SELECT {$idCol} AS id FROM {$tProv} ORDER BY 1");
      if($r){
        while($row=pg_fetch_assoc($r)){
          $out['proveedores'][] = ['id'=>$row['id'], 'nombre'=>"#".$row['id']];
        }
      }
    }
  }

  // Moneda (cotización)
  $tMon = findTableLike($cn, 'moned%'); // moneda / monedas
  if($tMon){
    $idCol = 'id_mon';

    // Detectar columnas típicas
    $has = function(string $col) use ($cn,$tMon): bool {
      $r = q1($cn,"SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=$1 AND column_name=$2 LIMIT 1",[$tMon,$col]);
      return $r && pg_num_rows($r)>0;
    };

    $cols = [$idCol];
    if($has('estado'))     $cols[]='estado';
    if($has('guarani'))    $cols[]='guarani';
    if($has('real'))       $cols[]='real';
    if($has('dolar'))      $cols[]='dolar';
    if($has('creado_en'))  $cols[]='creado_en';

    // Primero los ACTIVO y más recientes
    $sql = "SELECT ".implode(',', $cols)." FROM {$tMon} ".
           "ORDER BY (CASE WHEN COALESCE(estado,'')='ACTIVO' THEN 0 ELSE 1 END), creado_en DESC NULLS LAST, {$idCol} DESC";
    $r = q1($cn, $sql);
    if($r){
      while($row=pg_fetch_assoc($r)){
        $id = $row[$idCol] ?? null;
        $estado = strtoupper((string)($row['estado'] ?? ''));
        $g = $row['guarani'] ?? null;
        $re = $row['real'] ?? null;
        $do = $row['dolar'] ?? null;

        // ✅ Generamos 3 opciones por cada registro (ID + moneda), como pediste:
        //    "ID - GUARANÍ", "ID - REAL", "ID - DÓLAR"
        $opts = [
          ['moneda_doc'=>'GUARANI','label'=>'Guaraní','tasa'=>$g],
          ['moneda_doc'=>'REAL','label'=>'Real','tasa'=>$re],
          ['moneda_doc'=>'DOLAR','label'=>'Dólar','tasa'=>$do],
        ];

        foreach($opts as $o){
          // Si no existe la columna/tasa, igual mostramos pero con 0
          $tasa = ($o['tasa']===null || $o['tasa']==='') ? 0 : $o['tasa'];
          $tasa = ($o['tasa']===null || $o['tasa']==='') ? 0 : $o['tasa'];
$sym = 'Gs';
if($o['moneda_doc']==='REAL') $sym = 'R$';
if($o['moneda_doc']==='DOLAR') $sym = 'US$';

$out['monedas'][] = [
  'id' => $id,
  'moneda_doc' => $o['moneda_doc'],
  'tasa' => $tasa,
  // Solo ID + cotización (como pediste)
  'nombre' => '#'.$id.' — '.$sym.' '.$tasa,
];
        }
      }
    }
  }

  return $out;
}

// ---------- META para armar SELECTs ----------
if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['meta'])) {
  jsonOut(['success'=>true] + metaProveedoresMonedas($conexion));
}

// ---------- INSERT ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $fecha_com = trim((string)($_POST['fecha_com'] ?? ''));
  $id_proveedor = trim((string)($_POST['id_proveedor'] ?? ''));
  $id_mon = trim((string)($_POST['id_mon'] ?? ''));
  $moneda_doc = strtoupper(trim((string)($_POST['moneda_doc'] ?? 'GUARANI'))); // GUARANI | REAL | DOLAR
  $timbrado_com = trim((string)($_POST['timbrado_com'] ?? ''));
  $documento_com = trim((string)($_POST['documento_com'] ?? ''));
  $fecha_emision_comp = trim((string)($_POST['fecha_emision_comp'] ?? ''));
  $historico_com = trim((string)($_POST['historico_com'] ?? ''));
  if($historico_com==='') $historico_com = 'Sin histórico';

  if($moneda_doc!=='GUARANI' && $moneda_doc!=='REAL' && $moneda_doc!=='DOLAR'){ $moneda_doc='GUARANI'; }

  // ✅ Si no completan, igual se guarda con 0
  $valor_documento_raw = trim((string)($_POST['valor_documento_com'] ?? ''));
  $valor_documento_com = ($valor_documento_raw === '' ? 0 : (float)str_replace(',', '.', $valor_documento_raw));
  if ($valor_documento_com < 0) $valor_documento_com = 0;

  // Validar obligatorios (excepto valor_documento / historico)
  if ($fecha_com==='' || $id_proveedor==='' || $id_mon==='' || $timbrado_com==='' || $documento_com==='' || $fecha_emision_comp==='') {
    jsonOut(['success'=>false,'message'=>'Completá los campos obligatorios.'], 422);
  }

  // ✅ Traer cotización de la tabla moneda según lo seleccionado
  $tMon = findTableLike($conexion, 'moned%');
  $tasa = 0;
  if($tMon){
    // intentamos leer guarani/real/dolar del registro id_mon
    $rMon = q1($conexion, "SELECT guarani, real, dolar, estado, creado_en FROM {$tMon} WHERE id_mon=$1 LIMIT 1", [(int)$id_mon]);
    if($rMon && pg_num_rows($rMon)>0){
      $m = pg_fetch_assoc($rMon) ?: [];
      if($moneda_doc==='REAL')   $tasa = (float)($m['real'] ?? 0);
      if($moneda_doc==='DOLAR')  $tasa = (float)($m['dolar'] ?? 0);
      if($moneda_doc==='GUARANI')$tasa = (float)($m['guarani'] ?? 0);
      if($tasa <= 0) $tasa = 0;
      // snapshot en histórico (sin duplicar si ya lo pegaste manualmente)
      $snap = "Moneda: {$moneda_doc} | Cotización: {$tasa} | id_mon: {$id_mon}";
      if(stripos($historico_com, 'Moneda:') === false){
        $historico_com = rtrim($historico_com);
        $historico_com .= ($historico_com==='' ? '' : "
").$snap;
      }
    }
  }

  $sql = "INSERT INTO compra (fecha_com, id_proveedor, id_mon, timbrado_com, documento_com, fecha_emision_comp, historico_com, valor_documento_com)
          VALUES ($1,$2,$3,$4,$5,$6,$7,$8)
          RETURNING id_com";
  $r = q1($conexion, $sql, [$fecha_com, $id_proveedor, $id_mon, $timbrado_com, $documento_com, $fecha_emision_comp, $historico_com, $valor_documento_com]);
  if(!$r){
    jsonOut(['success'=>false,'message'=>'Error al guardar: '.pg_last_error($conexion)], 500);
  }
  $row = pg_fetch_assoc($r) ?: [];
  jsonOut(['success'=>true,'id_com'=>$row['id_com'] ?? null, 'tasa'=>$tasa, 'moneda_doc'=>$moneda_doc]);
}


// ---------- LIST ----------
$tProv = findTableLike($conexion, 'proveedor%');
$tMon  = findTableLike($conexion, 'moned%');

$provNameCol = null;
if($tProv){
  $provNameCol = findNameCol($conexion, $tProv, ['nombre_proveedor','proveedor','nombre','razon_social','nombre_razon','razon_social_proveedor']);
}

if($tMon){
  $sql = "SELECT c.*, ".
         ($tProv
            ? ($provNameCol ? "p.{$provNameCol} AS proveedor_nombre, " : "('#'||c.id_proveedor::text) AS proveedor_nombre, ")
            : "('#'||c.id_proveedor::text) AS proveedor_nombre, ").
         "m.guarani::text AS guarani, m.real::text AS real, m.dolar::text AS dolar ".
         "FROM compra c ".
         ($tProv ? "LEFT JOIN {$tProv} p ON p.id_proveedor = c.id_proveedor " : "").
         "LEFT JOIN {$tMon}  m ON m.id_mon = c.id_mon ".
         "ORDER BY c.id_com DESC";
  $r = q1($conexion, $sql);
} else {
  $r = q1($conexion, "SELECT c.*, ('#'||c.id_proveedor::text) AS proveedor_nombre FROM compra c ORDER BY c.id_com DESC");
}

if(!$r){
  jsonOut(['success'=>false,'message'=>'Error al listar: '.pg_last_error($conexion)], 500);
}

$compras=[];
while($row=pg_fetch_assoc($r)){
  // moneda_doc se guarda en historico_com como: "Moneda: GUARANI | Cotización: 123 | id_mon: 4"
  $md = 'GUARANI';
  if(isset($row['historico_com'])){
    if(preg_match('/Moneda\s*:\s*(GUARANI|REAL|DOLAR)/i', (string)$row['historico_com'], $mmd)){
      $md = strtoupper($mmd[1]);
    }
  }

  $idMon = $row['id_mon'] ?? '';
  $rate = '0';
  $sym  = '';

  if($md === 'REAL'){ $rate = $row['real'] ?? '0'; $sym = 'R$'; }
  elseif($md === 'DOLAR'){ $rate = $row['dolar'] ?? '0'; $sym = 'US$'; }
  else { $rate = $row['guarani'] ?? '0'; $sym = 'Gs'; $md = 'GUARANI'; }

  // Si la cotización viene vacía, ponemos 0
  $rate = trim((string)$rate);
  if($rate === '') $rate = '0';

  $row['moneda_label'] = '#'.$idMon.' — '.$sym.' '.$rate;

  // Opcional: exponer también cuál moneda se eligió
  $row['moneda_doc'] = $md;

  // No necesitamos mandar todas las tasas si no querés verlas
  unset($row['guarani'], $row['real'], $row['dolar']);
  $compras[] = $row;
}
jsonOut($compras);
