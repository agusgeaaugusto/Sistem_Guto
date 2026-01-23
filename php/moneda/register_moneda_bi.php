<?php
include 'conexion_bi.php';

// Función para redirigir después de una operación
function redireccionar() {
    header("Location: register_moneda_bi.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar datos del formulario
    $nombre_mon = isset($_POST['nombre_mon']) ? htmlspecialchars($_POST['nombre_mon']) : '';
    $tasa_cambio = isset($_POST['tasa_cambio']) ? htmlspecialchars($_POST['tasa_cambio']) : '';

    // Validar los datos
    if (empty($nombre_mon) || empty($tasa_cambio)) {
        die("Por favor, completa todos los campos.");
    }

    // Insertar datos en la base de datos
    $query = "INSERT INTO Moneda (nombre_mon, tasa_cambio) VALUES ($1, $2)";
    $result = pg_query_params($conexion, $query, array($nombre_mon, $tasa_cambio));

    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    redireccionar();
}

// Obtener todas las monedas ordenadas por ID
$query = "SELECT * FROM Moneda ORDER BY id_mon ASC";
$result = pg_query($conexion, $query);

$monedas = array();
while ($row = pg_fetch_assoc($result)) {
    $monedas[] = $row;
}

echo json_encode($monedas);
pg_close($conexion);
?>
