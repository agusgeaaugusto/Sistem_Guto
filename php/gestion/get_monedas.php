<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id_mon, nombre_mon FROM moneda ORDER BY nombre_mon ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $monedas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($monedas);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
