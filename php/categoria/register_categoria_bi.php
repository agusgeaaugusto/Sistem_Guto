<?php
include 'conexion_bi.php';

// Función para enviar una respuesta JSON con el código de estado HTTP
function responder($status, $success, $message, $data = null) {
    http_response_code($status);
    $response = ["success" => $success, "message" => $message];
    if (!is_null($data)) {
        $response["data"] = $data;
    }
    echo json_encode($response);
    exit();
}

// Verifica que la conexión a la base de datos esté activa
if (!$conexion) {
    responder(500, false, "Error al conectar con la base de datos: " . pg_last_error());
}

// Manejar solicitud POST (Agregar categoría)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_cat = isset($_POST['nombre_cat']) ? trim(htmlspecialchars($_POST['nombre_cat'])) : '';

    if (empty($nombre_cat)) {
        responder(400, false, "El nombre de la categoría no puede estar vacío.");
    }

    $query = "INSERT INTO Categoria (nombre_cat) VALUES ($1)";
    $result = pg_query_params($conexion, $query, [$nombre_cat]);

    if ($result) {
        responder(201, true, "Categoría agregada exitosamente.");
    } else {
        responder(500, false, "Error al insertar la categoría: " . pg_last_error($conexion));
    }
}

// Manejar solicitud GET (Obtener lista de categorías)
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $query = "SELECT * FROM Categoria ORDER BY id_cat ASC";
    $result = pg_query($conexion, $query);

    if (!$result) {
        responder(500, false, "Error al obtener las categorías: " . pg_last_error($conexion));
    }

    $categorias = [];
    while ($row = pg_fetch_assoc($result)) {
        $categorias[] = $row;
    }

    responder(200, true, "Lista de categorías obtenida exitosamente.", $categorias);
}

// Manejar otros métodos HTTP no permitidos
responder(405, false, "Método no permitido.");

// Cerrar la conexión
pg_close($conexion);
?>
