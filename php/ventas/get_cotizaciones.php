<?php
require_once 'conexion_bi.php';
header('Content-Type: application/json; charset=UTF-8');

// Obtener SOLO la cotización ACTIVA (la más reciente)
$query = "
  SELECT guarani, real, dolar
  FROM moneda
  WHERE estado = 'ACTIVO'
  ORDER BY creado_en DESC, id_mon DESC
  LIMIT 1
";

$result = pg_query($conexion, $query);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);

    $respuesta = [
        'guarani' => 1000, // 🔥 BASE REAL: 1000 Gs
        'real'    => floatval($row['real']),
        'dolar'   => floatval($row['dolar'])
    ];

    echo json_encode($respuesta);
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'No existe cotización ACTIVA'
    ]);
}

pg_close($conexion);
