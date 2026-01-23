<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT c.id_com, c.fecha_com, c.timbrado_com, c.documento_com, c.fecha_emision_comp,
               c.historico_com, c.valor_documento_com,
               p.nombre_prove, m.nombre_mon
        FROM compra c
        JOIN proveedor p ON c.id_proveedor = p.id_proveedor
        JOIN moneda m ON c.id_mon = m.id_mon
        ORDER BY c.id_com DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($compras);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
