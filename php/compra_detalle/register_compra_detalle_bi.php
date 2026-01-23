<?php
include 'conexion_bi.php';

// Función para redirigir a la página principal después de realizar una operación
function redireccionar() {
    header("Location: register_compra_detalle_bi.php");
    exit();
}

// Verificar si la conexión a la base de datos está activa
if (!$conexion) {
    die("Error de conexión: " . pg_last_error());
}

// Verificar si la solicitud es POST (para guardar datos)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar y validar datos
    $subtotal_comp_det = isset($_POST['subtotal_comp_det']) ? floatval($_POST['subtotal_comp_det']) : 0;
    $id_com = isset($_POST['id_com']) ? intval($_POST['id_com']) : 0;
    $id_pro = isset($_POST['id_pro']) ? intval($_POST['id_pro']) : 0;

    // Validar que los datos no estén vacíos
    if ($subtotal_comp_det <= 0 || $id_com <= 0 || $id_pro <= 0) {
        die("Por favor, completa todos los campos correctamente.");
    }

    // Preparar consulta para evitar SQL Injection
    $query = "INSERT INTO compra_detalle (subtotal_comp_det, id_com, id_pro) VALUES ($1, $2, $3)";
    $result = pg_query_params($conexion, $query, array($subtotal_comp_det, $id_com, $id_pro));

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Registro guardado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar: " . pg_last_error($conexion)]);
    }
    exit();
}

// Obtener los detalles de compra si la solicitud es GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $query = "SELECT * FROM compra_detalle ORDER BY id_comp_det DESC";
    $result = pg_query($conexion, $query);

    if ($result) {
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode($data);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al obtener datos: " . pg_last_error($conexion)]);
    }
    exit();
}

// Cerrar conexión
pg_close($conexion);
?>
