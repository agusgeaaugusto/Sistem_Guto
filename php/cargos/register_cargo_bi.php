<?php
include 'conexion_bi.php';

// Función para redirigir a la página principal después de realizar una operación
function redireccionar() {
    header("Location: register_cargo_bi.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar datos del formulario y limpiarlos
    $nombre_cargo = isset($_POST['nombre_cargo']) ? htmlspecialchars($_POST['nombre_cargo']) : '';

    // Validar los datos
    if (empty($nombre_cargo)) {
        die("Por favor, completa todos los campos del formulario.");
    }

    // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
    $query = "INSERT INTO Cargo (nombre_cargo) VALUES ($1)";
    $result = pg_query_params($conexion, $query, array($nombre_cargo));

    // Verificar si la consulta fue exitosa
    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    // Redireccionar después de la inserción (ajusta la ruta según tu estructura de archivos)
    redireccionar();
}

// Realiza la consulta para obtener la lista de cargos ordenada por ID
$query = "SELECT * FROM Cargo ORDER BY id_cargo ASC"; // ASC para ordenar de menor a mayor (ascendente)
$result = pg_query($conexion, $query);

// Crear un array para almacenar los cargos
$cargos = array();
while ($row = pg_fetch_assoc($result)) {
    $cargos[] = $row;
}

// Enviar los cargos como respuesta JSON
echo json_encode($cargos);

// Cerrar la conexión
pg_close($conexion);
?>
