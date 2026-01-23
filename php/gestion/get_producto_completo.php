<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

$codigo_barra = $_GET['codigo_barra'] ?? '';

if (empty($codigo_barra)) {
    echo json_encode(['existe' => false, 'message' => 'Código de barra no proporcionado']);
    exit;
}

try {
    $sql = "SELECT p.id_pro, p.nombre_pro, p.codigo_barra_pro, p.uni_caja_pro, p.iva_pro,
                   pd.cantidad_caja_pro, pd.cantidad_uni_pro, pd.costo_caja_pro, pd.costo_uni_pro, 
                   pd.porcen_pro, pd.precio1_pro, pd.precio2_pro, pd.precio3_pro
            FROM producto p
            LEFT JOIN producto_detalle pd ON p.id_pro = pd.id_pro
            WHERE p.codigo_barra_pro = :codigo_barra
            ORDER BY pd.id_det_pro DESC LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':codigo_barra', $codigo_barra);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['existe' => true] + $producto);
    } else {
        echo json_encode(['existe' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
}
