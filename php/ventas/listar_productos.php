<?php
require_once __DIR__ . '/../cargos/conexion_bi.php';
header('Content-Type: application/json; charset=utf-8');

try {
  if (!isset($conexion) || !$conexion) throw new Exception('Sin conexión a la base de datos.');

  $sql = "
    SELECT 
      p.id_pro,
      p.nombre_pro,
      p.imagen_pro,
      p.codigo_barra_pro,
      p.favorito,

      -- PRECIOS: último con precio > 0 -> último por fecha -> 0
      COALESCE(pok.precio1_pro, plast.precio1_pro, 0) AS precio1_pro,
      COALESCE(pok.precio2_pro, plast.precio2_pro, 0) AS precio2_pro,
      COALESCE(pok.precio3_pro, plast.precio3_pro, 0) AS precio3_pro,

      -- STOCK (unid.): último con cantidad -> último por fecha -> 0
      COALESCE(sok.cantidad_uni_pro, plast.cantidad_uni_pro, 0) AS cantidad_uni_pro,

      COALESCE(plast.costo_uni_pro, 0) AS costo_uni_pro,
      plast.fecha_ven_pro,
      plast.id_com

    FROM public.producto p

    -- Último detalle por FECHA (backup por id)
    LEFT JOIN LATERAL (
      SELECT d.*
      FROM public.productodetalle d
      WHERE d.id_pro = p.id_pro
      ORDER BY d.fecha_ven_pro DESC NULLS LAST, d.id_det_pro DESC
      LIMIT 1
    ) plast ON TRUE

    -- Último detalle con ALGÚN precio > 0
    LEFT JOIN LATERAL (
      SELECT d.precio1_pro, d.precio2_pro, d.precio3_pro
      FROM public.productodetalle d
      WHERE d.id_pro = p.id_pro
        AND (COALESCE(d.precio1_pro,0) > 0
          OR COALESCE(d.precio2_pro,0) > 0
          OR COALESCE(d.precio3_pro,0) > 0)
      ORDER BY d.fecha_ven_pro DESC NULLS LAST, d.id_det_pro DESC
      LIMIT 1
    ) pok ON TRUE

    -- Último detalle con cantidad informada
    LEFT JOIN LATERAL (
      SELECT d.cantidad_uni_pro
      FROM public.productodetalle d
      WHERE d.id_pro = p.id_pro
        AND d.cantidad_uni_pro IS NOT NULL
      ORDER BY d.fecha_ven_pro DESC NULLS LAST, d.id_det_pro DESC
      LIMIT 1
    ) sok ON TRUE

    ORDER BY p.nombre_pro ASC
    LIMIT 1000
  ";

  $res = pg_query($conexion, $sql);
  if (!$res) throw new Exception('Error SQL: ' . pg_last_error($conexion));

  $data = [];
  while ($row = pg_fetch_assoc($res)) {
    $fav_raw = strtolower((string)$row['favorito']);
    $data[] = [
      'id_pro'            => (int)$row['id_pro'],
      'nombre_pro'        => (string)$row['nombre_pro'],
      'codigo_barra_pro'  => isset($row['codigo_barra_pro']) ? (string)$row['codigo_barra_pro'] : '',
      'imagen_pro'        => !empty($row['imagen_pro']) ? (string)$row['imagen_pro'] : 'sin_imagen.jpg',
      'favorito'          => in_array($fav_raw, ['t','true','1','sí','si','y','yes'], true),
      'precio1_pro'       => (float)$row['precio1_pro'],
      'precio2_pro'       => (float)$row['precio2_pro'],
      'precio3_pro'       => (float)$row['precio3_pro'],
      'cantidad_uni_pro'  => (int)$row['cantidad_uni_pro'],
      'costo_uni_pro'     => (float)$row['costo_uni_pro'],
      'fecha_ven_pro'     => $row['fecha_ven_pro'],
      'id_com'            => isset($row['id_com']) ? (int)$row['id_com'] : null,
    ];
  }

  echo json_encode(['success'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($res) && is_resource($res)) pg_free_result($res);
  if (isset($conexion) && $conexion) pg_close($conexion);
}
