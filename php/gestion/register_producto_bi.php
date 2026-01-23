<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

// Capturar datos del formulario
$nombre_pro = $_POST['nombre_pro'] ?? '';
$codigo_barra_pro = $_POST['codigo_barra_pro'] ?? '';
$uni_caja_pro = $_POST['uni_caja_pro'] ?? 0;
$iva_pro = $_POST['iva_pro'] ?? 0;
$id_cat = $_POST['id_cat'] ?? null;

if (empty($nombre_pro) || empty($codigo_barra_pro) || empty($uni_caja_pro) || is_null($id_cat)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $sql = "INSERT INTO producto (nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat)
            VALUES (:nombre_pro, :codigo_barra_pro, :uni_caja_pro, :iva_pro, :id_cat) RETURNING id_pro";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nombre_pro', $nombre_pro);
    $stmt->bindParam(':codigo_barra_pro', $codigo_barra_pro);
    $stmt->bindParam(':uni_caja_pro', $uni_caja_pro);
    $stmt->bindParam(':iva_pro', $iva_pro);
    $stmt->bindParam(':id_cat', $id_cat);
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'id_pro' => $result['id_pro'],
        'codigo_barra_pro' => $codigo_barra_pro,
        'nombre_pro' => $nombre_pro,
        'uni_caja_pro' => $uni_caja_pro
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
