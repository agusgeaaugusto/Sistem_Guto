<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

$fecha_com = $_POST['fecha_com'] ?? '';
$id_proveedor = $_POST['id_proveedor'] ?? '';
$id_mon = $_POST['id_mon'] ?? '';
$timbrado_com = $_POST['timbrado_com'] ?? '';
$documento_com = $_POST['documento_com'] ?? '';
$fecha_emision_comp = $_POST['fecha_emision_comp'] ?? '';
$historico_com = $_POST['historico_com'] ?? '';
$valor_documento_com = $_POST['valor_documento_com'] ?? '';

if (empty($fecha_com) || empty($id_proveedor) || empty($id_mon) || empty($timbrado_com) || empty($documento_com) || empty($fecha_emision_comp) || empty($valor_documento_com)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    $sql = "INSERT INTO compra (fecha_com, id_proveedor, id_mon, timbrado_com, documento_com, fecha_emision_comp, historico_com, valor_documento_com)
            VALUES (:fecha_com, :id_proveedor, :id_mon, :timbrado_com, :documento_com, :fecha_emision_comp, :historico_com, :valor_documento_com)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fecha_com' => $fecha_com,
        ':id_proveedor' => $id_proveedor,
        ':id_mon' => $id_mon,
        ':timbrado_com' => $timbrado_com,
        ':documento_com' => $documento_com,
        ':fecha_emision_comp' => $fecha_emision_comp,
        ':historico_com' => $historico_com,
        ':valor_documento_com' => $valor_documento_com
    ]);

    $id_com = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'id_com' => $id_com]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
