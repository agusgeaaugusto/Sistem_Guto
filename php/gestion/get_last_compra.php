<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

try {
    $sql = "SELECT id_com FROM compra ORDER BY id_com DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'id_com' => $result['id_com']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró ninguna compra.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
