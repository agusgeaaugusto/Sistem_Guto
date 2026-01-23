<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

$id_com = $_GET['id_com'] ?? '';

if (empty($id_com)) {
    echo json_encode(['success' => false, 'message' => 'ID de compra no proporcionado']);
    exit;
}

try {
    $sql = "SELECT pd.*, p.nombre_pro, p.codigo_barra_pro
            FROM producto_detalle pd
            JOIN producto p ON pd.id_pro = p.id_pro
            WHERE pd.id_com = :id_com";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_com', $id_com);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $productos]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
