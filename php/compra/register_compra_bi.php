<?php
include 'conexion_bi.php';

// Función para redirigir a la página principal después de realizar una operación
function redireccionar() {
    header("Location: register_compra_bi.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar datos del formulario y limpiarlos
    $fecha_com = isset($_POST['fecha_com']) ? trim(htmlspecialchars($_POST['fecha_com'])) : '';
    $id_proveedor = isset($_POST['id_proveedor']) ? trim(htmlspecialchars($_POST['id_proveedor'])) : '';
    $id_mon = isset($_POST['id_mon']) ? trim(htmlspecialchars($_POST['id_mon'])) : '';
    $timbrado_com = isset($_POST['timbrado_com']) ? trim(htmlspecialchars($_POST['timbrado_com'])) : '';
    $documento_com = isset($_POST['documento_com']) ? trim(htmlspecialchars($_POST['documento_com'])) : '';
    $fecha_emision_comp = isset($_POST['fecha_emision_comp']) ? trim(htmlspecialchars($_POST['fecha_emision_comp'])) : '';
    $historico_com = isset($_POST['historico_com']) ? trim(htmlspecialchars($_POST['historico_com'])) : '';
    $valor_documento_com = isset($_POST['valor_documento_com']) ? trim(htmlspecialchars($_POST['valor_documento_com'])) : '';

    // Validar los datos
    if (empty($fecha_com) || empty($id_proveedor) || empty($id_mon) || empty($timbrado_com) || empty($documento_com) || empty($fecha_emision_comp) || empty($valor_documento_com)) {
        die("Por favor, completa todos los campos del formulario obligatorios.");
    }

    // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
    $query = "INSERT INTO Compra (fecha_com, id_proveedor, id_mon, timbrado_com, documento_com, fecha_emision_comp, historico_com, valor_documento_com) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $result = pg_query_params($conexion, $query, array($fecha_com, $id_proveedor, $id_mon, $timbrado_com, $documento_com, $fecha_emision_comp, $historico_com, $valor_documento_com));

    // Verificar si la consulta fue exitosa
    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    // Redireccionar después de la inserción
    redireccionar();
}

// Realiza la consulta para obtener la lista de compras ordenada por ID
$query = "SELECT * FROM Compra ORDER BY id_com ASC"; // ASC para ordenar de menor a mayor (ascendente)
$result = pg_query($conexion, $query);

if (!$result) {
    die("Error en la consulta: " . pg_last_error());
}

// Crear un array para almacenar las compras
$compras = array();
while ($row = pg_fetch_assoc($result)) {
    $compras[] = $row;
}

// Enviar las compras como respuesta JSON
echo json_encode($compras);

// Cerrar la conexión
pg_close($conexion);
?>
