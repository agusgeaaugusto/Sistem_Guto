<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM compra_detalle ORDER BY id_comp_det DESC");
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($detalles);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subtotal_comp_det = $_POST['subtotal_comp_det'] ?? null;
    $id_com = $_POST['id_com'] ?? null;
    $id_pro = $_POST['id_pro'] ?? null;
    $iva_comp_det = $_POST['iva_comp_det'] ?? 10; // valor por defecto
    $timbre_comp_det = $_POST['timbre_comp_det'] ?? 0;

    if (!$subtotal_comp_det || !$id_com || !$id_pro) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO compra_detalle (subtotal_comp_det, id_com, id_pro, iva_comp_det, timbre_comp_det)
                               VALUES (:subtotal_comp_det, :id_com, :id_pro, :iva_comp_det, :timbre_comp_det)");
        $stmt->execute([
            ':subtotal_comp_det' => $subtotal_comp_det,
            ':id_com' => $id_com,
            ':id_pro' => $id_pro,
            ':iva_comp_det' => $iva_comp_det,
            ':timbre_comp_det' => $timbre_comp_det
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
