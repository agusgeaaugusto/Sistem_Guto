<?php
include 'conexion_bi.php';

// Función para redirigir a la página principal después de realizar una operación
function redireccionar() {
    header("Location: register_rol_bi.php");
    exit();
}

// Verificar si el método de solicitud es POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar datos del formulario y limpiarlos
    $descripcion_rol = isset($_POST['descripcion_rol']) ? htmlspecialchars($_POST['descripcion_rol']) : '';
    $accesos_rol = isset($_POST['accesos_rol']) ? $_POST['accesos_rol'] : array(); // Asumiendo que los accesos se envían como un array
    $creado_rol = date('Y-m-d'); // Fecha actual
    $fecha_rol = isset($_POST['fecha_rol']) ? htmlspecialchars($_POST['fecha_rol']) : '';

    // Validar los datos
    if (empty($descripcion_rol) || empty($accesos_rol) || empty($fecha_rol)) {
        die("Por favor, completa todos los campos del formulario.");
    }

    // Convertir el array de accesos a una cadena para almacenarlo en la base de datos
    $accesos_rol_str = '{' . implode(",", $accesos_rol) . '}';

    // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
    $query = "INSERT INTO Roles (descripcion_rol, accesos_rol, creado_rol, fecha_rol) VALUES ($1, $2, $3, $4)";
    $result = pg_query_params($conexion, $query, array($descripcion_rol, $accesos_rol_str, $creado_rol, $fecha_rol));

    // Verificar si la consulta fue exitosa
    if (!$result) {
        die("Error al guardar el rol: " . pg_last_error($conexion)); // Agregué $conexion como argumento de pg_last_error
    }

    // Redireccionar después de la inserción (ajusta la ruta según tu estructura de archivos)
    redireccionar();
}

// Si se accede a este script a través de una solicitud GET, obtener y mostrar los roles
$query = "SELECT * FROM Roles ORDER BY id_rol ASC"; // ASC para ordenar de menor a mayor (ascendente)
$result = pg_query($conexion, $query);

// Verificar si la consulta fue exitosa
if (!$result) {
    die("Error al obtener los roles: " . pg_last_error($conexion)); // Agregué $conexion como argumento de pg_last_error
}

// Crear un array para almacenar los roles
$roles = array();
while ($row = pg_fetch_assoc($result)) {
    $roles[] = $row;
}

// Liberar el resultado
pg_free_result($result);

// Cerrar la conexión
pg_close($conexion);

// Enviar los roles como respuesta JSON
echo json_encode($roles);
?>
