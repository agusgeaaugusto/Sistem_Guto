<?php
include 'conexion_bi.php';

// Función para redirigir a la página principal
function redireccionar() {
    header("Location: register_proveedor_bi.php");
    exit();
}

// Verifica si se realizó una solicitud POST para agregar un nuevo proveedor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera y limpia los datos del formulario
    $nombre_prove = isset($_POST['nombre_prove'])? htmlspecialchars($_POST['nombre_prove']) : '';
    $ruc_prove = isset($_POST['ruc_prove'])? htmlspecialchars($_POST['ruc_prove']) : '';
    $direccion_prove = isset($_POST['direccion_prove'])? htmlspecialchars($_POST['direccion_prove']) : '';
    $telefono_prove = isset($_POST['telefono_prove'])? htmlspecialchars($_POST['telefono_prove']) : '';

   
    // Validar los datos
    if (empty($nombre_prove) || empty($ruc_prove) || empty($direccion_prove) || empty($telefono_prove)) {
        die("Por favor, completa todos los campos del formulario.");
    }

    // Preparar y ejecutar la consulta con consultas preparadas para evitar inyecciones SQL
    $query = "INSERT INTO proveedor (nombre_prove, ruc_prove, direccion_prove, telefono_prove) VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($conexion, $query, array($nombre_prove, $ruc_prove, $direccion_prove, $telefono_prove));

    // Verificar si la consulta fue exitosa
    if (!$result) {
        die("Error en la consulta: " . pg_last_error());
    }

    // Redireccionar después de la inserción
    redireccionar();
}

// Realiza la consulta para obtener la lista de proveedores ordenada por ID
$query = "SELECT * FROM proveedor ORDER BY id_proveedor ASC";
$result = pg_query($conexion, $query);

// Verificar si hubo algún error en la consulta
if (!$result) {
    die("Error en la consulta: " . pg_last_error());
}

// Crear un array para almacenar los proveedores
$proveedores = array();
while ($row = pg_fetch_assoc($result)) {
    $proveedores[] = $row;
}

// Devolver los proveedores como respuesta JSON
echo json_encode($proveedores);

// Cerrar la conexión
pg_close($conexion);
?>
