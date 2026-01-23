<?php
require_once __DIR__ . '/../../config/jasper.php';
$id_venta = intval($_GET['id_venta'] ?? 0);
if ($id_venta <= 0) { http_response_code(400); die('id_venta inválido'); }
$salida = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ticket_' . $id_venta . '_' . uniqid() . '.pdf';
$params = [
  'VENTA_ID' => $id_venta,
  'EMPRESA_NOMBRE'    => 'Carvallo Bodega',
  'EMPRESA_RUC'       => '80000000-1',
  'EMPRESA_DIR'       => 'Salto del Guairá',
  'EMPRESA_TEL'       => '+595 000 000',
  'EMPRESA_TIMBRADO'  => '12345678',
  'EMPRESA_PTO'       => '001-001',
];
run_report('ticket_58mm.jrxml', $salida, $params);
