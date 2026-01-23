<?php
require_once 'conexion_bi.php';

header('Content-Type: application/json');

// Validar campos necesarios
$campos = ['id_com', 'id_pro', 'codigo_barra_pro', 'nombre_pro', 'uni_caja_pro', 'cantidad_caja_pro', 'cantidad_uni_pro', 'costo_caja_pro', 'costo_uni_pro', 'porcen_pro', 'precio1_pro', 'precio2_pro', 'precio3_pro', 'iva_pro', 'fecha_ven_pro'];
$datos = [];

foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        echo json_encode(['success' => false, 'error' => "Falta el campo: $campo"]);
        exit;
    }
    $datos[$campo] = $_POST[$campo];
}

try {
    $sql = "INSERT INTO producto_det (id_com, id_pro, codigo_barra_pro, nombre_pro, uni_caja_pro, cantidad_caja_pro, cantidad_uni_pro, costo_caja_pro, costo_uni_pro, porcen_pro, precio1_pro, precio2_pro, precio3_pro, iva_pro, fecha_ven_pro) 
            VALUES (:id_com, :id_pro, :codigo_barra_pro, :nombre_pro, :uni_caja_pro, :cantidad_caja_pro, :cantidad_uni_pro, :costo_caja_pro, :costo_uni_pro, :porcen_pro, :precio1_pro, :precio2_pro, :precio3_pro, :iva_pro, :fecha_ven_pro)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($datos);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
