<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/conexion_bi.php';
if (!isset($conexion) || !$conexion) { http_response_code(500); echo json_encode(['error'=>'Sin conexión DB']); exit; }

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
  $id_mon = $_GET['id'];

  $q = "SELECT id_mon, guarani, real, dolar, estado, fecha_inicio, fecha_fin, creado_en
        FROM moneda
        WHERE id_mon = $1";
  $r = pg_query_params($conexion, $q, [$id_mon]);

  if ($r && pg_num_rows($r) > 0) {
    echo json_encode(pg_fetch_assoc($r));
  } else {
    http_response_code(404);
    echo json_encode(['error' => 'Moneda no encontrada']);
  }
} else {
  http_response_code(400);
  echo json_encode(['error' => 'ID de moneda no válido']);
}
pg_close($conexion);
