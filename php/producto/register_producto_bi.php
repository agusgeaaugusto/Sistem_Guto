<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion_bi.php';

header('Content-Type: application/json; charset=utf-8');

$directorioImagenes = '../img/productos/';

// Crear carpeta si no existe
if (!is_dir($directorioImagenes)) {
  if (!mkdir($directorioImagenes, 0777, true)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'No se pudo crear la carpeta de imágenes.']);
    exit;
  }
}

// -------------------- POST: agregar --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nombre_pro       = trim((string)($_POST['nombre_pro'] ?? ''));
  $codigo_barra_pro = trim((string)($_POST['codigo_barra_pro'] ?? ''));
  $uni_caja_pro     = (int)($_POST['uni_caja_pro'] ?? 0);
  $iva_pro          = (float)($_POST['iva_pro'] ?? 0);
  $id_cat           = (int)($_POST['id_cat'] ?? 0);

  $favorito_raw = strtolower(trim((string)($_POST['favorito'] ?? '0')));
  $favorito     = in_array($favorito_raw, ['1','true','on','yes'], true);

  $imagen_pro = '';

  // Validación (según tu schema)
  if ($nombre_pro === '' || $codigo_barra_pro === '' || $uni_caja_pro <= 0 || $iva_pro < 0 || $id_cat <= 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Faltan campos obligatorios o son inválidos.']);
    exit;
  }

  // Imagen (opcional)
  if (isset($_FILES['imagen']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
    $archivo = basename((string)$_FILES['imagen']['name']);
    $archivo = uniqid('', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $archivo);
    $destino = $directorioImagenes . $archivo;

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
      $imagen_pro = $archivo;
    } else {
      http_response_code(500);
      echo json_encode(['success'=>false,'message'=>'Error al subir la imagen.']);
      exit;
    }
  }

  // Insert robusto: funciona con o sin serial/identity en id_pro
  pg_query($conexion, 'BEGIN');
  $okLock = pg_query($conexion, 'LOCK TABLE producto IN EXCLUSIVE MODE');
  if (!$okLock) {
    pg_query($conexion, 'ROLLBACK');
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'No se pudo bloquear tabla producto.']);
    exit;
  }

  $sql = "WITH next_id AS (
            SELECT COALESCE(MAX(id_pro),0)+1 AS id FROM producto
          )
          INSERT INTO producto (id_pro, nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat, favorito, imagen_pro)
          SELECT id, $1, $2, $3, $4, $5, $6, $7 FROM next_id
          RETURNING id_pro";

  $params = [$nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat, $favorito, $imagen_pro];
  $result = pg_query_params($conexion, $sql, $params);

  if (!$result) {
    pg_query($conexion, 'ROLLBACK');
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Error al guardar: '.pg_last_error($conexion)]);
    exit;
  }

  $row = pg_fetch_assoc($result) ?: [];
  pg_query($conexion, 'COMMIT');

  echo json_encode(['success'=>true, 'id_pro'=>(int)($row['id_pro'] ?? 0)]);
  exit;
}

// -------------------- GET: listar --------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $q = "SELECT id_pro, nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat, favorito, imagen_pro
        FROM producto
        ORDER BY id_pro ASC";
  $r = pg_query($conexion, $q);

  $productos = [];
  if ($r) {
    while ($row = pg_fetch_assoc($r)) { $productos[] = $row; }
  }
  echo json_encode($productos, JSON_UNESCAPED_UNICODE);
  pg_close($conexion);
  exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Método no permitido']);
