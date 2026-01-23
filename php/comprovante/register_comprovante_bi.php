<?php
include 'conexion_bi.php';

function redireccionar() {
    header("Location: register_comprovante_bi.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_comprovante = isset($_POST['nombre_comprovante']) ? htmlspecialchars($_POST['nombre_comprovante']) : '';

    if (empty($nombre_comprovante)) {
        die("Por favor, completa todos los campos del formulario.");
    }

    $query = "INSERT INTO TipoComprovante (nombre_comprovante) VALUES ($1)";
    $result = pg_query_params($conexion, $query, array($nombre_comprovante));

    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    redireccionar();
}

$query = "SELECT * FROM TipoComprovante ORDER BY id_comprovante ASC";
$result = pg_query($conexion, $query);

$comprovantes = array();
while ($row = pg_fetch_assoc($result)) {
    $comprovantes[] = $row;
}

echo json_encode($comprovantes);

pg_close($conexion);
?>

