<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id_proveedor, nombre_prove FROM proveedor ORDER BY nombre_prove ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($proveedores);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
